<?php

namespace App\Http\Controllers;

use App\Models\stockcountingtables;
use App\Models\stockcountingtrans;
use App\Models\numbersequencevalues;
use App\Models\inventtables;
use App\Models\control;
use App\Models\rboinventtables;
use App\Models\inventtablemodules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Google_Client;
use Google_Service_Sheets;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class StockCountingLineController extends Controller
{
    public function index()
    {
        try {
            $storename = Auth::user()->storeid;
            $role = Auth::user()->role;

            $stockcountingtrans = DB::table('stockcountingtables as a')
                ->Join('stockcountingtrans as b', 'a.JOURNALID', '=', 'b.JOURNALID')
                ->leftJoin('inventtables as c', 'b.ITEMID', '=', 'c.itemid')
                ->where('a.STOREID', '=', $storename) 
                ->where('a.posted', '!=', '1')
                ->get();
        
            return Inertia::render('StockCountingLine/index', [
                'stockcountingtrans' => $stockcountingtrans
            ]);
        } catch (\Exception $e) {
            return back()
                ->with('message', 'Error loading data: ' . $e->getMessage())
                ->with('isError', true);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'itemname' => 'required|string',  
                'qty' => 'required|integer',  
            ]);
            
            $currentDateTime = Carbon::now('Asia/Manila')->toDateString();
            $storename = Auth::user()->storeid;

            DB::table('stockcountingtrans')->insert([
                'JOURNALID' => $request->JOURNALID,
                'LINENUM' => '',
                'TRANSDATE' => $currentDateTime,
                'ITEMID' => $request->itemname,
                'COUNTED' => $request->qty,    
                'updated_at' => $currentDateTime,
                'storename' => $storename,                
            ]);

            return redirect()
                ->route('StockCountingLine', ['journalid' => $request->JOURNALID])
                ->with('message', 'Stock Counting Successfully')
                ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
        } catch (\Exception $e) {
            return back()
                ->with('message', 'Error storing data: ' . $e->getMessage())
                ->with('isError', true);
        }
    }

    
    public function StockCountingLine($journalid)
    {
        try {
            $storeName = Auth::user()->storeid;
            $currentDate = Carbon::now('Asia/Manila')->toDateString();

            $stockcountingtrans = DB::table('stockcountingtrans AS a')
                ->select(
                    'a.*', 
                    'b.*', 
                    'c.*', 
                    'st.posted',
                    DB::raw("DATE(a.TRANSDATE) as TRANSDATE")  
                )
                ->leftJoin('inventtables AS b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('rboinventtables AS c', 'b.itemid', '=', 'c.itemid')
                ->leftJoin('stockcountingtables as st', function($join) use ($storeName) {
                    $join->on('a.journalid', '=', 'st.journalid')
                        ->where('st.storeid', '=', $storeName);
                })
                ->where('a.journalid', $journalid)
                ->where('a.storename', $storeName)
                ->OrderBy('a.ADJUSTMENT', 'DESC')
                ->get();

            $isPosted = DB::table('stockcountingtables')
                ->where('journalid', $journalid)
                ->where('storeid', $storeName)
                ->value('posted') ?? 0;

            return Inertia::render('StockCountingLine/index', [
                'journalid' => $journalid,
                'stockcountingtrans' => $stockcountingtrans,
                'isPosted' => $isPosted,
                'currentDate' => $currentDate, 
            ]);
        } catch (\Exception $e) {
            return back()
                ->with('message', 'Error loading stock counting line: ' . $e->getMessage())
                ->with('isError', true);
        }
    }

    public function getbwproducts(Request $request)
    {
        try {
            $request->validate([
                'JOURNALID' => 'required|string',  
            ]);

            $currentDateTime = Carbon::now('Asia/Manila')->toDateString();
            $journalid = $request->JOURNALID;
            $storename = Auth::user()->storeid;
            $role = Auth::user()->role;

            // Check for existing records
            $record = DB::table('stockcountingtrans')
                ->where('JOURNALID', $journalid)
                ->count();

            if ($record >= 1) {
                return redirect()
                    ->route('StockCountingLine', ['journalid' => $journalid])
                    ->with('message', 'You have already generated items!')
                    ->with('isError', true);
            }

            // Begin transaction
            DB::beginTransaction();

            try {
                // Insert active inventory items
                DB::table('stockcountingtrans')
                    ->insertUsing(
                        ['JOURNALID', 'ITEMDEPARTMENT', 'TRANSDATE', 'ITEMID', 'ADJUSTMENT', 'RECEIVEDCOUNT', 'WASTECOUNT', 'COUNTED', 'STORENAME'],
                        function ($query) use ($request, $currentDateTime, $storename) {
                            $query->select(
                                DB::raw("'{$request->JOURNALID}' as JOURNALID"),
                                'b.itemdepartment',
                                DB::raw("'{$currentDateTime}' as TRANSDATE"),
                                'a.itemid as ITEMID',
                                DB::raw('0 as ADJUSTMENT'),
                                DB::raw('0 as RECEIVEDCOUNT'),
                                DB::raw('0 as WASTECOUNT'),
                                DB::raw('0 as COUNTED'),
                                DB::raw("'{$storename}' as STORENAME")
                            )
                            ->from('inventtables as a')
                            ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                            ->where('b.activeondelivery', '1');
                        }
                    );

                $yesterday = Carbon::yesterday()->format('Y-m-d');

                $journalRecords = DB::connection('remote_db')
                    ->table('inventjournaltables as a')
                    ->leftJoin('inventjournaltrans as b', 'a.journalid', '=', 'b.journalid') 
                    ->whereDate('a.posteddatetime', $yesterday)
                    ->where('a.storeid', $storename)  
                    ->get();

                foreach ($journalRecords as $record) {
                    DB::table('stockcountingtrans')
                        ->where('ITEMID', $record->ITEMID) 
                        ->where('TRANSDATE', $currentDateTime)
                        ->update([
                            'ADJUSTMENT' => $record->ADJUSTMENT,
                            'RECEIVEDCOUNT' => $record->ADJUSTMENT,
                            'updated_at' => now()
                        ]);
                }

                // Commit transaction
                DB::commit();

                return redirect()
                    ->route('StockCountingLine', ['journalid' => $journalid])
                    ->with('message', 'Generate Item Successfully')
                    ->with('isSuccess', true);
            } catch (\Exception $e) {
                // Rollback transaction
                DB::rollBack();

                return back()
                    ->with('message', 'Error generating items: ' . $e->getMessage())
                    ->with('isError', true);
            }
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
        }
    }

    public function getstocktransfer(Request $request, $journalid)
    {
        try {
            $storename = Auth::user()->storeid;
            
            DB::beginTransaction();

            $journalRecords = DB::table('stock_transfers as a')
                ->leftJoin('stock_transfer_items as b', 'a.id', '=', 'b.stock_transfer_id')
                ->select('b.itemid', DB::raw('SUM(b.quantity) as qty'))
                ->whereDate('a.transfer_date', now())
                ->groupBy('b.itemid')
                ->get();

            foreach ($journalRecords as $record) {
                DB::table('stockcountingtrans')
                    ->where('ITEMID', $record->itemid)
                    ->whereDate('TRANSDATE', now())
                    ->update([
                        'TRANSFERCOUNT' => $record->qty,
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            return redirect()
                ->route('StockCountingLine', ['journalid' => $journalid])
                ->with('message', 'Stock Transfer Updated Successfully')
                ->with('isSuccess', true);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('message', 'Error occurred: ' . $e->getMessage())
                ->with('isError', true);
        }
    }

    public function updateAllCountedValues(Request $request) {
        try {
            Log::info('updateAllCountedValues method called', ['request' => $request->all()]);
            
            $user = Auth::user()->storeid;
            Log::info('Authenticated user store ID', ['storeid' => $user]);
            
            if (!$user) {
                Log::warning('Unauthorized access attempt - no store ID found', ['user' => Auth::user()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
            Log::info('Store ID retrieved successfully', ['storeid' => $user]);
    
            $validated = $request->validate([
                'journalId' => 'required|string',
                'updatedValues' => 'required|array',
                'updatedValues.*' => 'array',
                'updatedValues.*.RECEIVEDCOUNT' => 'nullable|numeric|min:0',
                'updatedValues.*.WASTECOUNT' => 'nullable|numeric|min:0',
                'updatedValues.*.COUNTED' => 'nullable|numeric|min:0',
                'updatedValues.*.TRANSFERCOUNT' => 'nullable|numeric|min:0',
                'updatedValues.*.WASTETYPE' => 'nullable|string|in:throw_away,early_molds,pull_out,rat_bites,ant_bites'
            ]);
    
            Log::info('Validation successful, validated data', ['validated_data' => $validated]);
    
            $storename = $user;
            Log::info('Processing with store name', ['storeid' => $storename]);
    
            try {
                DB::beginTransaction();
                $currentDate = Carbon::now('Asia/Manila')->toDateString();
                $successCount = 0;
                $errors = [];          
                
                foreach ($validated['updatedValues'] as $itemId => $values) {             
                    try {
                        $record = DB::table('stockcountingtrans')
                            ->where('JOURNALID', $validated['journalId'])
                            ->where('ITEMID', $itemId)
                            ->first();              
                        
                        if (!$record) {
                            $errors[] = "Record not found for ITEMID: $itemId";
                            continue;
                        }              
                        
                        if ($record->POSTED === 1) {
                            $errors[] = "Cannot update posted record for ITEMID: $itemId";
                            continue;
                        }              
                        
                        $recordDate = Carbon::parse($record->TRANSDATE)->toDateString();
                        if (isset($values['RECEIVEDCOUNT']) && $recordDate !== $currentDate) {
                            $errors[] = "Cannot update RECEIVEDCOUNT for past date records. ITEMID: $itemId";
                            continue;
                        }
            
                        if (isset($values['TRANSFERCOUNT']) && $recordDate !== $currentDate) {
                            $errors[] = "Cannot update TRANSFERCOUNT for past date records. ITEMID: $itemId";
                            continue;
                        }
                        
                        if (isset($values['WASTETYPE'])) {
                            if ($record->WASTETYPE !== null) {
                                $errors[] = "Cannot update waste type for ITEMID: $itemId - waste type already set";
                                continue;
                            }
                            
                            if (empty($values['WASTECOUNT']) && empty($record->WASTECOUNT)) {
                                $errors[] = "Waste count is required when setting waste type for ITEMID: $itemId";
                                continue;
                            }
                        }
                        
                        if (isset($values['WASTECOUNT']) && $values['WASTECOUNT'] == 0 && 
                            (isset($values['WASTETYPE']) || $record->WASTETYPE !== null)) {
                            $errors[] = "Waste count cannot be 0 when waste type is set for ITEMID: $itemId";
                            continue;
                        }
                        
                        $updateData = [];
                        foreach ($values as $field => $value) {
                            if (in_array($field, ['RECEIVEDCOUNT', 'WASTECOUNT', 'WASTETYPE', 'COUNTED', 'TRANSFERCOUNT'])) {
                                $updateData[$field] = $value;
                            }
                        }
                        
                        if (isset($values['WASTECOUNT']) || isset($values['WASTETYPE'])) {
                            $updateData['WASTEDATE'] = $currentDate;
                        }
                        
                        if (!empty($updateData)) {
                            $updateData['updated_at'] = now();
                            $updated = DB::table('stockcountingtrans')
                                ->where('JOURNALID', $validated['journalId'])
                                ->where('ITEMID', $itemId)
                                ->update($updateData);
                                
                            if ($updated) {
                                $successCount++;
                            }
                        }
                    } catch (Exception $e) {
                        Log::error('Error processing item', [
                            'itemId' => $itemId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $errors[] = "Error processing ITEMID: $itemId - " . $e->getMessage();
                        continue;
                    }
                }   
    
                if (empty($errors)) {
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => "$successCount records updated successfully"
                    ]);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'errors' => $errors
                    ], 422);
                }
            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Transaction error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred during the transaction',
                    'error' => $e->getMessage()
                ], 500);
            }
        } catch (ValidationException $e) {
            Log::error('Validation error', [
                'error' => $e->getMessage(),
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ViewOrders($journalid)
    {
        try {
            $storename = Auth::user()->storeid;
            
            $currentDate = Carbon::now('Asia/Manila')->toDateString();

            $record = DB::table('stockcountingtables')
                ->where('journalid', $journalid)
                ->where('storeid', $storename)
                ->where('posted', 0)
                ->count();

            $stockcountingtrans = DB::table('stockcountingtrans as a')
                ->select('a.*', 'b.*', 'c.*')
                ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
                ->where('a.journalid', $journalid)
                ->where('a.storename', $storename)
                ->where('a.counted', '!=', '0')
                ->get();

            return Inertia::render('StockCountingLine/index2', [
                'journalid' => $journalid,
                'stockcountingtrans' => $stockcountingtrans,
            ]);
        } catch (\Exception $e) {
            return back()
                ->with('message', 'Error viewing orders: ' . $e->getMessage())
                ->with('isError', true);
        }
    }

    public function post(Request $request)
    {
        try {
            $currentDateTime = Carbon::now('Asia/Manila')->toDateString();
            $storename = Auth::user()->storeid;
            $journalid = $request->journalid;
            $role = Auth::user()->role;
       
            DB::beginTransaction();
            
            $affected = DB::table('stockcountingtables')
                ->whereDate(DB::raw('cast(posteddatetime as date)'), $currentDateTime)
                ->update(['posted' => '1']);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Posted successfully',
                'affected' => $affected
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error posting: ' . $e->getMessage()
            ], 500);
        }
    }

    public function postbatch(Request $request)
    {
        try {
            $currentDateTime = Carbon::now('Asia/Manila')->toDateString();
            $storeName = Auth::user()->storeid;
            $journalid = $request->journalid;
            $role = Auth::user()->role;

            DB::beginTransaction();
            
            try {
                $storeStockCountingTables = DB::table('stockcountingtables')
                    ->get()
                    ->map(function ($item) {
                        return (array) $item;
                    })
                    ->toArray();
                    
                $storeStockCountingTrans = DB::table('stockcountingtrans')
                    ->get()
                    ->map(function ($item) {
                        return (array) $item;
                    })
                    ->toArray();

                DB::table('stockcountingtables')->insert($storeStockCountingTables);
                DB::table('stockcountingtrans')->insert($storeStockCountingTrans);

                DB::table('vstockcountingtables')->insert($storeStockCountingTables);
                DB::table('vstockcountingtrans')->insert($storeStockCountingTrans);

                DB::table('stockcountingtables')->update(['posted' => 1]);
                DB::table('vstockcountingtables')->update(['posted' => 1]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Data processed successfully',
                    'count' => count($storeStockCountingTables) + count($storeStockCountingTrans)
                ]);

            } catch (\Exception $e) {
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
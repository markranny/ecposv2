<?php

namespace App\Http\Controllers;
use App\Models\inventjournaltables;
use App\Models\inventjournaltrans;
use App\Models\inventjournaltransrepos;
use App\Models\numbersequencevalues;
use App\Models\inventtables;
use App\Models\control;
use App\Models\rboinventtables;
use App\Models\inventtablemodules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Google_Client;
use Google_Service_Sheets;
use Inertia\Inertia;
use Carbon\Carbon;

class ItemOrderController extends Controller
{
    public function index()
    {
        $storename = Auth::user()->storeid;

        $inventjournaltrans = DB::table('inventjournaltables as a')
        ->Join('inventjournaltrans as b', 'a.JOURNALID', '=', 'b.JOURNALID')
        ->leftJoin('inventtables as c', 'b.ITEMID', '=', 'c.itemid')
        ->where('a.STOREID', '=', $storename) 
        ->get();
    
        return Inertia::render('ItemOrders/index', ['inventjournaltrans' => $inventjournaltrans]);
    }

    public function ViewOrders($journalid)
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $record = DB::table('inventjournaltables')
            ->where('journalid', $journalid)
            ->where('storeid', $storename)
            ->where('posted', 0)
            ->count();

            if ($record <= 0) {
                return redirect()->route('order.index')
                ->with('message', 'This has already been posted. If you want to view your order, please click on the Report module in the left sidebar')
                ->with('isError', true);

            } else {

            $inventjournaltransrepos = DB::table('inventjournaltransrepos as a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->where('a.journalid',$journalid)
            ->where('a.storename', $storename)
            ->where('a.itemdepartment', 'REGULAR PRODUCT')
            ->where('a.counted', '!=', '0')
            ->get();

            return Inertia::render('ItemOrders/index2', [
                'journalid' => $journalid,
                'inventjournaltransrepos' => $inventjournaltransrepos,
            ]);
            }

    }

    public function fgsync()
    {

        try{
            $utcDateTime = Carbon::now('UTC');
            $beijingDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            rboinventtables::query()->
            update([
                'TRANSPARENTSTOCKS' => 0,
            ]);

            }catch(ValidationException $e) {
                return redirect()->back()
                    ->with('message', 'INVALID!')
                    ->with('isError', true);
            }  
            
            return redirect()->back();
    }

    public function autopost()
    {

        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storename = Auth::user()->storeid;
        $role = Auth::user()->role;
   
        DB::beginTransaction();
        try {

            $affected = DB::insert('
                INSERT INTO inventjournaltrans 
                (JOURNALID, TRANSDATE, ITEMID, ITEMDEPARTMENT, ADJUSTMENT, COSTPRICE, PRICEUNIT, SALESAMOUNT, INVENTONHAND, COUNTED, REASONREFRECID, VARIANTID, POSTED, POSTEDDATETIME, UNITID, updated_at)
                SELECT journalid, ?, itemid, itemdepartment, counted, 0.00, 0.00, 0.00, 0, counted, \'00001\', 0, 0, ?, \'PCS\', updated_at
                FROM inventjournaltransrepos
                WHERE storename = ? AND journalid = ? 
                AND CAST(transdate AS DATE) = ? 
                AND posted = 0 AND sent = 1
                ON DUPLICATE KEY UPDATE
                TRANSDATE = VALUES(TRANSDATE),
                ADJUSTMENT = VALUES(ADJUSTMENT),
                COUNTED = VALUES(COUNTED),
                POSTEDDATETIME = VALUES(POSTEDDATETIME),
                updated_at = VALUES(updated_at)
            ', [$currentDateTime, $currentDateTime, $currentDateTime]);


            }catch(ValidationException $e) {
                return redirect()->back()
                    ->with('message', 'INVALID!')
                    ->with('isError', true);
            }  
        DB::commit();
            
            return redirect()->back();
    }

    private function getSheetData()
    {
        try{
            $utcDateTime = Carbon::now('UTC');
            $manilaDateTime = $utcDateTime->setTimezone('Asia/Manila');
            $formattedDate = $manilaDateTime->format('F j, Y');
            $formattedDate = strtoupper($formattedDate);

            /* $utcDateTime = Carbon::now('UTC');
            $manilaDateTime = $utcDateTime->setTimezone('Asia/Manila');
            $yesterdayDateTime = $manilaDateTime->subDay();
            $formattedDate = $yesterdayDateTime->format('F j, Y');
            $formattedDate = strtoupper($formattedDate); */

            /* dd($formattedDate); */

            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/finished-goods-9b2565bb6e35.json'));
            $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);

            $service = new Google_Service_Sheets($client);

            $spreadsheetId = '1qPRNb5MK135DogIi7QlunasYYa_hkYEe50wWCB18yWE';
            $range = "{$formattedDate}!A:D";

            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();

            return array_slice(array_map(function($row) {
                return [
                    isset($row[0]) ? $row[0] : '',  
                    isset($row[3]) ? $row[3] : ''   
                ];
            }, $values), 2);
        }catch(ValidationException $e) {
            return redirect()->back()
                ->with('message', 'INVALID!')
                ->with('isError', true);
        }
    }


    public function ItemOrders($journalid)
    {
        $storeName = Auth::user()->storeid;
        $currentDate = Carbon::now('Asia/Manila')->toDateString();

        $journalPosted = DB::table('inventjournaltables')
            ->where('journalid', $journalid)
            ->where('storeid', $storeName)
            ->where('posted', 0)
            ->count();

        if ($journalPosted <= 0) {
            return redirect()->route('order.index')
                ->with('message', 'This has already been posted. If you want to view your order, please click on the Report module in the left sidebar')
                ->with('isError', true);
        }

        $inventJournalTransRepos = DB::table('inventjournaltransrepos AS a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables AS b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables AS c', 'b.itemid', '=', 'c.itemid')
            ->where('a.itemdepartment', 'REGULAR PRODUCT')
            ->where('a.journalid', $journalid)
            ->where('a.storename', $storeName)
            ->where('a.status', '=', '0')
            ->get();

        
        $storeType = DB::table('rbostoretables AS a')
            ->leftJoin('users AS b', 'a.name', '=', 'b.storeid')
            ->where('a.name', $storeName)
            ->where('a.types', 'NONE')
            ->count();

        if ($storeType === 1) {
            return Inertia::render('ItemOrders/index3', [
                'journalid' => $journalid,
                'inventjournaltransrepos' => $inventJournalTransRepos,
            ]);
        } else {
            return Inertia::render('ItemOrders/index', [
                'journalid' => $journalid,
                'inventjournaltransrepos' => $inventJournalTransRepos,
            ]);
        }
    }

    
    public function EnableOrder(Request $request)
    {
    \Log::info('Received itemids:', $request->all());

    $request->validate([
        'itemids' => 'required|array',
        'itemids.*' => 'exists:rboinventtables,itemid',
    ]);

    $itemids = $request->itemids;

    $updatedItems = rboinventtables::whereIn('itemid', $itemids)->get();

    foreach ($updatedItems as $item) {
        $item->Activeondelivery = $item->Activeondelivery == 1 ? 0 : 1;
        $item->save();
    }

    $enabledCount = $updatedItems->where('blocked', 1)->count();
    $disabledCount = $updatedItems->where('blocked', 0)->count();

    return response()->json([
        'message' => "Updated successfully. Enabled: $enabledCount, Disabled: $disabledCount items.",
        'enabled' => $enabledCount,
        'disabled' => $disabledCount
    ]);
    }


    public function updateCountedValue(Request $request)
    {
        try {
            \Log::info('updateCountedValue method reached', $request->all());

            $request->validate([
                'journalId' => 'required|string',
                'itemId' => 'required|string',
                'newValue' => 'required|numeric|min:0',
            ]);

            $record = inventjournaltransrepos::where('JOURNALID', $request->journalId)
                ->where('ITEMID', $request->itemId)
                ->first();

            if (!$record) {
                \Log::warning('Record not found', $request->all());
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found',
                ], 404);
            }

            $record->COUNTED = $request->newValue;
            $record->save();

            \Log::info('Record updated successfully', ['record' => $record]);

            return response()->json([
                'success' => true,
                'message' => 'Counted value updated successfully',
            ]);

        } catch (ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating counted value', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the counted value: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateAllCountedValues3(Request $request)
    {
        try {
            \Log::info('updateAllCountedValues method reached', $request->all());
            $request->validate([
                'journalId' => 'required|string',
                'updatedValues' => 'required|array',
            ]);

            $journalId = $request->journalId;
            $updatedValues = $request->updatedValues;

            \DB::beginTransaction();

            foreach ($updatedValues as $itemId => $newValue) {
                $record = inventjournaltransrepos::where('JOURNALID', $journalId)
                    ->where('ITEMID', $itemId)
                    ->first();

                if ($record) {
                    $record->COUNTED = $newValue;
                    $record->save();
                } else {
                    \Log::warning("Record not found for ITEMID: $itemId", ['journalId' => $journalId]);
                }
            }

            \DB::commit();

            \Log::info('All records updated successfully');
            return response()->json([
                'success' => true,
                'message' => 'All counted values updated successfully',
            ]);
        } catch (ValidationException $e) {
            \DB::rollBack();
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating counted values', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the counted values: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function updateAllCountedValues(Request $request)
    {
    try {
        \Log::info('updateAllCountedValues method reached', $request->all());
        $request->validate([
            'journalId' => 'required|string',
            'updatedValues' => 'required|array',
        ]);
        $journalId = $request->journalId;
        $updatedValues = $request->updatedValues;
        
        $errors = [];
        $successCount = 0;

        \DB::beginTransaction();
        foreach ($updatedValues as $itemId => $newValue) {
            $record = inventjournaltransrepos::where('JOURNALID', $journalId)
                ->where('ITEMID', $itemId)
                ->first();

            if ($record) {
                if ($newValue == 0 || $newValue >= $record->moq) {
                    $record->COUNTED = $newValue;
                    $record->save();
                    $successCount++;
                } else {
                    $errors[] = "ITEMID: $itemId - Updated value must be equal to or greater than MOQ ({$record->moq}).";
                }
            } else {
                $errors[] = "Record not found for ITEMID: $itemId";
            }
        }
        
        if (empty($errors)) {
            \DB::commit();
            \Log::info("$successCount records updated successfully");
            return response()->json([
                'success' => true,
                'message' => "$successCount counted values updated successfully",
            ]);
        } else {
            \DB::rollBack();
            return response()->json([
                'message' => "ITEMID: $itemId - Updated value must be equal to or greater than MOQ ({$record->moq}).",
            ]);
        }
    } catch (ValidationException $e) {
        \DB::rollBack();
        \Log::error('Validation failed', ['errors' => $e->errors()]);
        return response()->json([
            'success' => false,
            'message' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Error updating counted values', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json([
            'message' => "You don't have any changes!",
        ]);
    }
}
    
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'itemname'=> 'required|string',  
                'qty'=> 'required|integer',  
            ]);
            
            $utcDateTime = Carbon::now('UTC');
            $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            inventjournaltransrepos::create([
                'JOURNALID'=> $request->JOURNALID,
                'LINENUM'=> '',
                'TRANSDATE'=> $currentDateTime,
                'ITEMID'=> $request->itemname,
                'COUNTED'=> $request->qty,    
                'updated_at'=> $currentDateTime,                
            ]);

            $journalid = $request->JOURNALID;
            return redirect()->route('ItemOrders', ['journalid' => $journalid])
            ->with('message', 'Order Created Successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message',$e->errors())
            ->with('isSuccess', false);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
            try {

                $request->validate([
                    'JOURNALID' => 'required|string',  
                ]);

                $utcDateTime = Carbon::now('UTC');
                $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

                $journalid = $request->JOURNALID;

                $record = DB::table('inventjournaltransrepos')
                ->select('JOURNALID')
                ->where('journalid', $journalid)
                ->count();
                

                if ($record >= 1) {
                    return redirect()->route('ItemOrders', ['journalid' => $journalid])
                    ->with('message', 'You have already generated items!')
                    ->with('isError', true);

                } else {

                    if($request->EndDate != null){

                        $storename = Auth::user()->storeid;

                        DB::insert(
                            'INSERT INTO inventjournaltransrepos (JOURNALID, TRANSDATE, ITEMID, COUNTED, STORENAME)
                             SELECT ?, ?, itemid, counted
                             FROM inventjournaltrans
                             WHERE DATE(POSTEDDATETIME) = ? and STORENAME = ?',
                            [$request->JOURNALID, $currentDateTime, $request->EndDate, $storename]
                        );
                    
                        return redirect()->route('ItemOrders', ['journalid' => $request->JOURNALID])
                            ->with('message', 'Generate Item Successfully')
                            ->with('isSuccess', true);   
                        
                    }else{
                        $storename = Auth::user()->storeid;
                        
                        DB::table('inventjournaltransrepos')
                        ->insertUsing(
                            ['JOURNALID', 'TRANSDATE', 'ITEMID', 'COUNTED', 'STORENAME', 'MOQ'],
                            function ($query) use ($request, $currentDateTime, $storename) {
                                $query->select(
                                        DB::raw("'{$request->JOURNALID}' as JOURNALID"),
                                        DB::raw("'{$currentDateTime}' as TRANSDATE"),
                                        'a.itemid as ITEMID',
                                        DB::raw('0 as COUNTED'),
                                        DB::raw("'{$storename}' as STORENAME"),
                                        DB::raw("CASE WHEN b.moq IS NULL THEN 0 ELSE b.moq END")
                                    )
                                    ->from('inventtables as a')
                                    ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                                    ->where('b.activeondelivery', '1');
                            }
                        );
        
                        return redirect()->route('ItemOrders', ['journalid' => $journalid])
                        ->with('message', 'Generate Item Successfully')
                        ->with('isSuccess', true);

                        }
                }

                
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())
                    ->withInput()
                    ->with('message', $e->errors())
                    ->with('isSuccess', false);
            }
    }

    public function getbwproducts(Request $request)
    {
            try {

                $request->validate([
                    'JOURNALID' => 'required|string',  
                ]);

                $utcDateTime = Carbon::now('UTC');
                $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

                $journalid = $request->JOURNALID;

                $record = DB::table('inventjournaltransrepos')
                ->select('JOURNALID')
                ->where('journalid', $journalid)
                ->count();
                

                if ($record >= 1) {
                    return redirect()->route('ItemOrders', ['journalid' => $journalid])
                    ->with('message', 'You have already generated items!')
                    ->with('isError', true);

                } else {

                    if($request->EndDate != null){

                        $storename = Auth::user()->storeid;

                        DB::insert(
                            'INSERT INTO inventjournaltransrepos (JOURNALID, TRANSDATE, ITEMID, COUNTED, STORENAME)
                             SELECT ?, ?, itemid, counted
                             FROM inventjournaltrans
                             WHERE DATE(POSTEDDATETIME) = ? and STORENAME = ?',
                            [$request->JOURNALID, $currentDateTime, $request->EndDate, $storename]
                        );
                    
                        return redirect()->route('ItemOrders', ['journalid' => $request->JOURNALID])
                            ->with('message', 'Generate Item Successfully')
                            ->with('isSuccess', true);   
                        
                    }else{

                                $storename = Auth::user()->storeid;
                                DB::table('inventjournaltransrepos')
                                ->insertUsing(
                                    ['JOURNALID', 'ITEMDEPARTMENT', 'TRANSDATE', 'ITEMID', 'COUNTED', 'STORENAME', 'MOQ', 'STATUS'],
                                    function ($query) use ($request, $currentDateTime, $storename) {
                                        $query->select(
                                                DB::raw("'{$request->JOURNALID}' as JOURNALID"),
                                                'b.itemdepartment',
                                                DB::raw("'{$currentDateTime}' as TRANSDATE"),
                                                'a.itemid as ITEMID',
                                                DB::raw('0 as COUNTED'),
                                                DB::raw("'{$storename}' as STORENAME"),
                                                DB::raw("CASE WHEN b.moq IS NULL THEN 0 ELSE b.moq END"),
                                                DB::raw('0 as STATUS')
                                            )
                                            ->from('inventtables as a')
                                            ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                                            ->where('b.activeondelivery', '1');
                                    } 
                                );
                
                                return redirect()->route('ItemOrders', ['journalid' => $journalid])
                                ->with('message', 'Generate Item Successfully')
                                ->with('isSuccess', true);

                            
                    }
                }

                
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())
                    ->withInput()
                    ->with('message', $e->errors())
                    ->with('isSuccess', false);
            }
    }

    public function destroy(string $id, Request $request)
    {
        try {
            $request->validate([
                'JOURNALID' => 'required|exists:inventjournaltrans,JOURNALID',
                'LINENUM' => 'required|exists:inventjournaltrans,LINENUM',
            ]);

            inventjournaltrans::where('journalid', $request->JOURNALID)
            ->where('linenum', $request->LINENUM)
            ->delete();

            $journalid = $request->JOURNALID;

            return redirect()->route('ItemOrders', ['journalid' => $journalid])
            ->with('message', 'Order deleted successfully')
            ->with('isSuccess', true);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }

    public function saveFile(Request $request)
    {
        $content = $request->input('content');
        $filename = $request->input('filename');
        $folderName = $request->input('folderName');
        $storename = Auth::user()->storeid;

        $folderPath = 'public/' . $folderName;
        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath);
        }

        $filePath = $folderPath . '/' . $filename;
        Storage::put($filePath, $content);

        inventjournaltables::where('STOREID', $storename)
            ->update([
                'sent' => 1,
            ]);

        return response()->json(['success' => true, 'path' => Storage::url($filePath)]);
    }

    public function post(Request $request)
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storename = Auth::user()->storeid;
        $journalid = $request->journalid;
        $role = Auth::user()->role;
   
        DB::beginTransaction();
        try {
            
            $affected = DB::insert('
            INSERT INTO inventjournaltrans 
            (JOURNALID, TRANSDATE, ITEMID, ITEMDEPARTMENT, ADJUSTMENT, COSTPRICE, PRICEUNIT, SALESAMOUNT, INVENTONHAND, COUNTED, REASONREFRECID, VARIANTID, POSTED, POSTEDDATETIME, UNITID, updated_at)
            SELECT ?, ?, itemid, itemdepartment, counted, 0.00, 0.00, 0.00, 0, counted, \'00001\', 0, 0, ?, \'PCS\', updated_at
            FROM inventjournaltransrepos
            WHERE storename = ? AND journalid = ?
            ON DUPLICATE KEY UPDATE
            TRANSDATE = VALUES(TRANSDATE),
            ADJUSTMENT = VALUES(ADJUSTMENT),
            COUNTED = VALUES(COUNTED),
            POSTEDDATETIME = VALUES(POSTEDDATETIME),
            updated_at = VALUES(updated_at)
        ', [$journalid, $currentDateTime, $currentDateTime, $storename, $journalid]);

            $request->validate([
                'journalid' => 'required|exists:inventjournaltrans,journalid',
            ]);

            $start_time = Carbon::createFromTime(17, 0, 0); 
            $end_time = Carbon::createFromTime(5, 0, 0)->addDay(); 

            $evening_start = Carbon::createFromTime(17, 1, 0);
            $morning_end = Carbon::createFromTime(5, 0, 0)->addDay();

            $morning_start = Carbon::createFromTime(5, 1, 0);
            $evening_end = Carbon::createFromTime(17, 0, 0);

            $record = 
            DB::table('inventjournaltables')
            ->where('journalid', $journalid)
            ->where('storeid', $storename)
            ->whereDate('createddatetime', $currentDateTime)
            ->where('posted', 0)
            ->count();

            if ($record <= 0) {
                return redirect()->route('order.index')
                ->with('message', 'Posting all items is not allowed.')
                ->with('isError', true);

            } 

            else {


                inventjournaltables::where('journalid',$request->journalid)
                ->where('journalid',$request->journalid)
                ->whereDate('createddatetime', $currentDateTime)
                ->update([
                    'posted'=> '1',
                ]);

                inventjournaltrans::where('journalid',$request->journalid)
                ->where('journalid',$request->journalid)
                ->whereDate('transdate', $currentDateTime)
                ->update([
                    'VARIANTID'=> '1',
                ]);

                }

            DB::commit();
            return $affected;
                return response()->json([
                    'success' => true,
                    'message' => 'Order posted successfully'
                ]);


        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }

    /* public function post(Request $request)
    {

        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storename = Auth::user()->storeid;
        $journalid = $request->journalid;
        $role = Auth::user()->role;
   
        try {
            
            DB::insert('INSERT INTO inventjournaltrans
                    (JOURNALID, TRANSDATE, ITEMID, ADJUSTMENT, COSTPRICE, PRICEUNIT, SALESAMOUNT, INVENTONHAND, COUNTED, REASONREFRECID, VARIANTID, POSTED, POSTEDDATETIME, UNITID, updated_at)
                    SELECT ?, ?, itemid, counted, 0.00, 0.00, 0.00, 0, counted, \'00001\', 0, 0, ?, \'PCS\', updated_at 
                    FROM inventjournaltransrepos 
                    WHERE storename = ? and journalid = ?', 
                    [$journalid, $currentDateTime, $currentDateTime, $storename, $journalid]
                );

            $request->validate([
                'journalid' => 'required|exists:inventjournaltrans,journalid',
            ]);

            $start_time = Carbon::createFromTime(17, 0, 0); 
            $end_time = Carbon::createFromTime(5, 0, 0)->addDay(); 

            $evening_start = Carbon::createFromTime(17, 1, 0);
            $morning_end = Carbon::createFromTime(5, 0, 0)->addDay();

            $morning_start = Carbon::createFromTime(5, 1, 0);
            $evening_end = Carbon::createFromTime(17, 0, 0);

            $record = 
            DB::table('inventjournaltables')
            ->where('journalid', $journalid)
            ->where('storeid', $storename)
            ->whereDate('createddatetime', $currentDateTime)
            ->where('posted', 0)
            ->count();

            if ($record <= 0) {
                return redirect()->route('order.index')
                ->with('message', 'Posting all items is not allowed.')
                ->with('isError', true);

            } 
            else {

                $final = DB::table('inventjournaltransrepos as a')
                ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
                ->where('c.itemdepartment', 'REGULAR PRODUCT')
                ->where('a.journalid', $journalid)
                ->where('a.storename', $storename)
                ->groupBy('a.itemid', 'b.itemid', 'c.itemid', 'a.journalid', 'a.storename')
                ->selectRaw('SUM(CAST(a.counted AS FLOAT)) as total_counted')
                ->havingRaw('total_counted >= 1')
                ->count();

                if($final != 0){
                    inventjournaltables::where('journalid',$request->journalid)
                    ->where('journalid',$request->journalid)
                    ->whereDate('createddatetime', $currentDateTime)
                    ->update([
                        'posted'=> '1',
                    ]);

                    inventjournaltrans::where('journalid',$request->journalid)
                    ->where('journalid',$request->journalid)
                    ->whereDate('transdate', $currentDateTime)
                    ->update([
                        'VARIANTID'=> '1',
                    ]);

                    inventjournaltrans::where('journalid',$request->journalid)
                    ->where('journalid',$request->journalid)
                    ->whereDate('transdate', $currentDateTime)
                    ->update([
                        'MGCOUNT'=> 0,
                    ]);

                    inventjournaltrans::where('journalid',$request->journalid)
                    ->where('journalid',$request->journalid)
                    ->whereDate('transdate', $currentDateTime)
                    ->update([
                        'BALANCECOUNT'=> 0,
                    ]);

                    inventjournaltrans::where('journalid',$request->journalid)
                    ->where('journalid',$request->journalid)
                    ->whereDate('transdate', $currentDateTime)
                    ->update([
                        'CHECKINGCOUNT'=> 0,
                    ]);

                    DB::table('inventjournaltransrepos')
                    ->where('journalid', $request->journalid)
                    ->delete();

                    return redirect()->route('order.index')
                    ->with('message', 'Order posted successfully')
                    ->with('isSuccess', true);

                }else{
                    return redirect()->route('order.index')
                    ->with('message', `You don't have an order yet. Please place your order first and make sure you have saved all your items!`)
                    ->with('isError', true);
                }

                
            }


        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    } */

    public function updatefg(Request $request){
        inventjournaltransrepos::where('journalid',$request->journalid)
                ->where('journalid',$request->journalid)
                ->whereDate('transdate', $currentDateTime)
                ->update([
                    'status'=> 1,
                ]);

                $items = DB::table('inventjournaltrans')
                    ->where('JOURNALID', $request->journalid)
                    ->whereDate('POSTEDDATETIME', $currentDateTime)
                    ->select('ITEMID', 'COUNTED')
                    ->get();

                foreach ($items as $item) {
                    DB::table('rboinventtables')
                        ->where('itemid', $item->ITEMID)
                        ->update([
                            'stocks' => DB::raw("stocks - {$item->COUNTED}")
                        ]);
                }
    }

    public function DeleteOrders(Request $request)
    {
        try {
        DB::table('inventjournaltransrepos')->truncate();
        return redirect()->route('order.index')
                ->with('message', 'Delete order successfully')
                ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }


    /*WAREHOUSE */
    public function warehouseorders($journalid)
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $record = DB::table('inventjournaltables')
            ->where('journalid', $journalid)
            ->where('storeid', $storename)
            ->where('posted', 0)
            ->count();

            if ($record <= 0) {
                return redirect()->route('order.index')
                ->with('message', 'This has already been posted. If you want to view your order, please click on the Report module in the left sidebar')
                ->with('isError', true);

            } else {

            $inventjournaltransrepos = DB::table('inventjournaltransrepos as a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->where('c.itemdepartment','NON PRODUCT')
            ->where('a.journalid',$journalid)
            ->where('a.storename', $storename)
            ->get();

            return Inertia::render('ItemOrders/warehouse', [
                'journalid' => $journalid,
                'inventjournaltransrepos' => $inventjournaltransrepos,
            ]);
            }

    }


    public function wViewOrders($journalid)
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $record = DB::table('inventjournaltables')
            ->where('journalid', $journalid)
            ->where('storeid', $storename)
            ->where('posted', 0)
            ->count();

            if ($record <= 0) {
                return redirect()->route('order.index')
                ->with('message', 'This has already been posted. If you want to view your order, please click on the Report module in the left sidebar')
                ->with('isError', true);

            } else {

            $inventjournaltransrepos = DB::table('inventjournaltransrepos as a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->where('c.itemdepartment','NON PRODUCT')
            ->where('a.journalid',$journalid)
            ->where('a.storename', $storename)
            ->where('a.counted', '!=', '0')
            ->get();

            return Inertia::render('ItemOrders/warehousecart', [
                'journalid' => $journalid,
                'inventjournaltransrepos' => $inventjournaltransrepos,
            ]);
            }
    }
}

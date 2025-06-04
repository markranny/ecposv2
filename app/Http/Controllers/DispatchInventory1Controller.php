<?php

namespace App\Http\Controllers;

use App\Models\inventjournaltables;
use App\Models\inventjournaltrans;
use App\Models\inventjournaltransrepos;
use App\Models\numbersequencevalues;
use App\Models\inventtables;
use App\Models\rboinventtables;
use App\Models\inventtablemodules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DispatchInventory1Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
                
                    return redirect()->route('dispatch-items', ['journalid' => $request->JOURNALID])
                        ->with('message', 'Generate Item Successfully')
                        ->with('isSuccess', true);   
                    
                }else{
                    $storename = Auth::user()->storeid;
                    $journalid = $request->JOURNALID;
                    
                    DB::table('inventjournaltransrepos')
                    ->insertUsing(
                        ['JOURNALID', 'TRANSDATE', 'ITEMID', 'COUNTED', 'STORENAME'],
                        function ($query) use ($request, $currentDateTime, $storename) {
                            $query->select(
                                    DB::raw("'{$request->JOURNALID}' as JOURNALID"),
                                    DB::raw("'{$currentDateTime}' as TRANSDATE"),
                                    'a.itemid as ITEMID',
                                    DB::raw('0 as COUNTED'),
                                    DB::raw("'{$storename}' as STORENAME")
                                )
                                ->from('inventtables as a')
                                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                                ->where('b.activeondelivery', '1');
                        }
                    );

                    DB::insert('INSERT INTO inventjournaltrans
                    (JOURNALID, TRANSDATE, ITEMID, ADJUSTMENT, COSTPRICE, PRICEUNIT, SALESAMOUNT, INVENTONHAND, COUNTED, REASONREFRECID, VARIANTID, POSTED, POSTEDDATETIME, UNITID, updated_at)
                    SELECT ?, ?, itemid, counted, 0.00, 0.00, 0.00, 0, counted, \'00001\', 0, 0, ?, \'PCS\', updated_at 
                    FROM inventjournaltransrepos 
                    WHERE storename = ? and journalid = ?', 
                    [$journalid, $currentDateTime, $currentDateTime, $storename, $journalid]
                    );

                    inventjournaltables::where('journalid',$request->JOURNALID)
                    ->where('journalid',$request->JOURNALID)
                    ->whereDate('createddatetime', $currentDateTime)
                    ->update([
                        'posted'=> '1',
                    ]);

                    DB::table('inventjournaltransrepos')
                    ->where('journalid', $request->JOURNALID)
                    ->delete();
    
                    return redirect()->route('dispatch-items', ['journalid' => $journalid])
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
            try {

                $request->validate([
                    'JOURNALID' => 'required|string',  
                ]);

                $utcDateTime = Carbon::now('UTC');
                $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

                $journalid = $request->JOURNALID;

                $record = DB::table('inventjournaltrans')
                ->select('JOURNALID')
                ->where('journalid', $journalid)
                ->count();
                

                if ($record >= 1) {
                    return redirect()->route('dispatch-items', ['journalid' => $journalid])
                    ->with('message', 'You have already generated items!')
                    ->with('isError', true);

                } else {

                    if($request->EndDate != null){

                        $storename = Auth::user()->storeid;

                        DB::insert(
                            'INSERT INTO inventjournaltrans (JOURNALID, TRANSDATE, ITEMID, COUNTED, STORENAME)
                             SELECT ?, ?, itemid, counted
                             FROM inventjournaltrans
                             WHERE DATE(POSTEDDATETIME) = ? and STORENAME = ?',
                            [$request->JOURNALID, $currentDateTime, $request->EndDate, $storename]
                        );
                    
                        return redirect()->route('dispatch-items', ['journalid' => $request->JOURNALID])
                            ->with('message', 'Generate Item Successfully')
                            ->with('isSuccess', true);   
                        
                    }else{
                        $storename = Auth::user()->storeid;
                        
                        DB::table('inventjournaltrans
                        ')
                        ->insertUsing(
                            ['JOURNALID', 'TRANSDATE', 'ITEMID', 'COUNTED', 'STORENAME'],
                            function ($query) use ($request, $currentDateTime, $storename) {
                                $query->select(
                                        DB::raw("'{$request->JOURNALID}' as JOURNALID"),
                                        DB::raw("'{$currentDateTime}' as TRANSDATE"),
                                        'a.itemid as ITEMID',
                                        DB::raw('0 as COUNTED'),
                                        DB::raw("'{$storename}' as STORENAME")
                                    )
                                    ->from('inventtables as a')
                                    ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                                    ->where('b.activeondelivery', '1');
                            }
                        );
        
                        return redirect()->route('dispatch-items', ['journalid' => $journalid])
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

            \DB::beginTransaction();

            foreach ($updatedValues as $itemId => $newValue) {
                $record = inventjournaltrans::where('JOURNALID', $journalId)
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
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

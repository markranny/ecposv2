<?php

namespace App\Http\Controllers;
use App\Models\receivedordertables;
use App\Models\receivedordertrans;
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

class ReceivedItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $storename = Auth::user()->storeid;

        $receivedordertrans = DB::table('receivedordertables as a')
        ->Join('receivedordertrans as b', 'a.JOURNALID', '=', 'b.JOURNALID')
        ->leftJoin('inventtables as c', 'b.ITEMID', '=', 'c.itemid')
        ->where('a.STOREID', '=', $storename) 
        ->get();
    
        return Inertia::render('DeliveryItems/index', ['receivedordertrans' => $receivedordertrans]);
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
        try {

            $request->validate([
                'itemname'=> 'required|string',  
                'qty'=> 'required|integer',  
            ]);
            
            $utcDateTime = Carbon::now('UTC');
            $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            receivedordertrans::create([
                'JOURNALID'=> $request->JOURNALID,
                'LINENUM'=> '',
                'TRANSDATE'=> $currentDateTime,
                'ITEMID'=> $request->itemname,
                'COUNTED'=> $request->qty,    
                'updated_at'=> $currentDateTime,                
            ]);

            $journalid = $request->JOURNALID;
            return redirect()->route('ReceivedOrders', ['journalid' => $journalid])
            ->with('message', 'Received Order Successfully')
            ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message',$e->errors())
            ->with('isSuccess', false);
        }
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

            $record = DB::table('receivedordertrans')
            ->select('JOURNALID')
            ->where('journalid', $journalid)
            ->count();
            

            if ($record >= 1) {
                return redirect()->route('ReceivedItmes', ['journalid' => $journalid])
                ->with('message', 'You have already generated items!')
                ->with('isError', true);

            } else {

                    $storename = Auth::user()->storeid;
                    
                    DB::table('receivedordertrans')
                    ->insertUsing(
                        ['JOURNALID', 'TRANSDATE', 'ITEMID', 'COUNTED', 'STORENAME', 'MOQ'],
                        function ($query) use ($request, $currentDateTime, $storename) {
                            $query->select(
                                    DB::raw("'{$request->JOURNALID}' as JOURNALID"),
                                    DB::raw("'{$currentDateTime}' as TRANSDATE"),
                                    'a.itemid as ITEMID',
                                    DB::raw('0 as COUNTED'),
                                    DB::raw("'{$storename}' as STORENAME"),
                                )
                                ->from('inventtables as a')
                                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                                ->where('b.activeondelivery', '1');
                        }
                    );
    
                    return redirect()->route('ReceivedOrders', ['journalid' => $journalid])
                    ->with('message', 'Generate Item Successfully')
                    ->with('isSuccess', true);

            }

            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function ReceivedOrders($journalid)
    {
        $storeName = Auth::user()->storeid;
        $currentDate = Carbon::now('Asia/Manila')->toDateString();

        $journalPosted = DB::table('receivedordertables')
            ->where('journalid', $journalid)
            ->where('storeid', $storeName)
            ->where('posted', 0)
            ->count();

        if ($journalPosted <= 0) {
            return redirect()->route('order.index')
                ->with('message', 'This has already been posted. If you want to view your order, please click on the Report module in the left sidebar')
                ->with('isError', true);
        }

        $receivedordertrans = DB::table('receivedordertrans AS a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables AS b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables AS c', 'b.itemid', '=', 'c.itemid')
            ->where('a.itemdepartment', 'REGULAR PRODUCT')
            ->where('a.journalid', $journalid)
            ->where('a.storename', $storeName)
            /* ->where('a.status', '=', '0') */
            ->get();

            return Inertia::render('DeliveryItems/index', [
                'journalid' => $journalid,
                'receivedordertrans' => $receivedordertrans,
            ]);
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

                $record = DB::table('receivedordertrans')
                ->select('JOURNALID')
                ->where('journalid', $journalid)
                ->count();
                

                if ($record >= 1) {
                    return redirect()->route('ReceivedOrders', ['journalid' => $journalid])
                    ->with('message', 'You have already generated items!')
                    ->with('isError', true);

                } else {
                                $storename = Auth::user()->storeid;
                                DB::table('receivedordertrans')
                                ->insertUsing(
                                    ['JOURNALID', 'ITEMDEPARTMENT', 'TRANSDATE', 'ITEMID', 'COUNTED', 'STORENAME'],
                                    function ($query) use ($request, $currentDateTime, $storename) {
                                        $query->select(
                                                DB::raw("'{$request->JOURNALID}' as JOURNALID"),
                                                'b.itemdepartment',
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
                
                                return redirect()->route('ReceivedOrders', ['journalid' => $journalid])
                                ->with('message', 'Generate Item Successfully')
                                ->with('isSuccess', true);
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
        
        $errors = [];
        $successCount = 0;

        \DB::beginTransaction();
        foreach ($updatedValues as $itemId => $newValue) {
            $record = receivedordertrans::where('JOURNALID', $journalId)
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

public function ViewOrders($journalid)
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $record = DB::table('receivedordertables')
            ->where('journalid', $journalid)
            ->where('storeid', $storename)
            ->where('posted', 0)
            ->count();

            if ($record <= 0) {
                return redirect()->route('Received.index')
                ->with('message', 'This has already been posted. If you want to view your order, please click on the Report module in the left sidebar')
                ->with('isError', true);

            } else {

            $receivedordertrans = DB::table('receivedordertrans as a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->where('a.journalid',$journalid)
            ->where('a.storename', $storename)
            ->where('a.itemdepartment', 'REGULAR PRODUCT')
            ->where('a.counted', '!=', '0')
            ->get();

            return Inertia::render('DeliveryItems/index2', [
                'journalid' => $journalid,
                'receivedordertrans' => $receivedordertrans,
            ]);
            }

    }








    /*WAREHOUSE */
    public function warehouseorders($journalid)
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $record = DB::table('receivedordertables')
            ->where('journalid', $journalid)
            ->where('storeid', $storename)
            ->where('posted', 0)
            ->count();

            if ($record <= 0) {
                return redirect()->route('Received.index')
                ->with('message', 'This has already been posted. If you want to view your order, please click on the Report module in the left sidebar')
                ->with('isError', true);

            } else {

            $receivedordertrans = DB::table('receivedordertrans as a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->where('c.itemdepartment','NON PRODUCT')
            ->where('a.journalid',$journalid)
            ->where('a.storename', $storename)
            ->get();

            return Inertia::render('DeliveryItems/warehouse', [
                'journalid' => $journalid,
                'receivedordertrans' => $receivedordertrans,
            ]);
            }

    }


    public function wViewOrders($journalid)
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $record = DB::table('receivedordertables')
            ->where('journalid', $journalid)
            ->where('storeid', $storename)
            ->where('posted', 0)
            ->count();

            if ($record <= 0) {
                return redirect()->route('Received.index')
                ->with('message', 'This has already been posted. If you want to view your order, please click on the Report module in the left sidebar')
                ->with('isError', true);

            } else {

            $receivedordertrans = DB::table('receivedordertrans as a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->where('c.itemdepartment','NON PRODUCT')
            ->where('a.journalid',$journalid)
            ->where('a.storename', $storename)
            ->where('a.counted', '!=', '0')
            ->get();

            return Inertia::render('DeliveryItems/warehousecart', [
                'journalid' => $journalid,
                'receivedordertrans' => $receivedordertrans,
            ]);
            }
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
            
            $affected = DB::table('receivedordertables')
            ->whereDate(DB::raw('cast(posteddatetime as date)'), $currentDateTime)
            ->update(['posted' => '1']);

            DB::commit();
            return $affected;
                return response()->json([
                    'success' => true,
                    'message' => 'Received Order posted successfully'
                ]);


        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
    }
}

<?php

namespace App\Http\Controllers;
use App\Models\StockTransferTable;
use App\Models\StockTransferLine;
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

class StockTransferLineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $storename = Auth::user()->storeid;

        $stocktransfertrans = DB::table('stocktransfertables as a')
        ->Join('stocktransferline as b', 'a.JOURNALID', '=', 'b.JOURNALID')
        ->leftJoin('inventtables as c', 'b.ITEMID', '=', 'c.itemid')
        ->get();
    
        return Inertia::render('StockTransferLine/index', ['stocktransfertrans' => $stocktransfertrans]);
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
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

            $record = DB::table('stocktransferline')
                ->select('JOURNALID')
                ->where('journalid', $journalid)
                ->count();

            if ($record >= 1) {
                return redirect()->route('StockTransferLine', ['journalid' => $journalid])
                    ->with('message', 'You have already generated items!')
                    ->with('isError', true);
            } else {
                $storename = Auth::user()->storeid;

                DB::beginTransaction();

                try {
                    DB::table('stocktransferline')
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

                    DB::commit();

                    return redirect()->route('StockTransferLine', ['journalid' => $journalid])
                        ->with('message', 'Generate Item Successfully')
                        ->with('isSuccess', true);

                } catch (\Exception $e) {
                    DB::rollBack();

                    return back()->with('message', 'Error occurred: ' . $e->getMessage())
                        ->with('isError', true);
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
        $successCount = 0;

        \DB::beginTransaction();
        
        // Only update the specific items in updatedValues
        foreach ($updatedValues as $itemId => $newValue) {
            $updateResult = StockTransferLine::where('JOURNALID', $journalId)
                ->where('ITEMID', $itemId)
                ->update(['COUNTED' => $newValue]);
                
            if ($updateResult) {
                $successCount++;
                \Log::info("Updated item: $itemId with value: $newValue");
            }
        }
        
        \DB::commit();
        \Log::info("$successCount records updated successfully");
        
        return response()->json([
            'success' => true,
            'message' => "$successCount counted values updated successfully",
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
        \Log::error('Error updating counted values', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => "An error occurred while updating values",
        ], 500);
    }
}


}

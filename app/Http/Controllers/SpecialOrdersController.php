<?php

namespace App\Http\Controllers;
use App\Models\sptables;
use App\Models\sptrans;
use App\Models\sptransrepos;
use App\Models\rbostoretables;
use App\Models\numbersequencevalues;
use App\Models\inventtables;
use App\Models\rboinventtables;
use App\Models\inventtablemodules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class SpecialOrdersController extends Controller
{

    public function SpecialOrders()
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $journalid = "SPECIAL ORDER";
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $excludedNames = ['HQ', 'DEMO'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

                $items = DB::table('inventtablemodules AS a')
                ->select([
                    'a.ITEMID AS itemid',
                    'b.itemname AS itemname',
                    'c.itemgroup AS itemgroup',
                    'c.itemdepartment AS specialgroup',
                    DB::raw('ROUND(a.priceincltax, 2) AS price'),
                    DB::raw('ROUND(a.price, 2) AS cost'),
                    DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.ITEMBARCODE ELSE 'N/A' END AS barcode")
                ])
                ->leftJoin('inventtables AS b', 'a.ITEMID', '=', 'b.itemid')
                ->leftJoin('rboinventtables AS c', 'b.itemid', '=', 'c.itemid')
                ->leftJoin('inventitembarcodes AS d', 'c.barcode', '=', 'd.ITEMBARCODE')
                ->whereNotIn('a.ITEMID', function($query) use ($journalid) {
                    $query->select('b.ITEMID')
                        ->from('sptables AS a')
                        ->join('sptrans AS b', 'a.JOURNALID', '=', 'b.JOURNALID')
                        ->whereNotNull('b.ITEMID')
                        ->where('a.STOREID', '=', Auth::user()->storeid)
                        ->where('a.journalid', '=', $journalid); 
                })
                ->orderBy('b.itemname', 'ASC')
                ->get();

            $sptransrepos = DB::table('sptransrepos as a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            /* ->where('a.journalid',$journalid)
            ->where('a.storename', $storename) */
            ->get();

            $sptrans = DB::table('sptables as a')
                ->join('sptrans as b', 'a.JOURNALID', '=', 'b.JOURNALID')
                ->leftJoin('inventtables as c', 'b.ITEMID', '=', 'c.itemid')
                /* ->where('a.STOREID', '=', $storename) 
                ->where('a.journalid', '=', $journalid) */
                ->get();

            return Inertia::render('SpecialOrders/index', [
                'journalid' => $journalid,
                'sptransrepos' => $sptransrepos,
                'sptrans' => $sptrans,
                'items' => $items,
                'rbostoretables' => $rbostoretables,
            ]);

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
                $record = sptransrepos::where('JOURNALID', $journalId)
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

    public function DeleteOrders(Request $request)
    {
        try {
        DB::table('sptransrepos')->truncate();
        return redirect()->route('specialorders')
                ->with('message', 'Delete order successfully')
                ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
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
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {

            $request->validate([
                'JOURNALID' => 'required|string',  
            ]);

            $utcDateTime = Carbon::now('UTC');
            $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $journalid = $request->JOURNALID;

            $record = DB::table('sptransrepos')
            ->select('JOURNALID')
            ->where('journalid', $journalid)
            ->count();
            

            if ($record >= 1) {
                return redirect()->route('specialorders', ['journalid' => $journalid])
                ->with('message', 'You have already generated items!')
                ->with('isError', true);

            } else {

                if($request->EndDate != null){

                    $storename = Auth::user()->storeid;

                    DB::insert(
                        'INSERT INTO sptransrepos (JOURNALID, TRANSDATE, ITEMID, COUNTED, STORENAME)
                         SELECT ?, ?, itemid, counted
                         FROM sptrans
                         WHERE DATE(POSTEDDATETIME) = ? and STORENAME = ?',
                        [$request->JOURNALID, $currentDateTime, $request->EndDate, $storename]
                    );
                
                    return redirect()->route('specialorders')
                        ->with('message', 'Generate Item Successfully')
                        ->with('isSuccess', true);   
                    
                }else{
                    $storename = Auth::user()->storeid;
                    
                    DB::table('sptransrepos')
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
    
                    return redirect()->route('specialorders')
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        
    }

    public function post(Request $request)
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storename = Auth::user()->storeid;
        $journalid = 'SPECIAL ORDER';
        $role = Auth::user()->role;

        /* dd($request->remarks); */

        $storename = $request->storeid;
        $remarks = $request->remarks;
        $deliverydate = $request->EndDate;
   
        try {

            $specialorder = DB::table('sptrans')
            ->whereDate('transdate', $deliverydate)
            ->where('STORENAME', $storename)
            ->count();
            
            if ($specialorder <= 0) {
                DB::insert('
                INSERT INTO sptrans 
                (JOURNALID, STORENAME, REMARKS, TRANSDATE, ITEMID, ADJUSTMENT, COSTPRICE, PRICEUNIT, SALESAMOUNT, INVENTONHAND, COUNTED, REASONREFRECID, VARIANTID, POSTED, POSTEDDATETIME, UNITID, updated_at)
                SELECT ?, ?, ?, ?, itemid, counted, 0.00, 0.00, 0.00, 0, counted, \'00001\', 0, 0, ?, \'PCS\', updated_at 
                FROM sptransrepos 
                WHERE counted != 0
            ', [$journalid, $storename, $remarks, $request->EndDate, $currentDateTime]);

            DB::table('sptransrepos')->truncate();

            return redirect()->route('specialorders')
            ->with('message', 'Order posted successfully')
            ->with('isSuccess', true);

            }else{
            return redirect()->route('specialorders')
            ->with('message', 'This date has already been posted.')
            ->with('isError', true);
            }

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
        
    }

    public function ViewOrders()
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $sptrans = DB::table('sptrans as a')
            ->select('a.*', 'b.*', 'c.*')
            ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->get();

            return Inertia::render('SpecialOrders/index2', [
                'sptrans' => $sptrans,
            ]);

    }
}

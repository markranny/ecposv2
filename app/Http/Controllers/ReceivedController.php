<?php

namespace App\Http\Controllers;
use App\Models\receivedordertables; 
use App\Models\receivedordertrans; 
use App\Models\nubersequencevalues; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class ReceivedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

                    if($role == "ADMIN"){
                        $receivedordertables = DB::table('receivedordertables AS a')
                        ->select('a.journalid', 'a.storeid', 'a.description',
                                DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS QTY'),
                                DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2))) AS AMOUNT'),
                                'a.posted', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                        ->leftJoin('receivedordertrans AS b', 'b.JOURNALID', '=', 'a.journalid')
                        ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
                        ->groupBy('a.journalid', 'a.storeid', 'a.description',
                                'a.posted', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                        ->get();
                    }else{
                        $receivedordertables = DB::table('receivedordertables AS a')
                        ->select('a.journalid', 'a.storeid', 'a.description',
                                DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS qty'),
                                DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2)) * CAST(b.COUNTED AS UNSIGNED)) AS amount'),
                                'a.posted', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                        ->leftJoin('receivedordertrans AS b', 'b.JOURNALID', '=', 'a.journalid')
                        ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
                        ->where('storeid', '=', $storeId)
                        ->groupBy('a.journalid', 'a.storeid', 'a.description',
                                'a.posted', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                        ->get();
                    }
        
                    /* $currentDateTime = Carbon::now()->toDateString(); */
                    $utcDateTime = Carbon::now('UTC');
                    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

                    $orders = [];
                    if ($role === "STORE") {
                        $orders = DB::table('receivedordertables as b')
                            ->select(
                                'b.journalid',
                                'b.POSTEDDATETIME as POSTEDDATETIME',
                                'e.STOREID as STOREID', 
                                'b.STOREID as STORENAME',
                                'd.itemid as ITEMID',
                                'd.itemname as ITEMNAME',
                                'c.COUNTED as COUNTED'
                            )
                            ->leftJoin('receivedordertrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                            ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                            ->leftJoin('rbostoretables as e', 'b.STOREID', '=', 'e.NAME')
                            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
                            ->where('b.POSTED', '=', '0')
                            ->where('b.STOREID', '=', $storeId) 
                            ->get();
                    }

                    return Inertia::render('Delivery/Index', [
                        'receivedordertables' => $receivedordertables,
                        'orders' => $orders
                    ]);
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
        DB::beginTransaction();

        try {
            $utcDateTime = Carbon::now('UTC');
            $beijingDateTime = $utcDateTime->setTimezone('Asia/Manila');
            $storeId = Auth::user()->storeid;
            $userId = Auth::user()->id;

            $existingOrder = DB::table('receivedordertables')
                ->whereDate('CREATEDDATETIME', $beijingDateTime)
                ->where('STOREID', $storeId)
                ->exists();

            if ($existingOrder) {
                throw new \Exception('You have already received ordered this time.');
            }

            $tonextrec = DB::table('nubersequencevalues')
                ->where('storeid', $storeId)
                ->lockForUpdate()
                ->value('tonextrec');

            $tonextrec = $tonextrec !== null ? (int)$tonextrec + 1 : 1;

            DB::table('nubersequencevalues')
                ->where('STOREID', $storeId)
                ->update(['tonextrec' => $tonextrec]);

            $journalId = $userId . str_pad($tonextrec, 8, '0', STR_PAD_LEFT);
            $description = "TO" . $journalId;

            $newJournal = receivedordertables::create([
                'JOURNALID' => $journalId,
                'STOREID' => $storeId,
                'DESCRIPTION' => $description,
                'POSTED' => "0",
                'SENT' => "0",
                'POSTEDDATETIME' => $beijingDateTime,
                'JOURNALTYPE' => "1",
                'DELETEPOSTEDLINES' => "0",
                'CREATEDDATETIME' => $beijingDateTime,
            ]);

            DB::commit();

            return redirect()->route('Received.index', ['role' => Auth::user()->role])
                ->with('message', 'Received Order Created Successfully')
                ->with('isSuccess', true);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('Received.index')
                ->with('message', $e->getMessage())
                ->with('isError', true);
        }
    }

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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

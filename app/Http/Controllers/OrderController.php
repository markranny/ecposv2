<?php

namespace App\Http\Controllers;
use App\Models\inventjournaltables; 
use App\Models\nubersequencevalues; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index()
    {
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

                    if($role == "ADMIN"){
                        $inventjournaltables = DB::table('inventjournaltables AS a')
                        ->select('a.journalid', 'a.storeid', 'a.description',
                                DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS QTY'),
                                DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2))) AS AMOUNT'),
                                'a.posted', 'a.sent', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                        ->leftJoin('inventjournaltrans AS b', 'b.JOURNALID', '=', 'a.journalid')
                        ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
                        ->groupBy('a.journalid', 'a.storeid', 'a.description',
                                'a.posted', 'a.sent', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                        ->get();
                    }else{
                        $inventjournaltables = DB::table('inventjournaltables AS a')
                        ->select('a.journalid', 'a.storeid', 'a.description',
                                DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS qty'),
                                DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2)) * CAST(b.COUNTED AS UNSIGNED)) AS amount'),
                                'a.posted', 'a.sent', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                        ->leftJoin('inventjournaltrans AS b', 'b.JOURNALID', '=', 'a.journalid')
                        ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
                        ->where('storeid', '=', $storeId)
                        ->groupBy('a.journalid', 'a.storeid', 'a.description',
                                'a.posted', 'a.sent', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                        ->get();
                    }
        
                    /* $currentDateTime = Carbon::now()->toDateString(); */
                    $utcDateTime = Carbon::now('UTC');
                    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

                    $orders = [];
                    if ($role === "STORE") {
                        $orders = DB::table('inventjournaltables as b')
                            ->select(
                                'b.journalid',
                                'b.POSTEDDATETIME as POSTEDDATETIME',
                                'e.STOREID as STOREID', 
                                'b.STOREID as STORENAME',
                                'd.itemid as ITEMID',
                                'd.itemname as ITEMNAME',
                                'c.COUNTED as COUNTED'
                            )
                            ->leftJoin('inventjournaltransrepos as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                            ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                            ->leftJoin('rbostoretables as e', 'b.STOREID', '=', 'e.NAME')
                            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
                            ->where('b.POSTED', '=', '0')
                            ->where('b.STOREID', '=', $storeId) 
                            ->get();
                    }

                    return Inertia::render('Orders/Index', [
                        'inventjournaltables' => $inventjournaltables,
                        'orders' => $orders
                    ]);
    }

   
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

    try {
        $utcDateTime = Carbon::now('UTC');
        $beijingDateTime = $utcDateTime->setTimezone('Asia/Manila');
        $storeId = Auth::user()->storeid;
        $userId = Auth::user()->id;

        // Check for existing order on the same day
        $existingOrder = DB::table('inventjournaltables')
            ->whereDate('CREATEDDATETIME', $beijingDateTime)
            ->where('STOREID', $storeId)
            ->exists();

        if ($existingOrder) {
            throw new \Exception('You have already ordered this time.');
        }

        // Get and update the next record number atomically
        $nextRec = DB::table('nubersequencevalues')
            ->where('storeid', $storeId)
            ->lockForUpdate()
            ->value('nextrec');

        $nextRec = $nextRec !== null ? (int)$nextRec + 1 : 1;

        DB::table('nubersequencevalues')
            ->where('STOREID', $storeId)
            ->update(['NEXTREC' => $nextRec]);

        // Generate JOURNALID and DESCRIPTION
        $journalId = $userId . str_pad($nextRec, 8, '0', STR_PAD_LEFT);
        $description = "TR" . $journalId;

        // Create new inventory journal
        $newJournal = Inventjournaltables::create([
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

        return redirect()->route('order.index', ['role' => Auth::user()->role])
            ->with('message', 'Order Created Successfully')
            ->with('isSuccess', true);

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('order.index')
            ->with('message', $e->getMessage())
            ->with('isError', true);
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
        //
    }


    public function destroy(string $id)
    {
        //
    }

    public function generatetxtfile()
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        if($role == "STORE"){
            $orders = DB::table('inventjournaltables as b')
            ->select('b.journalid', 'b.POSTEDDATETIME as POSTEDDATETIME', 'e.STOREID as STOREID', 'b.STOREID as STORENAME', 'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'c.COUNTED as COUNTED')
            ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rbostoretables as e', 'b.STOREID', '=', 'e.NAME')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('b.POSTED', '=', '1')
            ->where('e.STOREID', '=', 'BW0002')
            ->get();
        }

        return Inertia::render('Reports/TxtFile', ['orders' => $orders]);
    }
}

<?php

namespace App\Http\Controllers;
use App\Models\inventjournaltables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

                    if($role == "ADMIN"){
                        $inventjournaltables = DB::table('inventjournaltables AS a')
                        ->select('a.journalid', 'a.storeid', 'a.description',
                                DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS QTY'),
                                DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2))) AS AMOUNT'),
                                'a.posted', 'a.posteddatetime', 'a.journaltype', 'a.createddatetime')
                        ->leftJoin('inventjournaltrans AS b', 'b.JOURNALID', '=', 'a.journalid')
                        ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
                        ->groupBy('a.journalid', 'a.storeid', 'a.description',
                                'a.posted', 'a.posteddatetime', 'a.journaltype', 'a.createddatetime')
                        ->get();
                    }else{
                        $inventjournaltables = DB::table('inventjournaltables AS a')
                        ->select('a.journalid', 'a.storeid', 'a.description',
                                DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS qty'),
                                DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2))) AS amount'),
                                'a.posted', 'a.posteddatetime', 'a.journaltype', 'a.createddatetime')
                        ->leftJoin('inventjournaltrans AS b', 'b.JOURNALID', '=', 'a.journalid')
                        ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
                        ->where('storeid', '=', $storeId)
                        ->groupBy('a.journalid', 'a.storeid', 'a.description',
                                'a.posted', 'a.posteddatetime', 'a.journaltype', 'a.createddatetime')
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
                            ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                            ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                            ->leftJoin('rbostoretables as e', 'b.STOREID', '=', 'e.NAME')
                            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
                            ->where('b.POSTED', '=', '1')
                            ->where('b.STOREID', '=', $storeId) 
                            ->get();
                    }

                    return Inertia::render('Orders/Index', [
                        'inventjournaltables' => $inventjournaltables,
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
        try {
            $utcDateTime = Carbon::now('UTC');
            $beijingDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $recordCount = DB::table('inventjournaltables')->count('journalid');
            $s1 = $recordCount + 1;
            $a1 = $recordCount + 1;
            

            if($s1 >= 1 ){
                $s = str_pad($s1, 8, '0', STR_PAD_LEFT);
            }else{
                $s = "0";
            }

            if ($a1 >= 1) {
                $a2 = str_pad($a1, 8, '0', STR_PAD_LEFT);
                $a = "TR" . $a2 . "";
            } else {
                $a2 = "0";
                $a = "TR" . $a2 . "";
            }

            $storeid = Auth::user()->storeid;
            $role = Auth::user()->role;

            $count = DB::table('inventjournaltables')
            ->whereDate('CREATEDDATETIME', '>=', $beijingDateTime)
            ->whereDate('CREATEDDATETIME', '<=', $beijingDateTime)
            ->where('STOREID', $storeid)
            ->count();

            if($count >= 1){
                return redirect()->route('order.index')
                ->with('message', 'You have already ordered this time.')
                ->with('isError', true);
            }else{
                Inventjournaltables::create([
                    'JOURNALID'=> $s,  
                    'STOREID'=> $storeid,  
                    'DESCRIPTION'=> $a,
                    'POSTED'=> "0",    
                    'POSTEDDATETIME'=> $beijingDateTime,  
                    'JOURNALTYPE'=> "1",    
                    'DELETEPOSTEDLINES'=> "0",
                    'CREATEDDATETIME'=> $beijingDateTime,               
                ]);

                return redirect()->route('order.index',['role' => $role])
                ->with('message', 'Order Created Successfully')
                ->with('isSuccess', true);
            }

            
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
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

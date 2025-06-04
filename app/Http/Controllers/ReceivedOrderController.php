<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\inventtables;
use App\Models\receivedordertables;
use App\Models\receivedordertrans;
use App\Events\NewOrderNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use ApacheSpark\SparkContext;
use Illuminate\Support\Facades\Storage;

class ReceivedOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentDateTime = Carbon::now('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;
        $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

        if (in_array($role, ["ADMIN", "SUPERADMIN", "OPIC", "PLANNING"])) {
            $orders = DB::table('receivedordertables AS b')
                ->select(
                    'b.journalid',
                    'f.STOREID',
                    'b.POSTEDDATETIME AS POSTEDDATETIME',
                    'b.STOREID AS STORENAME',
                    'd.itemid AS ITEMID',
                    'd.itemname AS ITEMNAME',
                    'e.ITEMGROUP AS CATEGORY',
                    'c.COUNTED AS COUNTED',
                    'e.transparentstocks as stocks',
                    'e.stocks as movementstocks'
                )
                ->leftJoin('receivedordertrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->whereDate('b.createddatetime', $currentDateTime)
                ->whereNotIn('f.NAME', $excludedNames)
                ->where('c.itemdepartment', '=', 'REGULAR PRODUCT')
                ->where('b.POSTED', '=', '1')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->get();
        } else {
            $orders = DB::table('receivedordertables as b')
                ->select(
                    'b.journalid',
                    'b.POSTEDDATETIME',
                    'b.STOREID as STORENAME',
                    'd.itemid as ITEMID',
                    'd.itemname as ITEMNAME',
                    'e.ITEMGROUP as CATEGORY',
                    'c.COUNTED',
                    'e.transparentstocks as stocks',
                    'e.stocks as movementstocks'
                )
                ->join('receivedordertrans as c', function ($join) {
                    $join->on('b.JOURNALID', '=', 'c.JOURNALID')
                        ->where('c.counted', '!=', '0');
                })
                ->join('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->join('rboinventtables as e', function ($join) {
                    $join->on('d.ITEMID', '=', 'e.itemid')
                        ->where('c.itemdepartment', '=', 'REGULAR PRODUCT');
                })
                ->whereDate('b.createddatetime', $currentDateTime)
                ->where('b.STOREID', '=', $storeId)
                ->where('b.POSTED', '=', '1')
                ->get();
        }

        $noordersold = DB::table('receivedordertables as a')
            ->leftJoin('rbostoretables as b', 'a.STOREID', '!=', 'b.name')
            ->select('b.NAME as NAME')
            ->where(function ($query) use ($currentDateTime, $excludedNames) {
                $query->where(function ($subQuery) use ($currentDateTime, $excludedNames) {
                    $subQuery->whereDate('posteddatetime', $currentDateTime)
                        ->whereNotIn('b.name', $excludedNames);
                })
                ->orWhere(function ($subQuery) use ($currentDateTime, $excludedNames) {
                    $subQuery->whereDate('posteddatetime', $currentDateTime)
                        ->where('POSTED', '0')
                        ->whereNotIn('b.name', $excludedNames);
                });
            })
            ->orderBy('b.NAME', 'ASC')
            ->get();

        $noorders = DB::table('receivedordertables')
        ->select('storeid as NAME')
        ->whereDate('posteddatetime', $currentDateTime)
        ->where('posted', '0')
        ->get();

        $role = Auth::user()->role;

        return Inertia::render('Reports/ReceivedOrdersConso', ['orders' => $orders, 'noorders' => $noorders, 'userRole' => $role]);
    }

    public function receivedwarehouseconso()
    {
        
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;
        $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

        if($role == "ADMIN" || $role == "SUPERADMIN" || $role == "OPIC" || $role == "PLANNING"){
            $orders = 
            DB::table('receivedordertables AS b')
            ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                    'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.COUNTED AS COUNTED',
                    'e.transparentstocks as stocks', 'e.stocks as movementstocks')
            ->leftJoin('receivedordertrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime]) 
            ->whereNotIn('f.NAME', $excludedNames) 
            ->where('c.itemdepartment', '=', 'NON PRODUCT')
            ->where('b.POSTED', '=', '1')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
            ->get();

        }else{

            $orders = 
            DB::table('receivedordertables as b')
            ->select('b.journalid', 'b.POSTEDDATETIME', 'b.STOREID as STORENAME',
                    'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED',
                    'e.transparentstocks as stocks', 'e.stocks as movementstocks')
            ->join('receivedordertrans as c', function($join) {
                $join->on('b.JOURNALID', '=', 'c.JOURNALID')
                    ->where('c.counted', '!=', '0');
            })
            ->join('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
            ->join('rboinventtables as e', function($join) {
                $join->on('d.ITEMID', '=', 'e.itemid')
                    ->where('e.itemdepartment', '=', 'NON PRODUCT');
            })
            ->whereDate('b.createddatetime', $currentDateTime)
            ->where('b.STOREID', '=', $storeId)
            ->where('b.POSTED', '=', '1')
            ->get();
        }
        $role = Auth::user()->role;
        return Inertia::render('Reports/receivedwarehouseconso', ['orders' => $orders, 'userRole' => $role,]);
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

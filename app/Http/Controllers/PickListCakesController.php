<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\inventtables;
use App\Models\inventjournaltables;
use App\Models\inventjournaltrans;
use App\Models\inventtablemodules;
use App\Models\rbostoretables;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PickListCakesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function cakepicklist()
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;
        $role = Auth::user()->name;

        $picklist = DB::table('inventjournaltables AS b')
            ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                    'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                    'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                    DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('b.POSTED', '=', '1')
            
            ->where('c.counted', '!=', '0')
            ->where('e.production', '=', 'CAKELAB')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();
        $groupedPicklist = $picklist->groupBy('STORENAME');

        $picklist12345 = DB::table('inventjournaltables AS b')
            ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                    'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                    DB::raw('(c.ADJUSTMENT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('b.POSTED', '=', '1')
            ->where('c.counted', '!=', '0')
            ->where('c.VARIANTID', '=', 2)
            ->where('e.production', '=', 'CAKELAB')

            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();

        $groupedDR = $picklist12345->groupBy('STORENAME');


        $excludedNames = ['HQ', 'DEMO'];
        $rbostoretables = rbostoretables::select([
            'STOREID',
            'NAME',
            'ROUTES',
        ])
        ->orderBy('NAME', 'asc')
        ->whereNotIn('NAME', $excludedNames) 
        ->get();
        $groupedDR = $picklist12345->groupBy('STORENAME');

        return Inertia::render('Picklist/cakes', [
            'groupedPicklist' => $groupedPicklist,
            'groupedDR' => $groupedDR,
            'rbostoretables' => $rbostoretables
        ]);
    }

    public function getstore(Request $request)
    {
        /* dd($request->STORE); */
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        $picklist = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.NAME', '=', $request->STORE)
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();
        $groupedPicklist = $picklist->groupBy('STORENAME');

        $picklist12345 = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'SOUTH 1')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();

        $groupedDR = $picklist12345->groupBy('STORENAME');

        $excludedNames = ['HQ', 'DEMO'];
        $rbostoretables = rbostoretables::select([
            'STOREID',
            'NAME',
            'ROUTES',
        ])
        ->orderBy('NAME', 'asc')
        ->whereNotIn('NAME', $excludedNames) 
        ->get();

        return Inertia::render('Picklist/cakes', [
            'groupedPicklist' => $groupedPicklist,
            'groupedDR' => $groupedDR,
            'rbostoretables' => $rbostoretables
        ]);
    }
    

    public function south1()
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        $picklist = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'SOUTH 1')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();
        $groupedPicklist = $picklist->groupBy('STORENAME');

        $picklist12345 = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'SOUTH 1')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();

        $groupedDR = $picklist12345->groupBy('STORENAME');

        $excludedNames = ['HQ', 'DEMO'];
        $rbostoretables = rbostoretables::select([
            'STOREID',
            'NAME',
            'ROUTES',
        ])
        ->orderBy('NAME', 'asc')
        ->whereNotIn('NAME', $excludedNames) 
        ->get();

        return Inertia::render('Picklist/cakes', [
            'groupedPicklist' => $groupedPicklist,
            'groupedDR' => $groupedDR,
            'rbostoretables' => $rbostoretables
        ]);
    }

    public function south2()
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        $picklist = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'SOUTH 2')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();
        $groupedPicklist = $picklist->groupBy('STORENAME');

        $picklist12345 = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'SOUTH 2')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();

        $groupedDR = $picklist12345->groupBy('STORENAME');

        $excludedNames = ['HQ', 'DEMO'];
        $rbostoretables = rbostoretables::select([
            'STOREID',
            'NAME',
            'ROUTES',
        ])
        ->orderBy('NAME', 'asc')
        ->whereNotIn('NAME', $excludedNames) 
        ->get();

        return Inertia::render('Picklist/cakes', [
            'groupedPicklist' => $groupedPicklist,
            'groupedDR' => $groupedDR,
            'rbostoretables' => $rbostoretables
        ]);
    }

    public function south3()
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        $picklist = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'SOUTH 3')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();
        $groupedPicklist = $picklist->groupBy('STORENAME');

        $picklist12345 = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'SOUTH 3')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();

        $groupedDR = $picklist12345->groupBy('STORENAME');

        $excludedNames = ['HQ', 'DEMO'];
        $rbostoretables = rbostoretables::select([
            'STOREID',
            'NAME',
            'ROUTES',
        ])
        ->orderBy('NAME', 'asc')
        ->whereNotIn('NAME', $excludedNames) 
        ->get();

        return Inertia::render('Picklist/cakes', [
            'groupedPicklist' => $groupedPicklist,
            'groupedDR' => $groupedDR,
            'rbostoretables' => $rbostoretables
        ]);
    }

    public function north1()
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        $picklist = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'NORTH 1')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();
        $groupedPicklist = $picklist->groupBy('STORENAME');

        $picklist12345 = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER','b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'NORTH 1')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();

        $groupedDR = $picklist12345->groupBy('STORENAME');

        $excludedNames = ['HQ', 'DEMO'];
        $rbostoretables = rbostoretables::select([
            'STOREID',
            'NAME',
            'ROUTES',
        ])
        ->orderBy('NAME', 'asc')
        ->whereNotIn('NAME', $excludedNames) 
        ->get();

        return Inertia::render('Picklist/cakes', [
            'groupedPicklist' => $groupedPicklist,
            'groupedDR' => $groupedDR,
            'rbostoretables' => $rbostoretables
        ]);
    }

    public function north2()
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        $picklist = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'NORTH 2')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();
        $groupedPicklist = $picklist->groupBy('STORENAME');

        $picklist12345 = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'NORTH 2')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();

        $groupedDR = $picklist12345->groupBy('STORENAME');

        $excludedNames = ['HQ', 'DEMO'];
        $rbostoretables = rbostoretables::select([
            'STOREID',
            'NAME',
            'ROUTES',
        ])
        ->orderBy('NAME', 'asc')
        ->whereNotIn('NAME', $excludedNames) 
        ->get();

        return Inertia::render('Picklist/cakes', [
            'groupedPicklist' => $groupedPicklist,
            'groupedDR' => $groupedDR,
            'rbostoretables' => $rbostoretables
        ]);
    }

    public function central()
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        $picklist =
        DB::table('inventjournaltables AS b')
            ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                    'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                    'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                    DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('b.POSTED', '=', '1')
            
            ->where('c.counted', '!=', '0')
            ->where('f.routes', '=', 'CENTRAL')
            ->where('e.production', '=', 'CAKELAB')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();
        $groupedPicklist = $picklist->groupBy('STORENAME');

        $picklist12345 = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'CENTRAL')
        ->where('e.production', '=', 'CAKELAB')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();

        $groupedDR = $picklist12345->groupBy('STORENAME');

        $excludedNames = ['HQ', 'DEMO'];
        $rbostoretables = rbostoretables::select([
            'STOREID',
            'NAME',
            'ROUTES',
        ])
        ->orderBy('NAME', 'asc')
        ->whereNotIn('NAME', $excludedNames) 
        ->get();

        return Inertia::render('Picklist/cakes', [
            'groupedPicklist' => $groupedPicklist,
            'groupedDR' => $groupedDR,
            'rbostoretables' => $rbostoretables
        ]);
    }

    public function east()
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        $picklist = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'EAST')
        ->where('e.production', '=', 'NEWCOM')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();
        $groupedPicklist = $picklist->groupBy('STORENAME');

        $picklist12345 = DB::table('inventjournaltables AS b')
        ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER',
                DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->where('b.POSTED', '=', '1')
        
        ->where('c.counted', '!=', '0')
        ->where('f.routes', '=', 'EAST')
        ->where('e.production', '=', 'NEWCOM')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
        ->get();

        $groupedDR = $picklist12345->groupBy('STORENAME');

        $excludedNames = ['HQ', 'DEMO'];
        $rbostoretables = rbostoretables::select([
            'STOREID',
            'NAME',
            'ROUTES',
        ])
        ->orderBy('NAME', 'asc')
        ->whereNotIn('NAME', $excludedNames) 
        ->get();

        return Inertia::render('Picklist/cakes', [
            'groupedPicklist' => $groupedPicklist,
            'groupedDR' => $groupedDR,
            'rbostoretables' => $rbostoretables
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

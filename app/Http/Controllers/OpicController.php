<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\inventtables;
use App\Models\inventjournaltables;
use App\Models\inventjournaltrans;
use App\Models\partycakes;
use App\Models\sptrans;
use App\Models\rbostoretables;
use App\Models\details;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class OpicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

        if($role == "ADMIN"){

            $orders = 
            DB::table('inventjournaltables AS b')
            ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                    'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.COUNTED AS COUNTED')
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            /* ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])  */
            ->where('b.OPICPOSTED', '=', '1')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
            ->get();

        }elseif($role == "OPIC"){
            $orders = DB::table('inventjournaltables AS b')
            ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                    'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.COUNTED AS COUNTED')
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            /* ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])  */
            ->where('b.OPICPOSTED', '=', '1')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
            ->get();
        }
        else{

            /* dd($storeId); */

            $orders = DB::table('inventjournaltables as b')
            ->select('b.journalid', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                    'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED')
            ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
            /* ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])  */
            ->where('b.STOREID', '=', $storeId)
            ->where('b.OPICPOSTED', '=', '1')
            ->where('c.counted', '!=', '0')
            ->get();
        }
        return Inertia::render('OpicFG/OrdersConso', ['orders' => $orders]);
    }

    public function getrange(Request $request)
    {

        /* dd($request->EndDate); */
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

            // Create a Carbon instance from the EndDate string
            $endDate = Carbon::parse($request->EndDate);
            $endDate->addDay();
            $spdate = $endDate->format('Y-m-d');

            $picklist = 

            DB::table('inventjournaltables AS b')
            ->select('b.journalid as JOURNALID', 'f.STOREID', 'b.POSTEDDATETIME', 'g.PRICE as COST', 'c.CHECKINGCOUNT as CHECKINGCOUNT', 'b.STOREID AS STORENAME',
                    'c.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.COUNTED AS TARGET',
                    'b.DELIVERYDATE AS DELIVERYDATE', 'b.DISPATCHER AS DISPATCHER', 'b.LOGISTICS AS LOGISTICS',
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER', 'b.orangecrates as orangeCrates', 'b.bluecrates as blueCrates', 
                    'b.empanadacrates as empanadaCrates', 'b.box as box', 
                    DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", $request->EndDate)
            ->where('b.STOREID', $request->STORE)
            ->where('b.POSTED', '=', '1')
            
            ->where('c.counted', '!=', '0')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();


            $sptrans = 
            DB::table('sptrans AS b')
            ->select('d.itemname as ITEMNAME', 'b.COUNTED as COUNTED', 'g.PRICE as COST')
            ->leftJoin('inventtables AS d', 'b.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'b.ITEMID', '=', 'e.itemid')
            ->leftJoin('inventtablemodules AS g', 'b.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.POSTEDDATETIME) = ?", $request->EndDate)
            ->where('b.STORENAME', $request->STORE)
            ->get();

            $partycakes = 
            DB::table('partycakes')
            ->select('COSNO as COSNO', 'SRP as SRP', 'BDAYCODENO as BDAYCODENO')
            ->whereRaw("DATE(updated_at) = ?", $request->EndDate)
            ->where('BRANCH', $request->STORE)
            ->where('STATUS', 'PROCESS')
            ->get();

            
            $groupedPicklist = $picklist->groupBy('STORENAME');

            $groupedDR = $picklist->groupBy('STORENAME');

            $excludedNames = ['HQ', 'DEMO'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

            $routes = $request->STORE;
            $getstore = $request->STORE;

            /* dd($getstore); */

            return Inertia::render('Opic/finaldr3', [
                'groupedPicklist' => $groupedPicklist,
                'groupedDR' => $groupedDR,
                'rbostoretables' => $rbostoretables,
                'sptrans' => $sptrans,
                'routes' => $routes,
                'getstore' => $getstore,
                'partycakes' => $partycakes,
            ]);
    }
    

    public function updateCratesCounts(Request $request)
    {
        $validated = $request->validate([
            'journalId' => 'required',
            'orangeCrates' => 'required|integer',
            'blueCrates' => 'required|integer',
            'empanadaCrates' => 'required|integer',
            'box' => 'required|integer',
        ]);

        $updated = DB::table('inventjournaltables')
            ->where('journalid', $validated['journalId'])
            ->update([
                'orangecrates' => $validated['orangeCrates'],
                'bluecrates' => $validated['blueCrates'],
                'empanadacrates' => $validated['empanadaCrates'],
                'box' => $validated['box'],
            ]);

        return response()->json(['success' => $updated]);
    }

    public function finaldr()
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
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER', 'b.orangecrates as orangeCrates', 'b.bluecrates as blueCrates', 
                    'b.empanadacrates as empanadaCrates', 'b.box as box', 
                    DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('b.POSTED', '=', '1')
            
            ->where('c.counted', '!=', '0')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();

            $sptrans = 

            DB::table('sptrans AS b')
            ->select('d.itemname as ITEMNAME', 'b.COUNTED as COUNTED', 'g.PRICE as COST')
            ->leftJoin('inventtables AS d', 'b.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'b.ITEMID', '=', 'e.itemid')
            ->leftJoin('inventtablemodules AS g', 'b.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.transdate) = ?", [$currentDateTime])
            ->get();

            
            $groupedPicklist = $picklist->groupBy('STORENAME');

            $groupedDR = $picklist->groupBy('STORENAME');

            $excludedNames = ['HQ', 'DEMO'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

            $routes = "ALL";

            return Inertia::render('Opic/finaldr', [
                'groupedPicklist' => $groupedPicklist,
                'groupedDR' => $groupedDR,
                'rbostoretables' => $rbostoretables,
                'sptrans' => $sptrans,
                'routes' => $routes
            ]);
    }

    public function fdrsouth1()
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
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER', 'b.orangecrates as orangeCrates', 'b.bluecrates as blueCrates', 
                    'b.empanadacrates as empanadaCrates', 'b.box as box', 
                    DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'SOUTH 1')
            ->where('b.POSTED', '=', '1')
            
            ->where('c.counted', '!=', '0')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();

            $sptrans = 

            DB::table('sptrans AS b')
            ->select('d.itemname as ITEMNAME', 'b.COUNTED as COUNTED', 'g.PRICE as COST')
            ->leftJoin('inventtables AS d', 'b.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'b.ITEMID', '=', 'e.itemid')
            ->leftJoin('inventtablemodules AS g', 'b.ITEMID', '=', 'g.ITEMID')
            ->leftJoin('rbostoretables AS f', 'b.STORENAME', '=', 'f.NAME')
            ->whereRaw("DATE(b.transdate) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'SOUTH 1')
            ->get();

            
            $groupedPicklist = $picklist->groupBy('STORENAME');

            $groupedDR = $picklist->groupBy('STORENAME');

            $excludedNames = ['HQ', 'DEMO'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

            $routes = "SOUTH 1";

            return Inertia::render('Opic/finaldr2', [
                'groupedPicklist' => $groupedPicklist,
                'groupedDR' => $groupedDR,
                'rbostoretables' => $rbostoretables,
                'sptrans' => $sptrans,
                'routes' => $routes
            ]);
    }

    public function fdrsouth2()
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
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER', 'b.orangecrates as orangeCrates', 'b.bluecrates as blueCrates', 
                    'b.empanadacrates as empanadaCrates', 'b.box as box', 
                    DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'SOUTH 2')
            ->where('b.POSTED', '=', '1')
            
            ->where('c.counted', '!=', '0')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();

            $sptrans = 

            DB::table('sptrans AS b')
            ->select('d.itemname as ITEMNAME', 'b.COUNTED as COUNTED', 'g.PRICE as COST')
            ->leftJoin('inventtables AS d', 'b.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'b.ITEMID', '=', 'e.itemid')
            ->leftJoin('inventtablemodules AS g', 'b.ITEMID', '=', 'g.ITEMID')
            ->leftJoin('rbostoretables AS f', 'b.STORENAME', '=', 'f.NAME')
            ->whereRaw("DATE(b.transdate) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'SOUTH 2')
            ->get();

            
            $groupedPicklist = $picklist->groupBy('STORENAME');

            $groupedDR = $picklist->groupBy('STORENAME');

            $excludedNames = ['HQ', 'DEMO'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

            $routes = "SOUTH 2";

            return Inertia::render('Opic/finaldr2', [
                'groupedPicklist' => $groupedPicklist,
                'groupedDR' => $groupedDR,
                'rbostoretables' => $rbostoretables,
                'sptrans' => $sptrans,
                'routes' => $routes
            ]);
    }

    public function fdrnorth1()
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
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER', 'b.orangecrates as orangeCrates', 'b.bluecrates as blueCrates', 
                    'b.empanadacrates as empanadaCrates', 'b.box as box', 
                    DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'NORTH 1')
            ->where('b.POSTED', '=', '1')
            
            ->where('c.counted', '!=', '0')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();

            $sptrans = 

            DB::table('sptrans AS b')
            ->select('d.itemname as ITEMNAME', 'b.COUNTED as COUNTED', 'g.PRICE as COST')
            ->leftJoin('inventtables AS d', 'b.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'b.ITEMID', '=', 'e.itemid')
            ->leftJoin('inventtablemodules AS g', 'b.ITEMID', '=', 'g.ITEMID')
            ->leftJoin('rbostoretables AS f', 'b.STORENAME', '=', 'f.NAME')
            ->whereRaw("DATE(b.transdate) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'NORTH 1')
            ->get();

            
            $groupedPicklist = $picklist->groupBy('STORENAME');

            $groupedDR = $picklist->groupBy('STORENAME');

            $excludedNames = ['HQ', 'DEMO'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

            $routes = "NORTH 1";

            return Inertia::render('Opic/finaldr2', [
                'groupedPicklist' => $groupedPicklist,
                'groupedDR' => $groupedDR,
                'rbostoretables' => $rbostoretables,
                'sptrans' => $sptrans,
                'routes' => $routes
            ]);
    }

    public function fdrsouth3()
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
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER', 'b.orangecrates as orangeCrates', 'b.bluecrates as blueCrates', 
                    'b.empanadacrates as empanadaCrates', 'b.box as box', 
                    DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'SOUTH 3')
            ->where('b.POSTED', '=', '1')
            
            ->where('c.counted', '!=', '0')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();

            $sptrans = 

            DB::table('sptrans AS b')
            ->select('d.itemname as ITEMNAME', 'b.COUNTED as COUNTED', 'g.PRICE as COST')
            ->leftJoin('inventtables AS d', 'b.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'b.ITEMID', '=', 'e.itemid')
            ->leftJoin('inventtablemodules AS g', 'b.ITEMID', '=', 'g.ITEMID')
            ->leftJoin('rbostoretables AS f', 'b.STORENAME', '=', 'f.NAME')
            ->whereRaw("DATE(b.transdate) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'SOUTH 3')
            ->get();

            
            $groupedPicklist = $picklist->groupBy('STORENAME');

            $groupedDR = $picklist->groupBy('STORENAME');

            $excludedNames = ['HQ', 'DEMO'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

            $routes = "SOUTH 3";

            return Inertia::render('Opic/finaldr2', [
                'groupedPicklist' => $groupedPicklist,
                'groupedDR' => $groupedDR,
                'rbostoretables' => $rbostoretables,
                'sptrans' => $sptrans,
                'routes' => $routes
            ]);
    }

    public function fdrnorth2()
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
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER', 'b.orangecrates as orangeCrates', 'b.bluecrates as blueCrates', 
                    'b.empanadacrates as empanadaCrates', 'b.box as box', 
                    DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'NORTH 2')
            ->where('b.POSTED', '=', '1')
            
            ->where('c.counted', '!=', '0')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();

            $sptrans = 

            DB::table('sptrans AS b')
            ->select('d.itemname as ITEMNAME', 'b.COUNTED as COUNTED', 'g.PRICE as COST')
            ->leftJoin('inventtables AS d', 'b.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'b.ITEMID', '=', 'e.itemid')
            ->leftJoin('inventtablemodules AS g', 'b.ITEMID', '=', 'g.ITEMID')
            ->leftJoin('rbostoretables AS f', 'b.STORENAME', '=', 'f.NAME')
            ->whereRaw("DATE(b.transdate) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'NORTH 2')
            ->get();

            
            $groupedPicklist = $picklist->groupBy('STORENAME');

            $groupedDR = $picklist->groupBy('STORENAME');

            $excludedNames = ['HQ', 'DEMO'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

            $routes = "NORTH 2";

            return Inertia::render('Opic/finaldr2', [
                'groupedPicklist' => $groupedPicklist,
                'groupedDR' => $groupedDR,
                'rbostoretables' => $rbostoretables,
                'sptrans' => $sptrans,
                'routes' => $routes
            ]);
    }

    public function fdrcentral()
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
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER', 'b.orangecrates as orangeCrates', 'b.bluecrates as blueCrates', 
                    'b.empanadacrates as empanadaCrates', 'b.box as box', 
                    DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'CENTRAL')
            ->where('b.POSTED', '=', '1')
            
            ->where('c.counted', '!=', '0')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();

            $sptrans = 

            DB::table('sptrans AS b')
            ->select('d.itemname as ITEMNAME', 'b.COUNTED as COUNTED', 'g.PRICE as COST')
            ->leftJoin('inventtables AS d', 'b.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'b.ITEMID', '=', 'e.itemid')
            ->leftJoin('inventtablemodules AS g', 'b.ITEMID', '=', 'g.ITEMID')
            ->leftJoin('rbostoretables AS f', 'b.STORENAME', '=', 'f.NAME')
            ->whereRaw("DATE(b.transdate) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'CENTRAL')
            ->get();

            
            $groupedPicklist = $picklist->groupBy('STORENAME');

            $groupedDR = $picklist->groupBy('STORENAME');

            $excludedNames = ['HQ', 'DEMO'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

            $routes = "CENTRAL";

            return Inertia::render('Opic/finaldr2', [
                'groupedPicklist' => $groupedPicklist,
                'groupedDR' => $groupedDR,
                'rbostoretables' => $rbostoretables,
                'sptrans' => $sptrans,
                'routes' => $routes
            ]);
    }

    public function fdreast()
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
                    'b.FGENCODER AS FGENCODER', 'b.PLENCODER AS PLENCODER', 'b.orangecrates as orangeCrates', 'b.bluecrates as blueCrates', 
                    'b.empanadacrates as empanadaCrates', 'b.box as box', 
                    DB::raw('(c.CHECKINGCOUNT) AS ACTUAL'))
            ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
            ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
            ->leftJoin('inventtablemodules AS g', 'e.ITEMID', '=', 'g.ITEMID')
            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'EAST')
            ->where('b.POSTED', '=', '1')
            
            ->where('c.counted', '!=', '0')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC, d.itemname ASC')
            ->get();

            $sptrans = 

            DB::table('sptrans AS b')
            ->select('d.itemname as ITEMNAME', 'b.COUNTED as COUNTED', 'g.PRICE as COST')
            ->leftJoin('inventtables AS d', 'b.ITEMID', '=', 'd.itemid')
            ->leftJoin('rboinventtables AS e', 'b.ITEMID', '=', 'e.itemid')
            ->leftJoin('inventtablemodules AS g', 'b.ITEMID', '=', 'g.ITEMID')
            ->leftJoin('rbostoretables AS f', 'b.STORENAME', '=', 'f.NAME')
            ->whereRaw("DATE(b.transdate) = ?", [$currentDateTime])
            ->where('f.routes', '=', 'EAST')
            ->get();

            
            $groupedPicklist = $picklist->groupBy('STORENAME');

            $groupedDR = $picklist->groupBy('STORENAME');

            $excludedNames = ['HQ', 'DEMO'];
            $rbostoretables = rbostoretables::select([
                'STOREID',
                'NAME',
                'ROUTES',
            ])
            ->orderBy('NAME', 'asc')
            ->whereNotIn('NAME', $excludedNames) 
            ->get();

            $routes = "EAST";

            return Inertia::render('Opic/finaldr2', [
                'groupedPicklist' => $groupedPicklist,
                'groupedDR' => $groupedDR,
                'rbostoretables' => $rbostoretables,
                'sptrans' => $sptrans,
                'routes' => $routes
            ]);
    }

    public function saveAllData(Request $request)
    {
    try {
        $data = $request->input('data');
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

        foreach ($data as $item) {

            $affected = DB::table('inventjournaltrans')
                ->where('ITEMID', $item['itemId'])
                ->update(['MGCOUNT' => $item['mgCount']]);

            \Log::info('Updated MGCount', ['itemId' => $item['itemId'], 'affected' => $affected]);

            // Update counted values for each store
            foreach ($item['counted'] as $storeName => $count) {
                $journal = DB::table('inventjournaltables')
                    ->where('STOREID', $storeName)
                    ->where('POSTED', '1')
                    ->whereDate('posteddatetime', $currentDateTime)
                    ->first();
            
                if (!$journal) {
                    \Log::warning("Journal not found for store {$storeName}");
                    continue;
                }
            
                $affected = DB::table('inventjournaltrans as a')
                    ->leftJoin('inventjournaltables as b', 'a.journalid', '=', 'b.journalid')
                    ->where('b.storeid', $storeName)
                    ->where('a.ITEMID', $item['itemId'])
                    ->whereDate('b.POSTEDDATETIME', $currentDateTime)
                    ->update(['a.ADJUSTMENT' => $count]);
            
                \Log::info('Updated count for store', [
                    'itemId' => $item['itemId'],
                    'storeName' => $storeName,
                    'affected' => $affected
                ]);
            }
        }

        DB::commit();
        \Log::info('All data saved successfully');
        return response()->json(['message' => 'Data saved successfully'], 200);
    } catch (\Exception $e) {
        DB::rollback();
        \Log::error('Error saving data: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json(['message' => 'Error saving data', 'error' => $e->getMessage()], 500);
    }
}

    public function mgcount()
    {
    $utcDateTime = Carbon::now('UTC');
    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
    $storeId = Auth::user()->storeid;
    $role = Auth::user()->role;

        $orders = 

        DB::table('inventjournaltables AS b')
        ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.MGCOUNT AS MGCOUNT')
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        /* ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])  */
        ->where('b.OPICPOSTED', '=', '1')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = 0;
        $order->BalanceCount = $order->COUNTED; 
        return $order;
    });

    $routes = "ALL ROUTES";

    return Inertia::render('OpicFG/MGCount', [
        'orders' => $orders,
        'routes' => $routes
    ]);
    }

    public function south1()
    {
    $utcDateTime = Carbon::now('UTC');
    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
    $storeId = Auth::user()->storeid;
    $role = Auth::user()->role;

        $orders = 

        DB::table('inventjournaltables AS b')
        ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.MGCOUNT AS MGCOUNT')
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime]) 
        ->where('f.routes', '=', 'SOUTH 1')
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = 0;
        $order->BalanceCount = $order->COUNTED; 
        return $order;
    });
    $routes = "SOUTH 1";

    return Inertia::render('OpicFG/MGCount', [
        'orders' => $orders,
        'routes' => $routes
    ]);
    }

    public function south2()
    {
    $utcDateTime = Carbon::now('UTC');
    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
    $storeId = Auth::user()->storeid;
    $role = Auth::user()->role;

        $orders = 

        DB::table('inventjournaltables AS b')
        ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.MGCOUNT AS MGCOUNT')
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'SOUTH 2')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime]) 
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = 0;
        $order->BalanceCount = $order->COUNTED; 
        return $order;
    });
    $routes = "SOUTH 2";

    return Inertia::render('OpicFG/MGCount', [
        'orders' => $orders,
        'routes' => $routes
    ]);
    }

    public function south3()
    {
    $utcDateTime = Carbon::now('UTC');
    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
    $storeId = Auth::user()->storeid;
    $role = Auth::user()->role;

        $orders = 

        DB::table('inventjournaltables AS b')
        ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.MGCOUNT AS MGCOUNT')
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'SOUTH 3')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime]) 
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = 0;
        $order->BalanceCount = $order->COUNTED; 
        return $order;
    });
    $routes = "SOUTH 3";

    return Inertia::render('OpicFG/MGCount', [
        'orders' => $orders,
        'routes' => $routes
    ]);
    }

    public function north1()
    {
    $utcDateTime = Carbon::now('UTC');
    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
    $storeId = Auth::user()->storeid;
    $role = Auth::user()->role;

        $orders = 

        DB::table('inventjournaltables AS b')
        ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.MGCOUNT AS MGCOUNT')
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'NORTH 1')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime]) 
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = 0;
        $order->BalanceCount = $order->COUNTED; 
        return $order;
    });
    $routes = "NORTH 1";

    return Inertia::render('OpicFG/MGCount', [
        'orders' => $orders,
        'routes' => $routes
    ]);
    }

    public function north2()
    {
    $utcDateTime = Carbon::now('UTC');
    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
    $storeId = Auth::user()->storeid;
    $role = Auth::user()->role;

        $orders = 

        DB::table('inventjournaltables AS b')
        ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.MGCOUNT AS MGCOUNT')
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'NORTH 2')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime]) 
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = 0;
        $order->BalanceCount = $order->COUNTED; 
        return $order;
    });
    $routes = "NORTH 2";

    return Inertia::render('OpicFG/MGCount', [
        'orders' => $orders,
        'routes' => $routes
    ]);
    }

    public function central()
    {
    $utcDateTime = Carbon::now('UTC');
    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
    $storeId = Auth::user()->storeid;
    $role = Auth::user()->role;

        $orders = 
        DB::table('inventjournaltables AS b')
        ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.MGCOUNT AS MGCOUNT')
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'CENTRAL')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime]) 
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = 0;
        $order->BalanceCount = $order->COUNTED; 
        return $order;
    });
    $routes = "CENTRAL";

    return Inertia::render('OpicFG/MGCount', [
        'orders' => $orders,
        'routes' => $routes
    ]);
    }

    public function east()
    {
    $utcDateTime = Carbon::now('UTC');
    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
    $storeId = Auth::user()->storeid;
    $role = Auth::user()->role;

        $orders = 

        DB::table('inventjournaltables AS b')
        ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.ADJUSTMENT AS COUNTED', 'c.MGCOUNT AS MGCOUNT')
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'EAST')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime]) 
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
        ->get();

        $orders = $orders->map(function ($order) {
        $order->MGCount = 0;
        $order->BalanceCount = $order->COUNTED; 
        return $order;
    });
    $routes = "EAST";

    return Inertia::render('OpicFG/MGCount', [
        'orders' => $orders,
        'routes' => $routes
    ]);
    }

    public function updateMGCount(Request $request)
    {
        $request->validate([
            'itemId' => 'required|string',
            'mgCount' => 'required|integer|min:0',
        ]);

        $item = DB::table('inventjournaltrans')
            ->where('ITEMID', $request->itemId)
            ->whereRaw("DATE(createddatetime) = ?", [Carbon::now()->toDateString()])
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        DB::table('inventjournaltrans')
            ->where('ITEMID', $request->itemId)
            ->whereRaw("DATE(createddatetime) = ?", [Carbon::now()->toDateString()])
            ->update(['MGCOUNT' => $request->mgCount]);

        return response()->json(['success' => true, 'mgCount' => $request->mgCount]);
    }

    public function updateCounted(Request $request)
    {
        $request->validate([
            'itemId' => 'required|string',
            'storeName' => 'required|string',
            'counted' => 'required|integer|min:0',
        ]);

        dd($request->itemId);

        $item = DB::table('inventjournaltrans AS c')
            ->join('inventjournaltables AS b', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->where('c.ITEMID', $request->itemId)
            ->where('b.STOREID', $request->storeName)
            ->whereRaw("DATE(b.createddatetime) = ?", [Carbon::now()->toDateString()])
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        DB::table('inventjournaltrans AS c')
            ->join('inventjournaltables AS b', 'b.JOURNALID', '=', 'c.JOURNALID')
            ->where('c.ITEMID', $request->itemId)
            ->where('b.STOREID', $request->storeName)
            ->whereRaw("DATE(b.createddatetime) = ?", [Carbon::now()->toDateString()])
            ->update(['c.COUNTED' => $request->counted]);

        return response()->json(['success' => true, 'counted' => $request->counted]);
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

        return Inertia::render('OpicFG/TxtFile', ['orders' => $orders]);
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
                'StartDate' => 'required|date',
                'EndDate' => 'required|date',
            ]);

            // Process the start and end dates here
            $startDate = $request->input('StartDate');
            $endDate = $request->input('EndDate');

            

            $storeId = Auth::user()->storeid;
            $role = Auth::user()->role;

            if($role == "ADMIN"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'b.STOREID as STORENAME', 'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'c.COUNTED as COUNTED')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->whereBetween(DB::raw('DATE(b.createddatetime)'), [$startDate, $endDate])
                ->get();
            }else{
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'b.STOREID as STORENAME', 'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'c.COUNTED as COUNTED')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->whereDate('b.createddatetime', '>=', $startDate)
                ->whereDate('b.createddatetime', '<=', $endDate)
                ->where('b.STOREID', '=', $storeId)
                ->get();
            }
            
            return Inertia::render('OpicFG/OrdersConso2', ['orders' => $orders]);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
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

    public function post(Request $request)
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storename = Auth::user()->storeid;
        $journalid = 'SPECIAL ORDER';
        $role = Auth::user()->role;

        /* dd($request->EndDate); */

        $storename = $request->storeid;
        $deliverydate = $request->EndDate;
   
        try {

            inventjournaltables::whereNull('OPICPOSTED')->
            update([
                'FGENCODER' => $request->FGENCODER,
                'PLENCODER' => $request->PLENCODER,
                'DISPATCHER' => $request->DISPATCHER,
                'LOGISTICS' => $request->LOGISTICS,
                'DELIVERYDATE' => $request->EndDate,
                'OPICPOSTED' => 1,
            ]);
           

            return redirect()->route('mgcount')
            ->with('message', 'Add Details successfully')
            ->with('isSuccess', true);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
        
    }

    public function adddetails()
    {
            $storename = Auth::user()->storeid;
            $utcDateTime = Carbon::now('UTC');
            $journalid = "SPECIAL ORDER";
            $currentDate = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $rbostoretables = rbostoretables::select([
                'ROUTES',
            ])
            ->distinct()
            ->orderBy('ROUTES', 'asc')
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

            $details = DB::table('details')
            ->select('*')
            ->get();

            return Inertia::render('Details/index', [
                'journalid' => $journalid,
                'details' => $details,
                'rbostoretables' => $rbostoretables,
            ]);
    }

    public function generate(Request $request)
    {
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storename = Auth::user()->storeid;
        $journalid = 'ADD DETAILS';
        $role = Auth::user()->role;

        $now = Carbon::now();
        $yesterday = $now->subDay();

        /* dd($yesterday); */

        $routes = $request->storeid;
        $remarks = $request->remarks;
        $deliverydate = $request->EndDate;
   
        try {

            $details = DB::table('details')
            ->whereDate('DELIVERYDATE', $deliverydate)
            ->where('ROUTES', $routes)
            ->count();
            
            if ($details <= 0) {
                DB::insert('
                INSERT INTO details 
                (ROUTES, CREATEDDATE, DELIVERYDATE)
                VALUES (?, ?, ?)
            ', [$routes, $currentDateTime, $deliverydate]);

            return redirect()->back()
            ->with('message', 'Add details created successfully')
            ->with('isSuccess', true);

            }else{
            return redirect()->back()
            ->with('message', 'This information already exist!')
            ->with('isError', true);
            }

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', $e->errors())
            ->with('isSuccess', false);
        }
        
    }
}

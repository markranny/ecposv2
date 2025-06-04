<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\inventtables;
use App\Models\inventjournaltables;
use App\Models\inventjournaltrans;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class UpdateOrderController extends Controller
{
    public function index(Request $request)
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
            $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

            if($role == "ADMIN" || $role == "SUPERADMIN" || $role == "OPIC" || $role == "PLANNING"){
                $orders = 
                /* DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $startDate)
                ->whereDate('b.createddatetime', '<=', $endDate)
                ->get(); */

                DB::table('inventjournaltables AS b')
                ->select('b.journalid', 'f.STOREID', 'b.POSTEDDATETIME AS POSTEDDATETIME', 'b.STOREID AS STORENAME',
                        'd.itemid AS ITEMID', 'd.itemname AS ITEMNAME', 'e.ITEMGROUP AS CATEGORY', 'c.COUNTED AS COUNTED',
                        'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime]) 
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $startDate)
                ->whereDate('b.createddatetime', '<=', $endDate)
                ->where('c.itemdepartment', '=', 'NON PRODUCT')
                ->where('b.POSTED', '=', '1')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->get();

                }else{
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'b.STOREID as STORENAME', 'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'c.COUNTED as COUNTED',
                'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->whereDate('b.createddatetime', '>=', $startDate)
                ->where('c.itemdepartment', '=', 'REGULAR PRODUCT')
                ->where('b.POSTED', '=', '1')
                ->whereDate('b.createddatetime', '<=', $endDate)
                ->where('b.STOREID', '=', $storeId)
                ->get();
            }

            /* dd($orders); */
            
            return Inertia::render('Reports/OrdersConso2', ['orders' => $orders]);

            /* return redirect()->route('orderingconso.store', ['orders' => $orders]); */
            /* return redirect()->back()->with(['success' => 'Orders created successfully.', 'orders' => $orders]); */

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
        }
    }
    public function getrange(Request $request)
    {
        try {
            $request->validate([
                'StartDate' => 'required|date',
                'EndDate' => 'required|date',
            ]);

            $startDate = $request->input('StartDate');
            $endDate = $request->input('EndDate');

            

            $storeId = Auth::user()->storeid;
            $role = Auth::user()->role;
            $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

            if($role == "ADMIN" || $role == "SUPERADMIN" || $role == "OPIC" || $role == "PLANNING"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('c.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $startDate)
                ->whereDate('b.createddatetime', '<=', $endDate)
                ->get();

            }else{
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->where('b.POSTED', '=', '1')
                ->where('c.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereDate('b.createddatetime', '>=', $startDate)
                ->whereDate('b.createddatetime', '<=', $endDate)
                ->where('b.STOREID', '=', $storeId)
                ->get();
            }

            /* dd($orders); */
            
            return Inertia::render('Reports/OrdersConso2', ['orders' => $orders, 'startDate' => $startDate, 'endDate' => $endDate]);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
        }
    }

    public function warehousegetrange(Request $request)
    {
        try {
            $request->validate([
                'StartDate' => 'required|date',
                'EndDate' => 'required|date',
            ]);

            $startDate = $request->input('StartDate');
            $endDate = $request->input('EndDate');

            

            $storeId = Auth::user()->storeid;
            $role = Auth::user()->role;
            $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

            if($role == "ADMIN" || $role == "SUPERADMIN" || $role == "OPIC" || $role == "PLANNING"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('c.itemdepartment', '=', 'NON PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $startDate)
                ->whereDate('b.createddatetime', '<=', $endDate)
                ->get();
            }else{
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'NON PRODUCT')
                ->whereDate('b.createddatetime', '>=', $startDate)
                ->whereDate('b.createddatetime', '<=', $endDate)
                ->where('b.STOREID', '=', $storeId)
                ->get();
            }

            /* dd($orders); */
            
            return Inertia::render('Reports/warehouseconso2', ['orders' => $orders, 'startDate' => $startDate, 'endDate' => $endDate]);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
        }
    }

    public function show(Request $request)
    {
        $orders = $request->input('orders');
        return Inertia::render('Reports/OrdersConso2');
    }

    public function lastmonth(Request $request)
    {
        try {

            $startDate = Carbon::now()->subMonth()->startOfMonth();
            $endDate = Carbon::now()->subMonth()->endOfMonth();  
            $formattedStartDate = $startDate->format('Y-m-d');
            $formattedEndDate = $endDate->format('Y-m-d');   

            $storeId = Auth::user()->storeid;
            $role = Auth::user()->role;
            $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

            if($role == "ADMIN"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedStartDate)
                ->whereDate('b.createddatetime', '<=', $formattedEndDate)
                ->get();
            }elseif($role == "SUPERADMIN"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedStartDate)
                ->whereDate('b.createddatetime', '<=', $formattedEndDate)
                ->get();
            }elseif($role == "OPIC"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedStartDate)
                ->whereDate('b.createddatetime', '<=', $formattedEndDate)
                ->get();
            }elseif($role == "PLANNING"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedStartDate)
                ->whereDate('b.createddatetime', '<=', $formattedEndDate)
                ->get();
            }
            else{
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereDate('b.createddatetime', '>=', $formattedStartDate)
                ->whereDate('b.createddatetime', '<=', $formattedEndDate)
                ->where('b.STOREID', '=', $storeId)
                ->get();
            }

            /* dd($orders); */
            
            return Inertia::render('Reports/OrdersConso2', ['orders' => $orders, 'startDate' => $formattedStartDate, 'endDate' => $formattedEndDate]);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
        }
    }

    public function lastweek(Request $request)
    {
        try {

            $startDate = Carbon::now()->subWeek()->startOfWeek();
            $endDate = Carbon::now()->subWeek()->endOfWeek();  
            $formattedStartDate = $startDate->format('Y-m-d');
            $formattedEndDate = $endDate->format('Y-m-d');   
            $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

            $storeId = Auth::user()->storeid;
            $role = Auth::user()->role;

            if($role == "ADMIN"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedStartDate)
                ->whereDate('b.createddatetime', '<=', $formattedEndDate)
                ->get();
            }elseif($role == "SUPERADMIN"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedStartDate)
                ->whereDate('b.createddatetime', '<=', $formattedEndDate)
                ->get();
            }elseif($role == "OPIC"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedStartDate)
                ->whereDate('b.createddatetime', '<=', $formattedEndDate)
                ->get();
            }elseif($role == "PLANNING"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedStartDate)
                ->whereDate('b.createddatetime', '<=', $formattedEndDate)
                ->get();
            }
            else{
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereDate('b.createddatetime', '>=', $formattedStartDate)
                ->whereDate('b.createddatetime', '<=', $formattedEndDate)
                ->where('b.STOREID', '=', $storeId)
                ->get();
            }

            /* dd($orders); */
            
            return Inertia::render('Reports/OrdersConso2', ['orders' => $orders, 'startDate' => $formattedStartDate, 'endDate' => $formattedEndDate]);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
        }
    }

    public function yesterday(Request $request)
    {
        try {

            $yesterday = Carbon::now()->subDays(1);
            $formattedYesterday = $yesterday->format('Y-m-d');
            $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

            $storeId = Auth::user()->storeid;
            $role = Auth::user()->role;

            if($role == "ADMIN"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedYesterday)
                ->whereDate('b.createddatetime', '<=', $formattedYesterday)
                ->get();
            }elseif($role == "SUPERADMIN"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedYesterday)
                ->whereDate('b.createddatetime', '<=', $formattedYesterday)
                ->get();
            }elseif($role == "OPIC"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedYesterday)
                ->whereDate('b.createddatetime', '<=', $formattedYesterday)
                ->get();
            }elseif($role == "PLANNING"){
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'f.STOREID as STOREID', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                         'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                         'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
                ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereNotIn('f.NAME', $excludedNames) 
                ->whereDate('b.createddatetime', '>=', $formattedYesterday)
                ->whereDate('b.createddatetime', '<=', $formattedYesterday)
                ->get();
            }
            else{
                $orders = DB::table('inventjournaltables as b')
                ->select('b.journalid', 'b.POSTEDDATETIME as POSTEDDATETIME', 'b.STOREID as STORENAME',
                'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED as COUNTED',
                'e.transparentstocks as stocks', 'e.stocks as movementstocks')
                ->leftJoin('inventjournaltrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                ->leftJoin('rboinventtables as e', 'd.ITEMID', '=', 'e.itemid')
                ->where('b.POSTED', '=', '1')
                ->where('e.itemdepartment', '=', 'REGULAR PRODUCT')
                ->whereDate('b.createddatetime', '>=', $formattedYesterday)
                ->whereDate('b.createddatetime', '<=', $formattedYesterday)
                ->where('b.STOREID', '=', $storeId)
                ->get();
            }

            /* dd($orders); */
            
            return Inertia::render('Reports/OrdersConso2', ['orders' => $orders, 'startDate' => $formattedYesterday, 'endDate' => $formattedYesterday]);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
        }
    }
}

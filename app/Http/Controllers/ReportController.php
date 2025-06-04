<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\inventtables;
use App\Models\inventjournaltables;
use App\Models\inventjournaltrans;
use App\Events\NewOrderNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use ApacheSpark\SparkContext;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
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
            $orders = DB::table('inventjournaltables AS b')
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
                ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
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
            $orders = DB::table('inventjournaltables as b')
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
                ->join('inventjournaltrans as c', function ($join) {
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

        $noordersold = DB::table('inventjournaltables as a')
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

        $noorders = DB::table('inventjournaltables')
        ->select('storeid as NAME')
        ->whereDate('posteddatetime', $currentDateTime)
        ->where('posted', '0')
        ->where('sent', '1')
        ->get();

        $role = Auth::user()->role;

        return Inertia::render('Reports/OrdersConso', ['orders' => $orders, 'noorders' => $noorders, 'userRole' => $role,]);
    }

    public function warehouseconso()
    {
        
        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;
        $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

        if($role == "ADMIN" || $role == "SUPERADMIN" || $role == "OPIC" || $role == "PLANNING"){
            $orders = 
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
            ->where('c.itemdepartment', '=', 'NON PRODUCT')
            ->where('b.POSTED', '=', '1')
            ->orderByRaw('CAST(f.STOREID AS UNSIGNED) ASC')
            ->get();

        }else{

            $orders = 
            DB::table('inventjournaltables as b')
            ->select('b.journalid', 'b.POSTEDDATETIME', 'b.STOREID as STORENAME',
                    'd.itemid as ITEMID', 'd.itemname as ITEMNAME', 'e.ITEMGROUP as CATEGORY', 'c.COUNTED',
                    'e.transparentstocks as stocks', 'e.stocks as movementstocks')
            ->join('inventjournaltrans as c', function($join) {
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
        return Inertia::render('Reports/warehouseconso', ['orders' => $orders, 'userRole' => $role,]);
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
    $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

        $orders = DB::table('inventjournaltables AS b')
        ->select(
            'b.journalid',
            'f.STOREID',
            'b.POSTEDDATETIME AS POSTEDDATETIME',
            'b.STOREID AS STORENAME',
            'd.itemid AS ITEMID',
            'd.itemname AS ITEMNAME',
            'e.ITEMGROUP AS CATEGORY',
            'c.ADJUSTMENT AS COUNTED',
            /* DB::raw('(SELECT MGCOUNT FROM inventjournaltrans WHERE ITEMID = d.itemid ORDER BY createddatetime DESC LIMIT 1) as MGCOUNT') */
            DB::raw('(SELECT FGCOUNT FROM rboinventtables WHERE ITEMID = d.itemid ORDER BY createddatetime DESC LIMIT 1) as MGCOUNT')
        )
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        /* ->where('f.routes', '=', 'SOUTH 1') */
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->whereNotIn('f.NAME', $excludedNames)
        ->where('e.itemdepartment','REGULAR PRODUCT')
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(b.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = $order->MGCOUNT ?? 0;
        $order->BalanceCount = $order->MGCount - $order->COUNTED;
        return $order;
    });
    $routes = "ALL";

    event(new NewOrderNotification('New order received!'));

    return Inertia::render('Reports/MGCount', [
        'orders' => $orders,
        'routes' => $routes,
    ]);
    event(new NewOrderNotification('New order received!'));
    }

    public function south1()
    {
    $utcDateTime = Carbon::now('UTC');
    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
    $storeId = Auth::user()->storeid;
    $role = Auth::user()->role;
    $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

        $orders = DB::table('inventjournaltables AS b')
        ->select(
            'b.journalid',
            'f.STOREID',
            'b.POSTEDDATETIME AS POSTEDDATETIME',
            'b.STOREID AS STORENAME',
            'd.itemid AS ITEMID',
            'd.itemname AS ITEMNAME',
            'e.ITEMGROUP AS CATEGORY',
            'c.ADJUSTMENT AS COUNTED',
            DB::raw('(SELECT MGCOUNT FROM inventjournaltrans WHERE ITEMID = d.itemid ORDER BY createddatetime DESC LIMIT 1) as MGCOUNT')
        )
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'SOUTH 1')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->whereNotIn('f.NAME', $excludedNames)
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(b.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = $order->MGCOUNT ?? 0;
        $order->BalanceCount = $order->MGCount - $order->COUNTED;
        return $order;
    });
    $routes = "SOUTH 1";

    event(new NewOrderNotification('New order received!'));

    return Inertia::render('Reports/MGCount', [
        'orders' => $orders,
        'routes' => $routes,
    ]);
    event(new NewOrderNotification('New order received!'));
    }

    public function south2()
    {
    $utcDateTime = Carbon::now('UTC');
    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
    $storeId = Auth::user()->storeid;
    $role = Auth::user()->role;
    $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

        $orders = DB::table('inventjournaltables AS b')
        ->select(
            'b.journalid',
            'f.STOREID',
            'b.POSTEDDATETIME AS POSTEDDATETIME',
            'b.STOREID AS STORENAME',
            'd.itemid AS ITEMID',
            'd.itemname AS ITEMNAME',
            'e.ITEMGROUP AS CATEGORY',
            'c.ADJUSTMENT AS COUNTED',
            DB::raw('(SELECT MGCOUNT FROM inventjournaltrans WHERE ITEMID = d.itemid ORDER BY createddatetime DESC LIMIT 1) as MGCOUNT')
        )
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'SOUTH 2')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->whereNotIn('f.NAME', $excludedNames)
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(b.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = $order->MGCOUNT ?? 0;
        $order->BalanceCount = $order->MGCount - $order->COUNTED;
        return $order;
    });
    $routes = "SOUTH 2";

    return Inertia::render('Reports/MGCount', [
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
    $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

        $orders = DB::table('inventjournaltables AS b')
        ->select(
            'b.journalid',
            'f.STOREID',
            'b.POSTEDDATETIME AS POSTEDDATETIME',
            'b.STOREID AS STORENAME',
            'd.itemid AS ITEMID',
            'd.itemname AS ITEMNAME',
            'e.ITEMGROUP AS CATEGORY',
            'c.ADJUSTMENT AS COUNTED',
            DB::raw('(SELECT MGCOUNT FROM inventjournaltrans WHERE ITEMID = d.itemid ORDER BY createddatetime DESC LIMIT 1) as MGCOUNT')
        )
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'SOUTH 3')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->whereNotIn('f.NAME', $excludedNames)
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(b.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = $order->MGCOUNT ?? 0;
        $order->BalanceCount = $order->MGCount - $order->COUNTED;
        return $order;
    });
    $routes = "SOUTH 3";

    return Inertia::render('Reports/MGCount', [
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
    $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

        $orders = DB::table('inventjournaltables AS b')
        ->select(
            'b.journalid',
            'f.STOREID',
            'b.POSTEDDATETIME AS POSTEDDATETIME',
            'b.STOREID AS STORENAME',
            'd.itemid AS ITEMID',
            'd.itemname AS ITEMNAME',
            'e.ITEMGROUP AS CATEGORY',
            'c.ADJUSTMENT AS COUNTED',
            DB::raw('(SELECT MGCOUNT FROM inventjournaltrans WHERE ITEMID = d.itemid ORDER BY createddatetime DESC LIMIT 1) as MGCOUNT')
        )
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'NORTH 1')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->whereNotIn('f.NAME', $excludedNames)
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(b.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = $order->MGCOUNT ?? 0;
        $order->BalanceCount = $order->MGCount - $order->COUNTED;
        return $order;
    });
    $routes = "NORTH 1";

    return Inertia::render('Reports/MGCount', [
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
    $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

        $orders = DB::table('inventjournaltables AS b')
        ->select(
            'b.journalid',
            'f.STOREID',
            'b.POSTEDDATETIME AS POSTEDDATETIME',
            'b.STOREID AS STORENAME',
            'd.itemid AS ITEMID',
            'd.itemname AS ITEMNAME',
            'e.ITEMGROUP AS CATEGORY',
            'c.ADJUSTMENT AS COUNTED',
            DB::raw('(SELECT MGCOUNT FROM inventjournaltrans WHERE ITEMID = d.itemid ORDER BY createddatetime DESC LIMIT 1) as MGCOUNT')
        )
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'NORTH 2')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->whereNotIn('f.NAME', $excludedNames)
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(b.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = $order->MGCOUNT ?? 0;
        $order->BalanceCount = $order->MGCount - $order->COUNTED;
        return $order;
    });
    $routes = "NORTH 2";

    return Inertia::render('Reports/MGCount', [
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
    $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

        $orders = DB::table('inventjournaltables AS b')
        ->select(
            'b.journalid',
            'f.STOREID',
            'b.POSTEDDATETIME AS POSTEDDATETIME',
            'b.STOREID AS STORENAME',
            'd.itemid AS ITEMID',
            'd.itemname AS ITEMNAME',
            'e.ITEMGROUP AS CATEGORY',
            'c.ADJUSTMENT AS COUNTED',
            DB::raw('(SELECT MGCOUNT FROM inventjournaltrans WHERE ITEMID = d.itemid ORDER BY createddatetime DESC LIMIT 1) as MGCOUNT')
        )
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'CENTRAL')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->whereNotIn('f.NAME', $excludedNames)
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(b.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = $order->MGCOUNT ?? 0;
        $order->BalanceCount = $order->MGCount - $order->COUNTED;
        return $order;
    });
    $routes = "CENTRAL";

    return Inertia::render('Reports/MGCount', [
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
    $excludedNames = ['HQ', 'DEMO', 'DISPATCH', 'FINISH GOODS'];

        $orders = DB::table('inventjournaltables AS b')
        ->select(
            'b.journalid',
            'f.STOREID',
            'b.POSTEDDATETIME AS POSTEDDATETIME',
            'b.STOREID AS STORENAME',
            'd.itemid AS ITEMID',
            'd.itemname AS ITEMNAME',
            'e.ITEMGROUP AS CATEGORY',
            'c.ADJUSTMENT AS COUNTED',
            DB::raw('(SELECT MGCOUNT FROM inventjournaltrans WHERE ITEMID = d.itemid ORDER BY createddatetime DESC LIMIT 1) as MGCOUNT')
        )
        ->leftJoin('inventjournaltrans AS c', 'b.JOURNALID', '=', 'c.JOURNALID')
        ->leftJoin('inventtables AS d', 'c.ITEMID', '=', 'd.itemid')
        ->leftJoin('rboinventtables AS e', 'd.ITEMID', '=', 'e.itemid')
        ->leftJoin('rbostoretables AS f', 'b.STOREID', '=', 'f.NAME')
        ->where('f.routes', '=', 'EAST')
        ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
        ->whereNotIn('f.NAME', $excludedNames)
        ->where('b.POSTED', '=', '1')
        ->orderByRaw('CAST(b.STOREID AS UNSIGNED) ASC')
        ->get();

    $orders = $orders->map(function ($order) {
        $order->MGCount = $order->MGCOUNT ?? 0;
        $order->BalanceCount = $order->MGCount - $order->COUNTED;
        return $order;
    });
    $routes = "EAST";

    return Inertia::render('Reports/MGCount', [
        'orders' => $orders,
        'routes' => $routes
    ]);
    }

    public function updateMGCount(Request $request)
    {
        Log::info('Received update request', $request->all());

        $request->validate([
            'itemId' => 'required|string',
            'mgCount' => 'required|integer|min:0',
        ]);

        $item = DB::table('inventjournaltrans')
            ->where('ITEMID', $request->itemId)
            ->whereRaw("DATE(transdate) = ?", [Carbon::now()->toDateString()])
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $updated = DB::table('inventjournaltrans')
            ->where('ITEMID', $request->itemId)
            ->whereRaw("DATE(transdate) = ?", [Carbon::now()->toDateString()])
            ->update(['MGCOUNT' => $request->mgCount]);

            if ($updated) {
                Log::info('MGCount updated successfully', ['itemId' => $request->itemId, 'mgCount' => $request->mgCount]);
                return response()->json(['success' => true, 'mgCount' => $request->mgCount]);
            } else {
                Log::error('Failed to update MGCount', ['itemId' => $request->itemId, 'mgCount' => $request->mgCount]);
                return response()->json(['success' => false, 'message' => 'Failed to update MGCount'], 500);
            }
        
    }

    public function updateCounted(Request $request)
    {
        $request->validate([
            'itemId' => 'required|string',
            'storeName' => 'required|string',
            'counted' => 'required|integer|min:0',
        ]);

        Log::info('Updating counted value', $request->all());

        try {
            $item = DB::table('inventjournaltrans AS c')
                ->join('inventjournaltables AS b', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->where('c.ITEMID', $request->itemId)
                ->where('b.STOREID', $request->storeName)
                ->whereRaw("DATE(b.createddatetime) = ?", [Carbon::now()->toDateString()])
                ->first();

            if (!$item) {
                Log::warning('Item not found for update', $request->all());
                return response()->json(['success' => false, 'message' => 'Item not found'], 404);
            }

            $updated = DB::table('inventjournaltrans AS c')
                ->join('inventjournaltables AS b', 'b.JOURNALID', '=', 'c.JOURNALID')
                ->where('c.ITEMID', $request->itemId)
                ->where('b.STOREID', $request->storeName)
                ->whereRaw("DATE(b.createddatetime) = ?", [Carbon::now()->toDateString()])
                ->update(['c.ADJUSTMENT' => $request->counted]);

            if ($updated) {
                Log::info('Counted value updated successfully', [
                    'itemId' => $request->itemId,
                    'storeName' => $request->storeName,
                    'counted' => $request->counted
                ]);
                return response()->json(['success' => true, 'counted' => $request->counted]);
            } else {
                Log::warning('No rows were updated', $request->all());
                return response()->json(['success' => false, 'message' => 'No rows were updated'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error updating counted value', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred while updating'], 500);
        }
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

    public function create()
    {
        //
    }

    public function store(Request $request)
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
            
            return Inertia::render('Reports/OrdersConso2', ['orders' => $orders]);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
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

    public function resetorder(Request $request)
    {
        try {
            $utcDateTime = Carbon::now('UTC');
            $beijingDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            /* dd($request->passcode); */

            if($request->passcode === '0123456789'){
                DB::beginTransaction();
                DB::table('inventjournaltrans')
                ->where('journalid', $request->tr)
                ->whereDate('posteddatetime', $beijingDateTime)
                ->delete();

                DB::table('inventjournaltables')
                ->where('journalid', $request->tr)
                ->whereDate('posteddatetime', $beijingDateTime)
                ->update(['posted' => '0', 'sent' => '0']);

                DB::table('inventjournaltransrepos')
                ->where('journalid', $request->tr)
                ->update(['status' => '0']);

                $journalId = $request->tr; 

                $storeid = DB::table('inventjournaltransrepos as a')
                    ->leftJoin('rbostoretables as b', 'a.STORENAME', '=', 'b.name')
                    ->where('a.journalid', $request->tr)
                    ->value('b.STOREID');

                if (!$storeid) {
                    return back()
                        ->with('message', 'Store ID not found')
                        ->with('isError', true);
                }

                DB::table('inventjournaltransrepos')
                    ->where('journalid', $request->tr)
                    ->update(['status' => '0']);

                $currentdateformat = Carbon::now()->format('Ymd');

                $filename = $storeid . $currentdateformat;
                $dynamicDisk = "public/{$currentdateformat}";

                if (!Storage::exists($dynamicDisk)) {
                    return back()
                        ->with('message', "Directory {$dynamicDisk} does not exist")
                        ->with('isError', true);
                }

                if (Storage::disk('public')->exists("{$currentdateformat}/{$filename}.txt")) {
                    Storage::disk('public')->delete("{$currentdateformat}/{$filename}.txt");
                }
                DB::commit();

                        return back()
                        ->with('message', 'Reset Order Successfully')
                        ->with('isSuccess', true);
                    }else{
                        return back()
                        ->with('message', 'Wrong Passcode')
                        ->with('isError', true);
                    }
            

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message',$e->errors())
            ->with('isError', false);
        }
    }
    
}

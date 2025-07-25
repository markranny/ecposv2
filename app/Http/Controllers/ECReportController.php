<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\rbotransactiontables;
use App\Models\rbotransactionsalestrans;
use App\Models\stockcountingtrans;
use App\Models\receivedordertrans;
use App\Models\wastedeclarationtrans;
use App\Models\inventtables;
use App\Models\rbostoretables;
use Inertia\Inertia;
use Carbon\Carbon;
use ApacheSpark\SparkContext;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\InventorySummary;


use Illuminate\Http\Request;

class ECReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = Auth::user()->role;
        return Inertia::render('Reports/index',['userRole' => $role,]);
    }

    public function ar(Request $request)
{
    // Validate request
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    // Build query
    $query = rbotransactiontables::select(
        'rbotransactiontables.receiptid',
        'rbotransactiontables.store',
        'rbostoretables.NAME as storename',
        DB::raw('CAST(createddate as date) as createddate'),  
        DB::raw('CAST(IFNULL(charge, 0) AS DECIMAL(10,0)) as charge'),  
        DB::raw('CAST(IFNULL(gcash, 0) AS DECIMAL(10,0)) as gcash'),  
        DB::raw('CAST(IFNULL(paymaya, 0) AS DECIMAL(10,0)) as paymaya'),  
        DB::raw('CAST(IFNULL(card, 0) AS DECIMAL(10,0)) as card'),  
        DB::raw('CAST(IFNULL(loyaltycard, 0) AS DECIMAL(10,0)) as loyaltycard'), 
        DB::raw('CAST(IFNULL(foodpanda, 0) AS DECIMAL(10,0)) as foodpanda'),  
        DB::raw('CAST(IFNULL(grabfood, 0) AS DECIMAL(10,0)) as grabfood'),  
        DB::raw('CAST(IFNULL(representation, 0) AS DECIMAL(10,0)) as representation'),
        DB::raw('CAST((
            IFNULL(charge, 0) + IFNULL(gcash, 0) + IFNULL(paymaya, 0) + 
            IFNULL(card, 0) + IFNULL(loyaltycard, 0) + IFNULL(foodpanda, 0) + 
            IFNULL(grabfood, 0) + IFNULL(representation, 0)
        ) AS DECIMAL(10,0)) as total_amount')
    )
    ->join('rbostoretables', 'rbotransactiontables.store', '=', 'rbostoretables.NAME');

    // Modified where clause to find transactions with receivables
    $query->where(function($q) {
        $q->where('charge', '>', 0)
        ->orWhere('gcash', '>', 0)
        ->orWhere('paymaya', '>', 0)
        ->orWhere('card', '>', 0)
        ->orWhere('loyaltycard', '>', 0)
        ->orWhere('foodpanda', '>', 0)
        ->orWhere('grabfood', '>', 0)
        ->orWhere('representation', '>', 0);
    });

    // Apply date filters if provided
    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            Carbon::parse($request->startDate)->startOfDay(),
            Carbon::parse($request->endDate)->endOfDay()
        ]);
    }

    // Apply store filters based on user role
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactiontables.store', $userStoreId);
    }

    // Get available stores based on user role
    $stores = rbostoretables::select('STOREID', 'NAME')
        ->when($role !== 'ADMIN' && $role !== 'SUPERADMIN', function($query) use ($userStoreId) {
            $query->where('STOREID', $userStoreId);
        })
        ->orderBy('NAME')
        ->get();

    // Get AR records
    $ar = $query->orderBy('createddate', 'desc')->get();

    // Calculate totals - Use integer values for totals
    $totals = [
        'charge' => (int)$ar->sum('charge'),
        'gcash' => (int)$ar->sum('gcash'),
        'paymaya' => (int)$ar->sum('paymaya'),
        'card' => (int)$ar->sum('card'),
        'loyaltycard' => (int)$ar->sum('loyaltycard'),
        'foodpanda' => (int)$ar->sum('foodpanda'),
        'grabfood' => (int)$ar->sum('grabfood'),
        'representation' => (int)$ar->sum('representation'),
        'total' => (int)$ar->sum('total_amount')
    ];

    return Inertia::render('Reports/AccountReceivable', [
        'ar' => $ar,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? []
        ]
    ]);
}

public function ec(Request $request)
{
    // Validate request
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;
    $isLoading = false;
    
    // Build query
    $query = rbotransactiontables::select(
        'rbotransactiontables.receiptid',
        'rbotransactiontables.store',
        'rbostoretables.NAME as storename',
        'rbotransactiontables.custaccount as custaccount',
        DB::raw('CAST(createddate as date) as createddate'),  
        DB::raw('CAST(IFNULL(grossamount, 0) AS DECIMAL(10,2)) as grossamount'),
        DB::raw('CAST(IFNULL(discamount, 0) AS DECIMAL(10,2)) as discamount'),
        DB::raw('CAST(IFNULL(netamount, 0) AS DECIMAL(10,2)) as netamount'),
        DB::raw('CAST(IFNULL(taxinclinprice, 0) AS DECIMAL(10,2)) as Vat'),
        DB::raw('CAST(IFNULL(netamountnotincltax, 0) AS DECIMAL(10,2)) as Vatablesales')
    )
    ->where('charge','>=', 1)
    ->join('rbostoretables', 'rbotransactiontables.store', '=', 'rbostoretables.NAME');

    // Apply date filters if provided
    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            Carbon::parse($request->startDate)->startOfDay(),
            Carbon::parse($request->endDate)->endOfDay()
        ]);
    }

    // Apply store filters based on user role
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactiontables.store', $userStoreId);
    }

    // Get available stores based on user role
    $stores = rbostoretables::select('STOREID', 'NAME')
        ->when($role !== 'ADMIN' && $role !== 'SUPERADMIN', function($query) use ($userStoreId) {
            $query->where('STOREID', $userStoreId);
        })
        ->orderBy('NAME')
        ->get();

    // Get EC records
    $ec = $query->orderBy('createddate', 'desc')->get();

    // Calculate totals
    $totals = [
        'grossamount' => (float)$ec->sum('grossamount'),
        'discamount' => (float)$ec->sum('discamount'),
        'netamount' => (float)$ec->sum('netamount'),
        'Vat' => (float)$ec->sum('Vat'),
        'Vatablesales' => (float)$ec->sum('Vatablesales')
    ];

    return Inertia::render('Reports/EmployeeCharge', [
        'ec' => $ec,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? []
        ]
    ]);
}

public function bo(Request $request)
{
    // Validate request
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;
    
    // Build query using stockcountingtrans table
    $query = stockcountingtrans::select(
        'stockcountingtrans.ITEMID as itemid',
        'rbostoretables.NAME as storename',
        'inventtables.itemname as itemname',
        'rboinventtables.itemgroup as category',
        'stockcountingtrans.TRANSDATE as batchdate',
        'stockcountingtrans.WASTEDATE as waste_declaration_date',
        // Add price including tax from inventtablemodules table
        'inventtablemodules.priceincltax as price',
        DB::raw("CAST(IFNULL(SUM(CASE WHEN UPPER(TRIM(stockcountingtrans.WASTETYPE)) IN ('THROW_AWAY', 'THROW AWAY') THEN stockcountingtrans.WASTECOUNT ELSE 0 END), 0) AS DECIMAL(10,2)) as throw_away"),
        DB::raw("CAST(IFNULL(SUM(CASE WHEN UPPER(TRIM(stockcountingtrans.WASTETYPE)) IN ('EARLY_MOLDS', 'EARLY MOLDS') THEN stockcountingtrans.WASTECOUNT ELSE 0 END), 0) AS DECIMAL(10,2)) as early_molds"),
        DB::raw("CAST(IFNULL(SUM(CASE WHEN UPPER(TRIM(stockcountingtrans.WASTETYPE)) IN ('PULL_OUT', 'PULL OUT') THEN stockcountingtrans.WASTECOUNT ELSE 0 END), 0) AS DECIMAL(10,2)) as pull_out"),
        DB::raw("CAST(IFNULL(SUM(CASE WHEN UPPER(TRIM(stockcountingtrans.WASTETYPE)) IN ('RAT_BITES', 'RAT BITES') THEN stockcountingtrans.WASTECOUNT ELSE 0 END), 0) AS DECIMAL(10,2)) as rat_bites"),
        DB::raw("CAST(IFNULL(SUM(CASE WHEN UPPER(TRIM(stockcountingtrans.WASTETYPE)) IN ('ANT_BITES', 'ANT BITES') THEN stockcountingtrans.WASTECOUNT ELSE 0 END), 0) AS DECIMAL(10,2)) as ant_bites")
    )
    ->join('rbostoretables', 'stockcountingtrans.STORENAME', '=', 'rbostoretables.NAME')
    ->join('inventtables', 'stockcountingtrans.ITEMID', '=', 'inventtables.itemid')
    ->join('rboinventtables', 'stockcountingtrans.ITEMID', '=', 'rboinventtables.itemid')
    // Join with inventtablemodules to get price information
    ->leftJoin('inventtablemodules', 'stockcountingtrans.ITEMID', '=', 'inventtablemodules.itemid')
    ->where('stockcountingtrans.WASTECOUNT', '>', 0);

    // Apply date filters
    if ($request->filled(['startDate', 'endDate'])) {
        // Format dates properly for database comparison when both start and end dates are provided
        $startDate = Carbon::parse($request->startDate)->startOfDay()->format('Y-m-d H:i:s');
        $endDate = Carbon::parse($request->endDate)->endOfDay()->format('Y-m-d H:i:s');
        
        // Use TRANSDATE for filtering
        $query->whereBetween('stockcountingtrans.TRANSDATE', [$startDate, $endDate]);
        
        // Add debug logging
        Log::info('Date filter applied on TRANSDATE with provided dates', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'query' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
    } else {
        // If no date range is provided, default to current date
        $currentDate = Carbon::now()->format('Y-m-d');
        $query->whereDate('stockcountingtrans.TRANSDATE', $currentDate);
        
        // Add debug logging
        Log::info('Date filter applied on TRANSDATE with current date', [
            'currentDate' => $currentDate,
            'query' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
    }

    // Apply store filters based on user role
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('stockcountingtrans.STORENAME', $userStoreId);
    }

    // Group and order results
    $query->groupBy(
        'stockcountingtrans.ITEMID',
        'rbostoretables.NAME',
        'inventtables.itemname',
        'rboinventtables.itemgroup',
        'stockcountingtrans.TRANSDATE',
        'stockcountingtrans.WASTEDATE',
        'inventtablemodules.priceincltax' // Add price including tax to the groupBy clause
    );

    // Format results
    $data = $query->orderBy('storename')->get()->map(function ($item) {
        // Calculate total waste count
        $totalWasteCount = (float) $item->throw_away + 
                         (float) $item->early_molds + 
                         (float) $item->pull_out + 
                         (float) $item->rat_bites + 
                         (float) $item->ant_bites;
        
        // Calculate total price (price * total waste)
        $totalPrice = (float) $item->price * $totalWasteCount;
        
        return [
            'itemid' => $item->itemid,
            'itemname' => $item->itemname,
            'category' => $item->category,
            'storename' => $item->storename,
            'batchdate' => $item->batchdate,
            'waste_declaration_date' => $item->waste_declaration_date,
            'price' => (float) $item->price, // Add price
            'throw_away' => (float) $item->throw_away,
            'early_molds' => (float) $item->early_molds,
            'pull_out' => (float) $item->pull_out,
            'rat_bites' => (float) $item->rat_bites,
            'ant_bites' => (float) $item->ant_bites,
            'total_price' => $totalPrice // Add total price
        ];
    });

    // Get available stores for filtering
    $stores = [];
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->orderBy('NAME')
            ->get();
    } else {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->where('STOREID', $userStoreId)
            ->get();
    }

    // Return the view with data
    return Inertia::render('Reports/BadOrders', [
        'bo' => $data,
        'stores' => $stores,
        'userRole' => $role,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? []
        ]
    ]);
}

public function ro(Request $request)
{
    // Validate request
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;
    
    // Build query using stockcountingtrans table with receivedcount field
    $query = stockcountingtrans::select(
        'stockcountingtrans.ITEMID as itemid',
        'rbostoretables.NAME as storename',
        'inventtables.itemname as itemname',
        'rboinventtables.itemgroup as category',
        'stockcountingtrans.TRANSDATE as received_date',
        // Add price including tax from inventtablemodules table
        'inventtablemodules.priceincltax as price',
        DB::raw("CAST(IFNULL(SUM(stockcountingtrans.RECEIVEDCOUNT), 0) AS DECIMAL(10,2)) as received_qty")
    )
    ->join('rbostoretables', 'stockcountingtrans.STORENAME', '=', 'rbostoretables.NAME')
    ->join('inventtables', 'stockcountingtrans.ITEMID', '=', 'inventtables.itemid')
    ->join('rboinventtables', 'stockcountingtrans.ITEMID', '=', 'rboinventtables.itemid')
    // Join with inventtablemodules to get price information
    ->leftJoin('inventtablemodules', 'stockcountingtrans.ITEMID', '=', 'inventtablemodules.itemid')
    ->where('stockcountingtrans.RECEIVEDCOUNT', '>', 0);

    // Apply date filters
    if ($request->filled(['startDate', 'endDate'])) {
        // Format dates properly for database comparison when both start and end dates are provided
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $endDate = Carbon::parse($request->endDate)->endOfDay();
        
        // Use whereDate instead of whereBetween for more reliable date comparison
        $query->where(function($q) use ($startDate, $endDate) {
            $q->whereDate('stockcountingtrans.TRANSDATE', '>=', $startDate->format('Y-m-d'))
              ->whereDate('stockcountingtrans.TRANSDATE', '<=', $endDate->format('Y-m-d'));
        });
        
        // Add debug logging
        Log::info('Date filter applied on TRANSDATE with provided dates', [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'query' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
    } else {
        // If no date range is provided, default to current date
        $currentDate = Carbon::now()->format('Y-m-d');
        $query->whereDate('stockcountingtrans.TRANSDATE', $currentDate);
        
        // Add debug logging
        Log::info('Date filter applied on TRANSDATE with current date', [
            'currentDate' => $currentDate,
            'query' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
    }

    // Apply store filters based on user role
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('stockcountingtrans.STORENAME', $userStoreId);
    }

    // Group and order results
    $query->groupBy(
        'stockcountingtrans.ITEMID',
        'rbostoretables.NAME',
        'inventtables.itemname',
        'rboinventtables.itemgroup',
        'stockcountingtrans.TRANSDATE',
        'inventtablemodules.priceincltax'
    );

    // Format results
    $data = $query->orderBy('storename')->get()->map(function ($item) {
        // Calculate total price (price * received quantity)
        $totalPrice = (float) $item->price * (float) $item->received_qty;
        
        return [
            'itemid' => $item->itemid,
            'itemname' => $item->itemname,
            'category' => $item->category,
            'storename' => $item->storename,
            'received_date' => $item->received_date,
            'price' => (float) $item->price,
            'received_qty' => (float) $item->received_qty,
            'total_price' => $totalPrice // Add calculated total price
        ];
    });

    // Get available stores for filtering
    $stores = [];
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->orderBy('NAME')
            ->get();
    } else {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->where('STOREID', $userStoreId)
            ->get();
    }

    // Return the view with data
    return Inertia::render('Reports/ReceivedOrders', [
        'ro' => $data,
        'stores' => $stores,
        'userRole' => $role,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? []
        ]
    ]);
}

public function rd(Request $request)
{
    // Validate request
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;
    $isLoading = false;
    
    $query = rbotransactionsalestrans::select(
        'rbotransactionsalestrans.receiptid',
        'rbotransactionsalestrans.itemname',
        'rbotransactionsalestrans.store',
        'rbostoretables.NAME as storename',
        DB::raw('CAST(createddate as date) as createddate'),
        DB::raw('CASE 
            WHEN rbotransactionsalestrans.discofferid LIKE "%SENIOR%" 
            THEN rbotransactionsalestrans.discamount 
            ELSE 0 
        END as senior_discount'),
        DB::raw('CASE 
            WHEN rbotransactionsalestrans.discofferid LIKE "%PWD%" 
            THEN rbotransactionsalestrans.discamount 
            ELSE 0 
        END as pwd_discount'),
        DB::raw('CASE 
            WHEN rbotransactionsalestrans.discofferid = "25% One Day Before"
            THEN rbotransactionsalestrans.discamount 
            ELSE 0 
        END as one_day_before_discount'),
        'rbotransactionsalestrans.grossamount as grossamount',
        'rbotransactionsalestrans.discofferid as discname'
    )
    ->join('rbostoretables', 'rbotransactionsalestrans.store', '=', 'rbostoretables.NAME')
    ->where('rbotransactionsalestrans.discamount', '>', 0)
    ->where(function($query) {
        $query->where('rbotransactionsalestrans.discofferid', 'LIKE', '%SENIOR%')
              ->orWhere('rbotransactionsalestrans.discofferid', 'LIKE', '%PWD%')
              ->orWhere('rbotransactionsalestrans.discofferid', '=', '25% One Day Before');
    });

    // Apply date filters if provided
    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            Carbon::parse($request->startDate)->startOfDay(),
            Carbon::parse($request->endDate)->endOfDay()
        ]);
    }

    // Apply store filters based on user role
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactionsalestrans.store', $userStoreId);
    }

    // Get available stores based on user role
    $stores = [];
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->orderBy('NAME')
            ->get();
    } else {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->where('STOREID', $userStoreId)
            ->get();
    }

    // Get data
    $rd = $query->orderBy('createddate', 'desc')->get();

    // Calculate totals
    $totals = [
        'senior_discount' => $rd->sum('senior_discount'),
        'pwd_discount' => $rd->sum('pwd_discount'),
        'one_day_before_discount' => $rd->sum('one_day_before_discount')
    ];

    return Inertia::render('Reports/RegularDiscount', [
        'rd' => $rd,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? []
        ]
    ]);
}


public function inventory(Request $request)
{
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    $startDate = $request->filled('startDate') ? 
        Carbon::parse($request->startDate)->format('Y-m-d') : 
        Carbon::yesterday()->timezone('Asia/Manila')->format('Y-m-d');

    $endDate = $request->filled('endDate') ? 
        Carbon::parse($request->endDate)->format('Y-m-d') : 
        Carbon::today()->timezone('Asia/Manila')->format('Y-m-d');

    try {
        // Get stores based on user role
        $stores = ($role === 'ADMIN' || $role === 'SUPERADMIN') 
            ? rbostoretables::select('STOREID', 'NAME')->orderBy('NAME')->get()
            : rbostoretables::select('STOREID', 'NAME')->where('STOREID', $userStoreId)->get();

        // Build the base query - Get individual records instead of aggregating
        $query = DB::table('inventory_summaries')
            ->whereBetween('report_date', [$startDate, $endDate]);

        // Apply store filters based on user role
        if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
            if ($request->filled('stores')) {
                $query->whereIn('storename', $request->stores);
            }
        } else {
            $query->where('storename', $userStoreId);
        }

        // Get individual records with all fields including id
        $inventoryData = $query
            ->select([
                'id',
                'itemid',
                'itemname',
                'storename',
                'beginning',
                'received_delivery',
                'stock_transfer',
                'sales',
                'bundle_sales',
                'throw_away',
                'early_molds',
                'pull_out',
                'rat_bites',
                'ant_bites',
                'item_count',
                'ending',
                'variance',
                'report_date'
            ])
            ->whereNotLike('itemid', '%PRM-PRO%')
            ->orderBy('itemname')
            ->orderBy('storename')
            ->orderBy('report_date')
            ->get()
            ->map(function ($item) {
                // Ensure all numeric values are properly formatted as floats
                return [
                    'id' => $item->id,
                    'itemid' => $item->itemid,
                    'itemname' => $item->itemname,
                    'storename' => $item->storename,
                    'beginning' => (float) ($item->beginning ?? 0),
                    'received_delivery' => (float) ($item->received_delivery ?? 0),
                    'stock_transfer' => (float) ($item->stock_transfer ?? 0),
                    'sales' => (float) ($item->sales ?? 0),
                    'bundle_sales' => (float) ($item->bundle_sales ?? 0),
                    'throw_away' => (float) ($item->throw_away ?? 0),
                    'early_molds' => (float) ($item->early_molds ?? 0),
                    'pull_out' => (float) ($item->pull_out ?? 0),
                    'rat_bites' => (float) ($item->rat_bites ?? 0),
                    'ant_bites' => (float) ($item->ant_bites ?? 0),
                    'item_count' => (float) ($item->item_count ?? 0),
                    'ending' => (float) ($item->ending ?? 0),
                    'variance' => (float) ($item->variance ?? 0),
                    'report_date' => $item->report_date
                ];
            });

        return Inertia::render('Reports/Inventory', [
            'inventory' => $inventoryData,
            'stores' => $stores,
            'userRole' => $role,
            'filters' => [
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
                'selectedStores' => $request->stores ?? []
            ],
            'url' => route('reports.inventory')
        ]);

    } catch (\Exception $e) {
        Log::error('Inventory Report Error:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return back()->with('error', 'An error occurred while generating the inventory report: ' . $e->getMessage());
    }
}

    /**
 * Get bundle sales for item
 */
private function getBundleSalesForItem($itemId, $storeName, $startDate, $endDate)
{
    try {
        // Check if table exists before querying
        if (Schema::hasTable('bundle_sales')) {
            return DB::table('bundle_sales')
                ->where('component_itemid', $itemId)
                ->where('store', $storeName)
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->sum('qty') ?? 0;
        }
        
        // If the table doesn't exist, return 0
        return 0;
    } catch (\Exception $e) {
        \Log::warning('Error calculating bundle sales: ' . $e->getMessage());
        return 0;
    }
}

public function sales(Request $request)
{
    DB::statement("
        DELETE t1
        FROM rbotransactionsalestrans t1
        JOIN rbotransactionsalestrans t2
        ON t1.transactionid = t2.transactionid
        AND t1.linenum = t2.linenum
        AND t1.id > t2.id
    ");

    DB::statement("
        DELETE t1
        FROM rbotransactiontables t1
        JOIN rbotransactiontables t2
        ON t1.transactionid = t2.transactionid
        AND t1.id > t2.id
    ");
    
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    // DB::statement("
    //     DELETE t1
    //     FROM rbotransactionsalestrans t1
    //     JOIN rbotransactionsalestrans t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.linenum = t2.linenum
    //     AND t1.id > t2.id
    // ");

    // DB::statement("
    //     DELETE t1
    //     FROM rbotransactiontables t1
    //     JOIN rbotransactiontables t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.id > t2.id
    // ");

    $query = rbotransactiontables::select(
        'rbotransactiontables.store as storename', 
        DB::raw('SUM(rbotransactiontables.netamount) as netamount'),
        DB::raw('SUM(rbotransactiontables.discamount) as discamount'),
        DB::raw('SUM(rbotransactiontables.grossamount) as grossamount')
    )
    ->groupBy('rbotransactiontables.store')
    ->join('rbostoretables', 'rbotransactiontables.store', '=', 'rbostoretables.NAME');

    if ($request->filled(['startDate', 'endDate'])) {
        $startDate = Carbon::parse($request->startDate)->startOfDay();  
        $endDate = Carbon::parse($request->endDate)->endOfDay();  
        $query->whereBetween('rbotransactiontables.createddate', [$startDate, $endDate]);
    }

    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactiontables.store', $userStoreId); 
    }

    $stores = rbostoretables::select('STOREID', 'NAME')
        ->orderBy('NAME')
        ->get();

    $ec = $query->orderBy('createddate', 'desc')->get();

    $totals = [
        'grossamount' => $ec->sum('grossamount'),
        'discamount' => $ec->sum('discamount'),
        'netamount' => $ec->sum('netamount'),
    ];

    return Inertia::render('Reports/Sales', [
        'ec' => $ec,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? [],
        ],
    ]);
}

public function tsales(Request $request)
{
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string',
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    // Clean up duplicate records
    DB::statement("
        DELETE t1
        FROM rbotransactionsalestrans t1
        JOIN rbotransactionsalestrans t2
        ON t1.transactionid = t2.transactionid
        AND t1.linenum = t2.linenum
        AND t1.id > t2.id
    ");

    DB::statement("
        DELETE t1
        FROM rbotransactiontables t1
        JOIN rbotransactiontables t2
        ON t1.transactionid = t2.transactionid
        AND t1.id > t2.id
    ");

    $query = DB::table('rbotransactionsalestrans')
        ->select(
            DB::raw('DATE(rbotransactionsalestrans.createddate) as createddate'),
            DB::raw('TIME(rbotransactionsalestrans.createddate) as timeonly'),
            'rbotransactionsalestrans.transactionid',
            DB::raw("REGEXP_REPLACE(rbotransactionsalestrans.receiptid, '[^0-9]', '') as receiptid"),
            DB::raw('rbotransactionsalestrans.grossamount as total_grossamount'),
            DB::raw('rbotransactionsalestrans.costamount as total_costamount'),
            'rbotransactionsalestrans.paymentmethod',
            'rbotransactionsalestrans.itemgroup',
            'rbotransactionsalestrans.itemname',
            'rbotransactionsalestrans.qty',
            'rbotransactionsalestrans.price',
            'rbotransactionsalestrans.custaccount',
            'rbotransactionsalestrans.discofferid',
            DB::raw('rbotransactionsalestrans.discamount as total_discamount'),
            DB::raw('rbotransactionsalestrans.price / 1.12 as total_costprice'),
            DB::raw('rbotransactionsalestrans.netamount as total_netamount'),
            DB::raw('rbotransactionsalestrans.netamountnotincltax as vatablesales'),
            DB::raw('rbotransactionsalestrans.taxinclinprice as vat'),
            'rbotransactionsalestrans.store as storename',
            'rbotransactionsalestrans.staff',
            'rbotransactionsalestrans.remarks',
            
            // Get transaction totals for percentage calculation
            DB::raw('rbt.netamount as transaction_total_netamount'),
            
            // Calculate proportional payment methods based on item's netamount vs transaction total
            DB::raw('
                CASE 
                    WHEN rbt.netamount > 0 
                    THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.charge, 0), 2)
                    ELSE 0 
                END as charge
            '),
            DB::raw('
                CASE 
                    WHEN rbt.netamount > 0 
                    THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.gcash, 0), 2)
                    ELSE 0 
                END as gcash
            '),
            DB::raw('
                CASE 
                    WHEN rbt.netamount > 0 
                    THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.paymaya, 0), 2)
                    ELSE 0 
                END as paymaya
            '),
            DB::raw('
                CASE 
                    WHEN rbt.netamount > 0 
                    THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.cash, 0), 2)
                    ELSE 0 
                END as cash
            '),
            DB::raw('
                CASE 
                    WHEN rbt.netamount > 0 
                    THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.card, 0), 2)
                    ELSE 0 
                END as card
            '),
            DB::raw('
                CASE 
                    WHEN rbt.netamount > 0 
                    THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.loyaltycard, 0), 2)
                    ELSE 0 
                END as loyaltycard
            '),
            DB::raw('
                CASE 
                    WHEN rbt.netamount > 0 
                    THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.foodpanda, 0), 2)
                    ELSE 0 
                END as foodpanda
            '),
            DB::raw('
                CASE 
                    WHEN rbt.netamount > 0 
                    THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.grabfood, 0), 2)
                    ELSE 0 
                END as grabfood
            '),
            DB::raw('
                CASE 
                    WHEN rbt.netamount > 0 
                    THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.representation, 0), 2)
                    ELSE 0 
                END as representation
            '),
            
            // Commission calculation based on remarks
            DB::raw("
                CASE 
                    WHEN LOWER(TRIM(rbotransactionsalestrans.remarks)) = 'qrph' 
                    THEN rbotransactionsalestrans.netamount - 0.75
                    ELSE 0 
                END as commission
            "),
            
            // RD Discount - include '20% EMPLOYEE DISCOUNT'
            DB::raw("
                CASE 
                    WHEN rbotransactionsalestrans.discofferid LIKE '%SENIOR%' 
                      OR rbotransactionsalestrans.discofferid LIKE '%PWD%'
                      OR rbotransactionsalestrans.discofferid = '25% One Day Before'
                      OR rbotransactionsalestrans.discofferid = '20% EMPLOYEE DISCOUNT'
                      OR rbotransactionsalestrans.discofferid = '40% OFF'
                      OR rbotransactionsalestrans.discofferid = '50% OFF'
                    THEN rbotransactionsalestrans.discamount 
                    ELSE 0 
                END as rddisc
            "),
            
            // Marketing Discount - exclude '20% EMPLOYEE DISCOUNT'
            DB::raw("
                CASE 
                    WHEN rbotransactionsalestrans.discofferid IS NOT NULL 
                      AND rbotransactionsalestrans.discofferid != ''
                      AND rbotransactionsalestrans.discofferid NOT LIKE '%SENIOR%' 
                      AND rbotransactionsalestrans.discofferid NOT LIKE '%PWD%'
                      AND rbotransactionsalestrans.discofferid != '25% One Day Before'
                      AND rbotransactionsalestrans.discofferid != '20% EMPLOYEE DISCOUNT'
                      AND rbotransactionsalestrans.discofferid != '40% OFF'
                      AND rbotransactionsalestrans.discofferid != '50% OFF'
                    THEN rbotransactionsalestrans.discamount 
                    ELSE 0 
                END as mrktgdisc
            "),
            
            // Product categories based on your requirements
            DB::raw("
                CASE 
                    WHEN UPPER(rbotransactionsalestrans.itemgroup) LIKE '%BW%' 
                    THEN rbotransactionsalestrans.grossamount 
                    ELSE 0 
                END as bw_products
            "),
            DB::raw("
                CASE 
                    WHEN UPPER(rbotransactionsalestrans.itemgroup) NOT LIKE '%BW%' 
                      AND UPPER(rbotransactionsalestrans.itemname) != 'PARTYCAKES'
                    THEN rbotransactionsalestrans.grossamount 
                    ELSE 0 
                END as merchandise
            "),
            DB::raw("
                CASE 
                    WHEN UPPER(rbotransactionsalestrans.itemname) = 'PARTY CAKES' 
                    THEN rbotransactionsalestrans.grossamount 
                    ELSE 0 
                END as partycakes
            ")
        )
        ->leftJoin('rbostoretables', 'rbotransactionsalestrans.store', '=', 'rbostoretables.STOREID')
        ->leftJoin('rbotransactiontables as rbt', function($join) {
            $join->on('rbotransactionsalestrans.transactionid', '=', 'rbt.transactionid')
                 ->on('rbotransactionsalestrans.store', '=', 'rbt.store');
        })
        ->orderBy('rbotransactionsalestrans.transactionid', 'DESC');

    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            $request->startDate . ' 00:00:00',
            $request->endDate . ' 23:59:59',
        ]);
    }

    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactionsalestrans.store', $userStoreId);
    }

    $stores = rbostoretables::select('STOREID', 'NAME')
        ->orderBy('NAME')
        ->get();

    $ec = $query->orderBy('createddate', 'desc')->get();

    $totals = [
        'grossamount' => $ec->sum('total_grossamount'),
        'discamount' => $ec->sum('total_discamount'),
        'netamount' => $ec->sum('total_netamount'),
        'commission' => $ec->sum('commission'),
    ];

    return Inertia::render('Reports/TSales', [
        'ec' => $ec,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? [],
        ],
    ]);
}


public function itemsales(Request $request)
{
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string',
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    //  DB::statement("
    //     DELETE t1
    //     FROM rbotransactionsalestrans t1
    //     JOIN rbotransactionsalestrans t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.linenum = t2.linenum
    //     AND t1.id > t2.id
    // ");

    // DB::statement("
    //     DELETE t1
    //     FROM rbotransactiontables t1
    //     JOIN rbotransactiontables t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.id > t2.id
    // "); 

    $query = DB::table('rbotransactionsalestrans')
    ->select(
        'rbotransactionsalestrans.store as storename',
        'rbotransactionsalestrans.itemname',
        'rbotransactionsalestrans.itemgroup',
        'rbotransactionsalestrans.price as price', 
        DB::raw('DATE(rbotransactionsalestrans.createddate) as createddate'),
        DB::raw('SUM(rbotransactionsalestrans.grossamount) as total_grossamount'),
        DB::raw('SUM(rbotransactionsalestrans.costamount) as total_costamount'),
        DB::raw('SUM(rbotransactionsalestrans.qty) as qty'),
        DB::raw('SUM(rbotransactionsalestrans.discamount) as total_discamount'),
        DB::raw('SUM(rbotransactionsalestrans.qty * rbotransactionsalestrans.price / 1.12) as total_costprice'), 
        DB::raw('SUM(rbotransactionsalestrans.netamount) as total_netamount'),
        DB::raw('SUM(rbotransactionsalestrans.netamountnotincltax) as vatablesales'),
        DB::raw('SUM(rbotransactionsalestrans.taxinclinprice) as vat')
    )
    ->leftJoin('rbostoretables', 'rbotransactionsalestrans.store', '=', 'rbostoretables.STOREID')
    /* ->where('rbotransactionsalestrans.itemgroup', '!=', 'BW PROMO') */
    ->groupBy(
        'rbotransactionsalestrans.itemname',
        DB::raw('DATE(rbotransactionsalestrans.createddate)'), 
        'rbotransactionsalestrans.store',
        'rbotransactionsalestrans.itemgroup',
        'rbotransactionsalestrans.price'
    );

    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            $request->startDate . ' 00:00:00',
            $request->endDate . ' 23:59:59',
        ]);
    }

    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactionsalestrans.store', $userStoreId);
    }

    $stores = rbostoretables::select('STOREID', 'NAME')
        ->orderBy('NAME')
        ->get();

    $ec = $query->orderBy('createddate', 'desc')->get();

    $totals = [
        'grossamount' => $ec->sum('grossamount'),
        'discamount' => $ec->sum('discamount'),
        'netamount' => $ec->sum('netamount'),
    ];

    return Inertia::render('Reports/ItemSales', [
        'ec' => $ec,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? [],
        ],
    ]);
}


public function md(Request $request)
{
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string',
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    // DB::statement("
    //     DELETE t1
    //     FROM rbotransactionsalestrans t1
    //     JOIN rbotransactionsalestrans t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.linenum = t2.linenum
    //     AND t1.id > t2.id
    // ");

    // DB::statement("
    //     DELETE t1
    //     FROM rbotransactiontables t1
    //     JOIN rbotransactiontables t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.id > t2.id
    // "); 

    $query = DB::table('rbotransactionsalestrans')
    ->select(
        DB::raw('DATE(rbotransactionsalestrans.createddate) as createddate'),
        'rbotransactionsalestrans.store as storename',
        'rbotransactionsalestrans.discofferid',
        DB::raw('TIME(rbotransactionsalestrans.createddate) as timeonly'),
        DB::raw('SUM(rbotransactionsalestrans.grossamount) as total_grossamount'),
        DB::raw('SUM(rbotransactionsalestrans.costamount) as total_costamount'),
        DB::raw('SUM(rbotransactionsalestrans.qty) as qty'),
        DB::raw('SUM(rbotransactionsalestrans.qty * rbotransactionsalestrans.price) as price'), 
        DB::raw('SUM(rbotransactionsalestrans.discamount) as total_discamount'),
        DB::raw('SUM(rbotransactionsalestrans.qty * rbotransactionsalestrans.price / 1.12) as total_costprice'), 
        DB::raw('SUM(rbotransactionsalestrans.netamount) as total_netamount'),
        DB::raw('SUM(rbotransactionsalestrans.netamountnotincltax) as vatablesales'),
        DB::raw('SUM(rbotransactionsalestrans.taxinclinprice) as vat')
    )
    ->leftJoin('rbostoretables', 'rbotransactionsalestrans.store', '=', 'rbostoretables.STOREID')
    ->where('rbotransactionsalestrans.discofferid', '!=', null)
    ->groupBy(
        'rbotransactionsalestrans.discofferid',
        'rbotransactionsalestrans.createddate',
        'rbotransactionsalestrans.store' 
    );

    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            $request->startDate . ' 00:00:00',
            $request->endDate . ' 23:59:59',
        ]);
    }

    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactionsalestrans.store', $userStoreId);
    }

    $stores = rbostoretables::select('STOREID', 'NAME')
        ->orderBy('NAME')
        ->get();

    $ec = $query->orderBy('createddate', 'desc')->get();

    $totals = [
        'grossamount' => $ec->sum('grossamount'),
        'discamount' => $ec->sum('discamount'),
        'netamount' => $ec->sum('netamount'),
    ];

    return Inertia::render('Reports/MarketingDiscount', [
        'ec' => $ec,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? [],
        ],
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

    /**
 * Adjust item count with remarks
 */
public function adjustItemCount(Request $request)
{
    try {
        $request->validate([
            'id' => 'required|integer',
            'adjustment_value' => 'required|numeric',
            'adjustment_type' => 'required|in:add,subtract,set',
            'remarks' => 'required|string|max:500'
        ]);

        DB::beginTransaction();

        // Find the inventory summary record
        $inventorySummary = DB::table('inventory_summaries')->where('id', $request->id)->first();
        
        if (!$inventorySummary) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Inventory record not found'
            ], 404);
        }

        $currentItemCount = (float) ($inventorySummary->item_count ?? 0);
        $adjustmentValue = (float) $request->adjustment_value;
        $newItemCount = $currentItemCount;

        // Calculate new item count based on adjustment type
        switch ($request->adjustment_type) {
            case 'add':
                $newItemCount = $currentItemCount + $adjustmentValue;
                break;
            case 'subtract':
                $newItemCount = $currentItemCount - $adjustmentValue;
                break;
            case 'set':
                $newItemCount = $adjustmentValue;
                break;
        }

        // Ensure item count doesn't go negative
        if ($newItemCount < 0) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Item count cannot be negative'
            ], 400);
        }

        // Update inventory summary
        DB::table('inventory_summaries')
            ->where('id', $request->id)
            ->update([
                'item_count' => $newItemCount,
                'remarks' => $request->remarks,
                'updated_at' => now()
            ]);

        // Recalculate variance after adjustment
        $ending = (float) ($inventorySummary->ending ?? 0);
        $newVariance = $ending - $newItemCount;
        
        DB::table('inventory_summaries')
            ->where('id', $request->id)
            ->update([
                'variance' => $newVariance
            ]);

        // Check if inventory_adjustments table exists before logging
        if (Schema::hasTable('inventory_adjustments')) {
            // Log the adjustment for audit trail
            DB::table('inventory_adjustments')->insert([
                'inventory_summary_id' => $request->id,
                'itemid' => $inventorySummary->itemid,
                'storename' => $inventorySummary->storename,
                'report_date' => $inventorySummary->report_date,
                'old_item_count' => $currentItemCount,
                'new_item_count' => $newItemCount,
                'adjustment_value' => $adjustmentValue,
                'adjustment_type' => $request->adjustment_type,
                'remarks' => $request->remarks,
                'adjusted_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Item count adjusted successfully',
            'data' => [
                'old_item_count' => $currentItemCount,
                'new_item_count' => $newItemCount,
                'new_variance' => $newVariance
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error adjusting item count: ' . $e->getMessage(), [
            'request_data' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while adjusting item count. Please try again.'
        ], 500);
    }
}

/**
 * Get adjustment history for an item
 */
public function getAdjustmentHistory(Request $request)
{
    try {
        $request->validate([
            'itemid' => 'required|string',
            'storename' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date'
        ]);

        // Check if inventory_adjustments table exists
        if (!Schema::hasTable('inventory_adjustments')) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No adjustment history available'
            ]);
        }

        $query = DB::table('inventory_adjustments')
            ->where('itemid', $request->itemid)
            ->where('storename', $request->storename)
            ->leftJoin('users', 'inventory_adjustments.adjusted_by', '=', 'users.id')
            ->select(
                'inventory_adjustments.*',
                'users.name as adjusted_by_name'
            );

        if ($request->filled('start_date')) {
            $query->whereDate('inventory_adjustments.created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('inventory_adjustments.created_at', '<=', $request->end_date);
        }

        $adjustments = $query->orderBy('inventory_adjustments.created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $adjustments
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error fetching adjustment history: ' . $e->getMessage(), [
            'request_data' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while fetching adjustment history'
        ], 500);
    }
}
}
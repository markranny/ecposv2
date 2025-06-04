<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\rbotransactiontables;
use App\Models\rbotransactionsalestrans;
use App\Models\stockcountingtrans;
use App\Models\receivedordertrans;
use App\Models\wastedeclarationtrans;
use App\Models\rbostoretables;
use Inertia\Inertia;
use Carbon\Carbon;
use ApacheSpark\SparkContext;
use Illuminate\Support\Facades\Storage;


use Illuminate\Http\Request;

class ECReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Reports/index');
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
    
    $query = rbotransactiontables::select(
        'rbotransactiontables.receiptid',
        'rbotransactiontables.store',
        'rbostoretables.NAME as storename',
        DB::raw('CAST(createddate as date) as createddate'),  
        DB::raw('COALESCE(charge, 0.00) as charge'),  
        DB::raw('COALESCE(gcash, 0.00) as gcash'),  
        DB::raw('COALESCE(paymaya, 0.00) as paymaya'),  
        DB::raw('COALESCE(card, 0.00) as card'),  
        DB::raw('COALESCE(loyaltycard, 0.00) as loyaltycard'), 
        DB::raw('COALESCE(foodpanda, 0.00) as foodpanda'),  
        DB::raw('COALESCE(grabfood, 0.00) as grabfood'),  
        DB::raw('COALESCE(representation, 0.00) as representation'),
        DB::raw('(
            COALESCE(charge, 0) + COALESCE(gcash, 0) + COALESCE(paymaya, 0) + 
            COALESCE(card, 0) + COALESCE(loyaltycard, 0) + COALESCE(foodpanda, 0) + 
            COALESCE(grabfood, 0) + COALESCE(representation, 0)
        ) as total_amount')
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

    // Calculate totals
    $totals = [
        'charge' => $ar->sum('charge'),
        'gcash' => $ar->sum('gcash'),
        'paymaya' => $ar->sum('paymaya'),
        'card' => $ar->sum('card'),
        'loyaltycard' => $ar->sum('loyaltycard'),
        'foodpanda' => $ar->sum('foodpanda'),
        'grabfood' => $ar->sum('grabfood'),
        'representation' => $ar->sum('representation'),
        'total' => $ar->sum('total_amount')
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
        $request->validate([
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'stores' => 'nullable|array',
            'stores.*' => 'string'  
        ]);

        $role = Auth::user()->role;
        $userStoreId = Auth::user()->storeid;
        
        $query = rbotransactiontables::select(
            'rbotransactiontables.receiptid',
            'rbotransactiontables.store',
            'rbostoretables.NAME as storename',
            'rbotransactiontables.custaccount as custaccount',
            DB::raw('CAST(createddate as date) as createddate'),  
            'rbotransactiontables.grossamount as grossamount',
            'rbotransactiontables.discamount as discamount',
            'rbotransactiontables.netamount as netamount',
            'rbotransactiontables.taxinclinprice as Vat',
            DB::raw('IFNULL(netamountnotincltax, 0.00) as Vatablesales')
        )
        ->where('charge','>=', 1)
        ->join('rbostoretables', 'rbotransactiontables.store', '=', 'rbostoretables.NAME');

        if ($request->filled(['startDate', 'endDate'])) {
            $query->whereBetween('createddate', [
                $request->startDate . ' 00:00:00',
                $request->endDate . ' 23:59:59'
            ]);
        }

        if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
            if ($request->filled('stores')) {
                $query->whereIn('rbostoretables.NAME', $request->stores);
            }
        } else {
            $query->where('rbotransactiontables.store', $userStoreId);
        }

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

        $ec = $query->orderBy('createddate', 'desc')->get();

        $totals = [
            'grossamount' => $ec->sum('grossamount'),
            'discamount' => $ec->sum('discamount'),
            'netamount' => $ec->sum('netamount'),
            'taxinclinprice' => $ec->sum('taxinclinprice'),
            'netamountnotincltax' => $ec->sum('netamountnotincltax'),
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
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;
    
    // First, let's debug the query
    \Log::info('Starting BO query');
    
    $query = wastedeclarationtrans::select(
        'wastedeclarationtrans.itemid',
        'rbostoretables.NAME as storename',
        'inventtables.itemname as itemname',
        'wastedeclarationtrans.reason as reason',
        DB::raw('COUNT(*) as total_count')
    )
    /* ->where('counted','=','0') */
    ->join('rbostoretables', 'wastedeclarationtrans.storename', '=', 'rbostoretables.NAME')
    ->join('inventtables', 'wastedeclarationtrans.itemid', '=', 'inventtables.itemid');

    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('transdate', [
            $request->startDate . ' 00:00:00',
            $request->endDate . ' 23:59:59'
        ]);
    }

    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('wastedeclarationtrans.storename', $userStoreId);
    }

    // Group by all necessary fields
    $query->groupBy(
        'wastedeclarationtrans.itemid',
        'rbostoretables.NAME',
        'inventtables.itemname',
        'wastedeclarationtrans.reason'
    );

    $rawData = $query->orderBy('storename')->get();
    
    \Log::info('Raw data count: ' . count($rawData));
    
    // Transform the data
    $transformedData = [];
    foreach ($rawData as $record) {
        $key = $record->itemid . '-' . $record->storename;
        
        if (!isset($transformedData[$key])) {
            $transformedData[$key] = [
                'itemid' => $record->itemid,
                'itemname' => $record->itemname,
                'storename' => $record->storename,
                'throw_away' => 0,
                'early_molds' => 0,
                'pull_out' => 0,
                'rat_bites' => 0,
                'ant_bites' => 0
            ];
        }

        // Convert reason to lowercase for case-insensitive comparison
        $reason = strtolower(trim($record->reason));
        
        switch ($reason) {
            case 'throw away':
                $transformedData[$key]['throw_away'] = $record->total_count;
                break;
            case 'early molds':
                $transformedData[$key]['early_molds'] = $record->total_count;
                break;
            case 'pull out':
                $transformedData[$key]['pull_out'] = $record->total_count;
                break;
            case 'rat bites':
                $transformedData[$key]['rat_bites'] = $record->total_count;
                break;
            case 'ant bites':
                $transformedData[$key]['ant_bites'] = $record->total_count;
                break;
        }
    }

    \Log::info('Transformed data count: ' . count($transformedData));

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

    return Inertia::render('Reports/BadOrders', [
        'bo' => array_values($transformedData),
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
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;
    
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
        'rbotransactionsalestrans.grossamount as grossamount',
        'rbotransactionsalestrans.discofferid as discname'
    )
    ->join('rbostoretables', 'rbotransactionsalestrans.store', '=', 'rbostoretables.NAME');

    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            $request->startDate . ' 00:00:00',
            $request->endDate . ' 23:59:59'
        ]);
    }

    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactionsalestrans.store', $userStoreId);
    }

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

    $rd = $query->orderBy('createddate', 'desc')->get();

    $totals = [
        'senior_discount' => $rd->sum('discamount'),
        'pwd_discount' => $rd->sum('discamount')
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
        
        $query = StockCountingTrans::select(
            'inventtables.itemid',
            'inventtables.itemname',
            DB::raw('SUM(stockcountingtrans.counted) as beginning'),
            DB::raw('SUM(receivedordertrans.counted) as received_delivery'),
            DB::raw('SUM(rbotransactionsalestrans.qty) as sales'),
            DB::raw('SUM(CASE WHEN wastedeclarationtrans.REASON = "THROW AWAY" THEN wastedeclarationtrans.counted ELSE 0 END) as throw_away'),
            DB::raw('SUM(CASE WHEN wastedeclarationtrans.REASON = "EARLY MOLDS" THEN wastedeclarationtrans.counted ELSE 0 END) as early_molds'),
            DB::raw('SUM(CASE WHEN wastedeclarationtrans.REASON = "PULL OUT" THEN wastedeclarationtrans.counted ELSE 0 END) as pull_out'),
            DB::raw('SUM(CASE WHEN wastedeclarationtrans.REASON = "RAT BITES" THEN wastedeclarationtrans.counted ELSE 0 END) as rat_bites'),
            DB::raw('SUM(CASE WHEN wastedeclarationtrans.REASON = "ANT BITES" THEN wastedeclarationtrans.counted ELSE 0 END) as ant_bites'),
            DB::raw('(
                COALESCE(SUM(stockcountingtrans.counted), 0) + 
                COALESCE(SUM(receivedordertrans.counted), 0) - 
                COALESCE(SUM(rbotransactionsalestrans.qty), 0) - 
                COALESCE(SUM(CASE WHEN wastedeclarationtrans.REASON = "THROW AWAY" THEN wastedeclarationtrans.counted ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN wastedeclarationtrans.REASON = "EARLY MOLDS" THEN wastedeclarationtrans.counted ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN wastedeclarationtrans.REASON = "PULL OUT" THEN wastedeclarationtrans.counted ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN wastedeclarationtrans.REASON = "RAT BITES" THEN wastedeclarationtrans.counted ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN wastedeclarationtrans.REASON = "ANT BITES" THEN wastedeclarationtrans.counted ELSE 0 END), 0)
            ) as ending'),
            DB::raw('(
                (COALESCE(SUM(stockcountingtrans.counted), 0) + 
                COALESCE(SUM(receivedordertrans.counted), 0) - 
                COALESCE(SUM(rbotransactionsalestrans.qty), 0) - 
                COALESCE(SUM(CASE WHEN wastedeclarationtrans.REASON = "THROW AWAY" THEN wastedeclarationtrans.counted ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN wastedeclarationtrans.REASON = "EARLY MOLDS" THEN wastedeclarationtrans.counted ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN wastedeclarationtrans.REASON = "PULL OUT" THEN wastedeclarationtrans.counted ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN wastedeclarationtrans.REASON = "RAT BITES" THEN wastedeclarationtrans.counted ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN wastedeclarationtrans.REASON = "ANT BITES" THEN wastedeclarationtrans.counted ELSE 0 END), 0)) -
                COALESCE(SUM(stockcountingtrans.counted), 0)
            ) as variance')
        )
        ->leftJoin('inventtables', 'stockcountingtrans.itemid', '=', 'inventtables.itemid')
        ->leftJoin('receivedordertrans', function($join) {
            $join->on('stockcountingtrans.itemid', '=', 'receivedordertrans.itemid')
                 ->on('stockcountingtrans.storename', '=', 'receivedordertrans.storename');
        })
        ->leftJoin('rbotransactionsalestrans', function($join) {
            $join->on('stockcountingtrans.itemid', '=', 'rbotransactionsalestrans.itemid')
                 ->on('stockcountingtrans.storename', '=', 'rbotransactionsalestrans.store');
        })
        ->leftJoin('wastedeclarationtrans', function($join) {
            $join->on('stockcountingtrans.itemid', '=', 'wastedeclarationtrans.itemid')
                 ->on('stockcountingtrans.storename', '=', 'wastedeclarationtrans.storename');
        });

        if ($request->filled(['startDate', 'endDate'])) {
            $query->where(function($q) use ($request) {
                $q->whereBetween('stockcountingtrans.createddate', [
                    $request->startDate . ' 00:00:00',
                    $request->endDate . ' 23:59:59'
                ])
                ->orWhereBetween('receivedordertrans.createddate', [
                    $request->startDate . ' 00:00:00',
                    $request->endDate . ' 23:59:59'
                ])
                ->orWhereBetween('rbotransactionsalestrans.createddate', [
                    $request->startDate . ' 00:00:00',
                    $request->endDate . ' 23:59:59'
                ])
                ->orWhereBetween('wastedeclarationtrans.createddate', [
                    $request->startDate . ' 00:00:00',
                    $request->endDate . ' 23:59:59'
                ]);
            });
        }

        if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
            if ($request->filled('stores')) {
                $query->whereIn('stockcountingtrans.storename', function($q) use ($request) {
                    $q->select('STOREID')
                      ->from('rbostoretables')
                      ->whereIn('NAME', $request->stores);
                });
            }
        } else {
            $query->where('stockcountingtrans.storename', $userStoreId);
        }

        $query->groupBy('inventtables.itemid', 'inventtables.itemname');

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

        $inventory = $query->orderBy('inventtables.itemname')->get();

        return Inertia::render('Reports/Inventory', [
            'inventory' => $inventory,
            'stores' => $stores,
            'userRole' => $role,
            'filters' => [
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
                'selectedStores' => $request->stores ?? []
            ]
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

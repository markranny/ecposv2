<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\announcements;
use App\Models\windowtables;
use App\Models\windowtrans;
use App\Models\rbostoretables;
use App\Models\rbotransactiontables;
use App\Models\rbotransactionsalestrans;
use App\Models\receivedordertrans;
use App\Models\wastedeclarationtrans;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Agent;
use Detection\MobileDetect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class HomeController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;

        // Replace direct DB statements with a safer, recent-only batched approach
        $this->cleanupRecentDuplicates();

        $announcements = announcements::select('*')->get();

        if (in_array($role, ['SUPERADMIN', 'ADMIN', 'OPIC'])) {
            $metrics = $this->calculateMetrics();

            Artisan::call('optimize:clear');

            \Log::info('Metrics being passed:', $metrics);

            return Inertia::render('Home/admin', [
                'metrics' => $metrics,
                'announcements' => $this->getAnnouncements(),
                'username' => auth()->user()->name,
            ]);
        } else {
            return Inertia::render('Home/stores', ['announcements' => $announcements]);
        }
    }

    /**
     * Clean up only recent duplicate records in very small batches
     * This is the safest approach to avoid timeouts while still maintaining data integrity
     */
    private function cleanupRecentDuplicates()
    {
        try {
            // Use a much smaller batch size for safety
            $batchSize = 100;
            
            // Only process records from the last 24 hours
            $recentDate = now()->subHours(24)->format('Y-m-d H:i:s');
            
            // Process sales transactions
            $affected = DB::affectingStatement("
                DELETE t1 FROM rbotransactionsalestrans t1
                JOIN rbotransactionsalestrans t2 
                ON t1.transactionid = t2.transactionid
                AND t1.linenum = t2.linenum
                AND t1.id > t2.id
                WHERE t1.createddate >= ?
                LIMIT ?
            ", [$recentDate, $batchSize]);
            
            \Log::info("Removed {$affected} recent duplicate sales transaction records");
            
            // Process transaction tables
            $affected = DB::affectingStatement("
                DELETE t1 FROM rbotransactiontables t1
                JOIN rbotransactiontables t2 
                ON t1.transactionid = t2.transactionid
                AND t1.id > t2.id
                WHERE t1.createddate >= ?
                LIMIT ?
            ", [$recentDate, $batchSize]);
            
            \Log::info("Removed {$affected} recent duplicate transaction records");
            
        } catch (\Exception $e) {
            \Log::error('Error removing recent duplicate records: ' . $e->getMessage());
        }
    }

    public function getMetrics(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'nullable|array',
                'stores.*' => 'string'  // Changed from strict validation to allow store names
            ]);

            $query = rbotransactiontables::whereDate('createddate', '>=', $validated['start_date'])
                ->whereDate('createddate', '<=', $validated['end_date']);

            if (!empty($validated['stores'])) {
                // Handle both object and string store names
                $storeNames = collect($validated['stores'])->map(function ($store) {
                    return is_array($store) ? $store['NAME'] : $store;
                })->toArray();
                
                $query->whereIn('store', $storeNames);
            }

            $metrics = $this->calculateMetrics($query);

            return response()->json($metrics);
        } catch (\Exception $e) {
            \Log::error('Metrics Retrieval Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            
            return response()->json([
                'error' => 'Unable to retrieve metrics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getStores()
    {
        $stores = rbostoretables::select('NAME')
            ->orderBy('NAME')
            ->distinct()
            ->get();

        return response()->json($stores);
    }

    private function calculateMetrics($query = null)
    {
        $query = $query ?: rbotransactiontables::query();

        $baseMetrics = $query->select([
            DB::raw('COALESCE(SUM(grossamount), 0) as totalGross'),
            DB::raw('COALESCE(SUM(netamount), 0) as totalNetsales'),
            DB::raw('COALESCE(SUM(totaldiscamount), 0) as totalDiscount'),
            DB::raw('COALESCE(SUM(costamount), 0) as totalCost'),
            DB::raw('COALESCE(SUM(netamount - netamountnotincltax), 0) as totalVat'),
            DB::raw('COALESCE(SUM(netamountnotincltax), 0) as totalVatableSales'),
            DB::raw('COALESCE(SUM(cash), 0) as totalCash'),
            DB::raw('COALESCE(SUM(gcash), 0) as totalGcash'),
            DB::raw('COALESCE(SUM(paymaya), 0) as totalPaymaya'),
            DB::raw('COALESCE(SUM(card), 0) as totalCard'),
            DB::raw('COALESCE(SUM(loyaltycard), 0) as totalLoyaltyCard'),
            DB::raw('COALESCE(SUM(foodpanda), 0) as totalFoodPanda'),
            DB::raw('COALESCE(SUM(grabfood), 0) as totalGrabFood'),
            DB::raw('COALESCE(SUM(representation), 0) as totalrepresentation'),
        ])->first();

        $totalSales = $baseMetrics->totalNetsales ?: 1;

        $totalTransactions = rbotransactionsalestrans::whereDate('createddate', '>=', $query->getQuery()->wheres[0]['value'] ?? now()->subYears(10))
        ->whereDate('createddate', '<=', $query->getQuery()->wheres[1]['value'] ?? now())
        ->count();

        $totalReceivedDeliveries = receivedordertrans::whereDate('TRANSDATE', '>=', $query->getQuery()->wheres[0]['value'] ?? now()->subYears(10))
            ->whereDate('TRANSDATE', '<=', $query->getQuery()->wheres[1]['value'] ?? now())
            ->count();

        $totalWaste = wastedeclarationtrans::whereDate('TRANSDATE', '>=', $query->getQuery()->wheres[0]['value'] ?? now()->subYears(10))
            ->whereDate('TRANSDATE', '<=', $query->getQuery()->wheres[1]['value'] ?? now())
            ->count();

        return [
            'totalGross' => (float) ($baseMetrics->totalGross ?? 0),
            'totalNetsales' => (float) ($baseMetrics->totalNetsales ?? 0),
            'totalDiscount' => (float) ($baseMetrics->totalDiscount ?? 0),
            'totalCost' => (float) ($baseMetrics->totalCost ?? 0),
            'totalVat' => (float) ($baseMetrics->totalVat ?? 0),
            'totalVatableSales' => (float) ($baseMetrics->totalVatableSales ?? 0),
            'totalTransactions' => $totalTransactions,
            'totalReceivedDeliveries' => $totalReceivedDeliveries,
            'totalWaste' => $totalWaste,
            'paymentBreakdown' => [
                'cash' => round($baseMetrics->totalCash),
                'gcash' => round($baseMetrics->totalGcash),
                'paymaya' => round($baseMetrics->totalPaymaya),
                'card' => round($baseMetrics->totalCard),
                'loyaltyCard' => round($baseMetrics->totalLoyaltyCard),
                'foodPanda' => round($baseMetrics->totalFoodPanda),
                'grabFood' => round($baseMetrics->totalGrabFood),
                'representation' => round($baseMetrics->totalrepresentation),
            ],
            'todayTransactions' => rbotransactiontables::whereDate('createddate', now()->today())->count(),
        ];
    }

    public function getTopBottomProducts(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'exists:rbostoretables,NAME'
            ]);

            $query = rbotransactionsalestrans::select(
                'itemname',
                DB::raw('ABS(SUM(qty)) as total_quantity'),
                DB::raw('ABS(SUM(netamount)) as total_sales'),
                DB::raw('COUNT(DISTINCT store) as store_count')
            )
            ->whereDate('createddate', '>=', $validated['start_date'])
            ->whereDate('createddate', '<=', $validated['end_date']);

            if (!empty($validated['stores'])) {
                $query->whereIn('store', $validated['stores']);
            }

            $products = $query
                ->groupBy('itemname')
                ->having('total_quantity', '>', 0)
                ->get();

            // Convert to array and sort directly
            $productsArray = $products->map(function($item) {
                return [
                    'itemname' => $item->itemname,
                    'total_quantity' => round($item->total_quantity, 2),
                    'total_sales' => round($item->total_sales, 2),
                    'store_count' => $item->store_count
                ];
            })->toArray();

            // Sort for top products
            $topProducts = collect($productsArray)
                ->sortByDesc('total_sales')
                ->take(10)
                ->values()
                ->all();

            // Sort for bottom products
            $bottomProducts = collect($productsArray)
                ->sortBy('total_sales')
                ->take(10)
                ->values()
                ->all();

            return response()->json([
                'topProducts' => $topProducts,
                'bottomProducts' => $bottomProducts,
                'summary' => [
                    'total_products' => count($productsArray),
                    'date_range' => [
                        'start' => $validated['start_date'],
                        'end' => $validated['end_date']
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Top/Bottom Products Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to retrieve products'], 500);
        }
    }

    public function getMonthlySales(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'exists:rbostoretables,NAME'
            ]);

            \Log::info('Monthly Sales Request Received', [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'stores' => $validated['stores'] ?? 'All Stores'
            ]);

            $query = rbotransactiontables::select(
                DB::raw('YEAR(createddate) as year'),
                DB::raw('MONTH(createddate) as month'),
                DB::raw('SUM(grossamount) as total_sales')
            )
            ->whereBetween('createddate', [
                $validated['start_date'],
                $validated['end_date']
            ]);

            if (!empty($validated['stores'])) {
                $query->whereIn('store', $validated['stores']);
            }

            $monthlySales = $query
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(function($item) {
                    return [
                        'label' => date('F Y', mktime(0, 0, 0, $item->month, 1, $item->year)),
                        'total_sales' => $item->total_sales
                    ];
                });

            \Log::info('Monthly Sales Data', [
                'total_months' => $monthlySales->count()
            ]);

            return response()->json($monthlySales);
        } catch (\Exception $e) {
            \Log::error('Monthly Sales Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to retrieve monthly sales'], 500);
        }
    }

    public function getTopWastes(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'exists:rbostoretables,NAME'
            ]);

                $query = DB::table('wastedeclarationtrans as w')
                    ->join('inventtables as p', 'w.ITEMID', '=', 'p.ITEMID')
                    ->join('inventtablemodules as a', 'w.ITEMID', '=', 'a.ITEMID')
                    ->join('rboinventtables as b', 'w.ITEMID', '=', 'b.ITEMID')
                    ->select(
                        'w.ITEMID', 
                        'p.itemname as itemname', 
                        DB::raw('ABS(SUM(w.COUNTED)) AS total_waste_quantity'),
                        DB::raw('ABS(SUM(a.priceincltax)) AS total_waste_cost'),
                        DB::raw('COUNT(DISTINCT w.STORENAME) AS store_count')
                    )
                    ->whereBetween('w.TRANSDATE', [
                        $validated['start_date'],
                        $validated['end_date']
                    ]);
            
            if (!empty($validated['stores'])) {
                $query->whereIn('w.STORENAME', $validated['stores']);
            }

            $topWastes = $query
                ->groupBy('w.ITEMID', 'p.itemname')
                ->orderByDesc(DB::raw('ABS(SUM(w.SALESAMOUNT))'))
                ->limit(10)
                ->get();

            return response()->json([
                'data' => $topWastes,
                'summary' => [
                    'totalWasteCost' => $topWastes->sum('total_waste_cost'),
                    'totalWasteQuantity' => $topWastes->sum('total_waste_quantity'),
                    'uniqueStores' => $topWastes->sum('store_count')
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Top Wastes Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Unable to retrieve waste data'], 500);
        }
    }

    private function getAnnouncements()
    {
        return announcements::latest()
            ->take(5)
            ->get()
            ->map(fn($announcement) => [
                'title' => $announcement->title,
                'description' => $announcement->description,
            ]);
    }

    public function admin() {
        $role = Auth::user()->role;

        $announcements = announcements::select('*')->get();

        if (in_array($role, ['SUPERADMIN', 'ADMIN', 'OPIC'])) {
            $metrics = $this->calculateMetrics();

            \Log::info('Metrics being passed:', $metrics);

            return Inertia::render('Home/admin', [
                'metrics' => $metrics,
                'announcements' => $this->getAnnouncements(),
                'username' => auth()->user()->name,
            ]);
        } else {
            return Inertia::render('Home/stores', ['announcements' => $announcements]);
        }
    }

    public function pos() {
        $windowtables = windowtables::all();
        $windowtrans = windowtrans::all();
    
        $windows = DB::table('windowtables as a')
            ->leftJoin('windowtrans as b', 'a.id', '=', 'b.windownum')
            ->select('a.*', 'b.*') 
            ->where('b.windownum', '=', '1')
            ->get();
    
        return Inertia::render('Home/pos', [
            'windowtables' => $windowtables,
            'windowtrans' => $windowtrans,
            'windows' => $windows,
        ]);
    }
    
    public function downloadFile($id)
    {
        $announcement = Announcements::findOrFail($id);
        
        if (!$announcement->file_path) {
            return redirect()->back()
                    ->with('message', 'File not found!')
                    ->with('isError', true);
        }

        $path = storage_path('app/public/' . $announcement->file_path);

        if (!file_exists($path)) {
            return redirect()->back()
                    ->with('message', 'File not exist!')
                    ->with('isError', true);
        }

        return response()->download($path);
    }

    public function offline(){
        return Inertia::render('Offline');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\announcements;
use App\Models\windowtables;
use App\Models\windowtrans;
use App\Models\rbotransactiontables;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Agent;
use Detection\MobileDetect;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(){
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
    ])->first();

    // Ensure we don't divide by zero
    $totalSales = $baseMetrics->totalNetsales ?: 1;

    // Explicitly cast all values to float and ensure all keys exist
    return [
        'totalGross' => (float) ($baseMetrics->totalGross ?? 0),
        'totalNetsales' => (float) ($baseMetrics->totalNetsales ?? 0),
        'totalDiscount' => (float) ($baseMetrics->totalDiscount ?? 0),
        'totalCost' => (float) ($baseMetrics->totalCost ?? 0),
        'totalVat' => (float) ($baseMetrics->totalVat ?? 0),
        'totalVatableSales' => (float) ($baseMetrics->totalVatableSales ?? 0),
        'paymentBreakdown' => [
            'cash' => round(($baseMetrics->totalCash / $totalSales) * 100, 2),
            'gcash' => round(($baseMetrics->totalGcash / $totalSales) * 100, 2),
            'paymaya' => round(($baseMetrics->totalPaymaya / $totalSales) * 100, 2),
            'card' => round(($baseMetrics->totalCard / $totalSales) * 100, 2),
            'loyaltyCard' => round(($baseMetrics->totalLoyaltyCard / $totalSales) * 100, 2),
            'foodPanda' => round(($baseMetrics->totalFoodPanda / $totalSales) * 100, 2),
            'grabFood' => round(($baseMetrics->totalGrabFood / $totalSales) * 100, 2),
        ],
        'todayTransactions' => rbotransactiontables::whereDate('createddate', now()->today())->count(),
    ];
}

    public function getMetrics(Request $request)
{
    $validated = $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    $query = rbotransactiontables::whereBetween('created_at', [
        $validated['start_date'],
        $validated['end_date']
    ]);

    return response()->json($this->calculateMetrics($query));
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

        /* $role = Auth::user()->role;
        $announcements = Announcements::select('*')->get();

        if ($role == 'SUPERADMIN' || $role == 'ADMIN' || $role == 'OPIC' ) {
            return Inertia::render('Home/admin', ['announcements' => $announcements]);
        } else {
            return Inertia::render('Home/stores', ['announcements' => $announcements]);
        } */

        $role = Auth::user()->role;
    
        $announcements = announcements::select('*')->get();
    
        if (in_array($role, ['SUPERADMIN', 'ADMIN', 'OPIC'])) {
            $metrics = $this->calculateMetrics();
            
            \Log::info('Metrics being passed:', $metrics);
    
            return Inertia::render('Home/admin', [
                'metrics' => $metrics, // Make sure this is a complete array
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
        /* dd('test'); */
        $announcement = Announcements::findOrFail($id);
        
        if (!$announcement->file_path) {
            /* abort(404, 'File not found'); */
            return redirect()->back()
                    ->with('message', 'File not found!')
                    ->with('isError', true);
        }

        $path = storage_path('app/public/' . $announcement->file_path);

        if (!file_exists($path)) {
            /* abort(404, 'File not found'); */
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

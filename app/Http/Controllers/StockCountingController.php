<?php

namespace App\Http\Controllers;

use App\Models\stockcountingtables;
use App\Models\stockcountingtrans;
use App\Models\nubersequencevalues;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;
use Exception;

class StockCountingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $storeId = Auth::user()->storeid;
            $role = Auth::user()->role;

            if ($role == "ADMIN") {
                $stockcountingtables = DB::table('stockcountingtables AS a')
                    ->select('a.journalid', 'a.storeid', 'a.description',
                            DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS QTY'),
                            DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2))) AS AMOUNT'),
                            'a.posted', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                    ->leftJoin('stockcountingtrans AS b', 'b.JOURNALID', '=', 'a.journalid')
                    ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
                    ->where('a.posted', '!=', '1')
                    ->groupBy('a.journalid', 'a.storeid', 'a.description',
                            'a.posted', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                    ->orderBy('a.createddatetime', 'DESC')
                    ->get();
            } else {
                $stockcountingtables = DB::table('stockcountingtables AS a')
                    ->select('a.journalid', 'a.storeid', 'a.description',
                            DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS qty'),
                            DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2)) * CAST(b.COUNTED AS UNSIGNED)) AS amount'),
                            'a.posted', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                    ->leftJoin('stockcountingtrans AS b', 'b.JOURNALID', '=', 'a.journalid')
                    ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
                    ->where('storeid', '=', $storeId)
                    ->where('a.posted', '!=', '1')
                    ->groupBy('a.journalid', 'a.storeid', 'a.description',
                            'a.posted', 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                    ->orderBy('a.createddatetime', 'DESC')
                    ->get();
            }

            $currentDateTime = Carbon::now('Asia/Manila')->toDateString();
            
            $orders = [];
            if ($role === "STORE") {
                $orders = DB::table('stockcountingtables as b')
                    ->select(
                        'b.journalid',
                        'b.POSTEDDATETIME as POSTEDDATETIME',
                        'e.STOREID as STOREID', 
                        'b.STOREID as STORENAME',
                        'd.itemid as ITEMID',
                        'd.itemname as ITEMNAME',
                        'c.COUNTED as COUNTED'
                    )
                    ->leftJoin('stockcountingtrans as c', 'b.JOURNALID', '=', 'c.JOURNALID')
                    ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                    ->leftJoin('rbostoretables as e', 'b.STOREID', '=', 'e.NAME')
                    ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
                    ->where('b.POSTED', '=', '0')
                    ->where('b.STOREID', '=', $storeId) 
                    ->get();
            }

            return Inertia::render('StockCounting/Index', [
                'stockcountingtables' => $stockcountingtables,
                'orders' => $orders
            ]);
        } catch (Exception $e) {
            return redirect()->back()
                ->with('message', 'Error loading stock counting data: ' . $e->getMessage())
                ->with('isError', true);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $currentDateTime = Carbon::now('Asia/Manila');
            $storeId = Auth::user()->storeid;
            $userId = Auth::user()->id;

            $existingOrder = DB::table('stockcountingtables')
                ->whereDate('CREATEDDATETIME', $currentDateTime)
                ->where('STOREID', $storeId)
                ->exists();

            if ($existingOrder) {
                throw new Exception('You have already Stock Counting this time.');
            }

            $stocknextrec = DB::table('nubersequencevalues')
                ->where('storeid', $storeId)
                ->lockForUpdate()
                ->value('stocknextrec');

            $stocknextrec = $stocknextrec !== null ? (int)$stocknextrec + 1 : 1;

            DB::table('nubersequencevalues')
                ->where('STOREID', $storeId)
                ->update(['stocknextrec' => $stocknextrec]);

            $journalId = $userId . str_pad($stocknextrec, 8, '0', STR_PAD_LEFT);
            $description = "BATCH" . $journalId;

            DB::table('stockcountingtables')->insert([
                'JOURNALID' => $journalId,
                'STOREID' => $storeId,
                'DESCRIPTION' => $description,
                'POSTED' => "0",
                'POSTEDDATETIME' => $currentDateTime,
                'JOURNALTYPE' => "1",
                'DELETEPOSTEDLINES' => "0",
                'CREATEDDATETIME' => $currentDateTime,
            ]);

            DB::commit();

            return redirect()
                ->route('StockCounting.index', ['role' => Auth::user()->role])
                ->with('message', 'Stock Counting Created Successfully')
                ->with('isSuccess', true);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('StockCounting.index')
                ->with('message', $e->getMessage())
                ->with('isError', true);
        }
    }
}
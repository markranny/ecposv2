<?php

namespace App\Http\Controllers;

use App\Models\stockcountingtables;
use App\Models\stockcountingtrans;
use App\Models\numbersequencevalues;
use App\Models\inventtables;
use App\Models\control;
use App\Models\rboinventtables;
use App\Models\inventtablemodules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Google_Client;
use Google_Service_Sheets;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ApisStockCountingLineController extends Controller
{
    public function __construct()
    {
        Log::info('ApisStockCountingLineController initialized');
    }

    public function index()
    {
        Log::info('Accessing stock counting index');
        try {
            $storename = Auth::user()->storeid;
            $role = Auth::user()->role;
            
            Log::info('User details', [
                'store' => $storename,
                'role' => $role
            ]);

            $stockcountingtrans = DB::table('stockcountingtables as a')
                ->Join('stockcountingtrans as b', 'a.JOURNALID', '=', 'b.JOURNALID')
                ->leftJoin('inventtables as c', 'b.ITEMID', '=', 'c.itemid')
                ->where('a.STOREID', '=', $storename) 
                ->where('a.posted', '!=', '1')
                ->get();
            
            Log::info('Stock counting data retrieved', [
                'count' => $stockcountingtrans->count()
            ]);
        
            return Inertia::render('StockCountingLine/index', [
                'stockcountingtrans' => $stockcountingtrans
            ]);
        } catch (\Exception $e) {
            Log::error('Error in index method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->with('message', 'Error loading data: ' . $e->getMessage())
                ->with('isError', true);
        }
    }
    
    public function store(Request $request)
    {
        Log::info('Accessing store method', ['request' => $request->all()]);
        try {
            $request->validate([
                'itemname' => 'required|string',  
                'qty' => 'required|integer',  
            ]);
            
            Log::info('Validation passed', [
                'itemname' => $request->itemname,
                'qty' => $request->qty
            ]);
            
            $currentDateTime = Carbon::now('Asia/Manila')->toDateString();
            $storename = Auth::user()->storeid;

            DB::table('stockcountingtrans')->insert([
                'JOURNALID' => $request->JOURNALID,
                'LINENUM' => '',
                'TRANSDATE' => $currentDateTime,
                'ITEMID' => $request->itemname,
                'COUNTED' => $request->qty,    
                'updated_at' => $currentDateTime,
                'storename' => $storename,                
            ]);

            Log::info('Stock counting record created', [
                'journalId' => $request->JOURNALID,
                'itemId' => $request->itemname
            ]);

            return redirect()
                ->route('StockCountingLine', ['journalid' => $request->JOURNALID])
                ->with('message', 'Stock Counting Successfully')
                ->with('isSuccess', true);
        } catch (ValidationException $e) {
            Log::warning('Validation failed', [
                'errors' => $e->errors()
            ]);
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
        } catch (\Exception $e) {
            Log::error('Error in store method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->with('message', 'Error storing data: ' . $e->getMessage())
                ->with('isError', true);
        }
    }

    public function show($storeids, $journalId)
    {
        try {
            $store = DB::table('rbostoretables')
                ->where('name', $storeids)
                ->value('name');
            
            $storeName = $store;
            $currentDate = Carbon::now('Asia/Manila')->toDateString();
            Log::info('User store', ['store' => $storeName]);

            $stockcountingtrans = DB::table('stockcountingtrans AS a')
                ->select(
                    'a.*', 
                    'b.*', 
                    'c.*', 
                    'st.posted',
                    DB::raw("DATE(a.TRANSDATE) as TRANSDATE")  
                )
                ->leftJoin('inventtables AS b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('rboinventtables AS c', 'b.itemid', '=', 'c.itemid')
                ->leftJoin('stockcountingtables as st', function($join) use ($storeName) {
                    $join->on('a.journalid', '=', 'st.journalid')
                        ->where('st.storeid', '=', $storeName);
                })
                ->where('a.journalid', $journalId)
                ->where('a.storename', $storeName)
                ->OrderBy('a.ADJUSTMENT', 'DESC')
                ->get();

            // Check if no data was found
            if ($stockcountingtrans->isEmpty()) {
                Log::info('No data found, redirecting to getbwproducts', [
                    'journalId' => $journalId,
                    'store' => $storeName
                ]);
                
                // Create a new request instance with all required parameters
                $request = new Request();
                $request->merge([
                    'JOURNALID' => $journalId,
                    'storeids' => $storeids
                ]);
                
                // Call getbwproducts method with all required parameters
                return $this->getbwproducts($request, $storeids, $journalId);
            }

            $isPosted = DB::table('stockcountingtables')
                ->where('journalid', $journalId)
                ->where('storeid', $storeName)
                ->value('posted') ?? 0;
                
            return response()->json([
                'journalid' => $journalId,
                'stockcountingtrans' => $stockcountingtrans,
                'isPosted' => $isPosted,
                'currentDate' => $currentDate, 
            ]);

        } catch (\Exception $e) {
            Log::error('Error in show method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getbwproducts(Request $request, $storeids, $journalId)
    {
        try {
            $request->validate([
                'JOURNALID' => 'required|string',
            ]);
            Log::info('Validation passed', ['journalId' => $request->JOURNALID]);

            $currentDateTime = Carbon::now('Asia/Manila')->toDateString();
            
            // Use the passed parameters instead of getting from request
            $journalid = $journalId ?? $request->JOURNALID;
            
            $store = DB::table('users')
                ->where('name', $storeids)
                ->value('name');
                
            $storerole = DB::table('users')
                ->where('name', $storeids)
                ->value('name');
            
            $storename = $store;
            $role = $storerole;
            
            Log::info('Context', [
                'store' => $storename,
                'role' => $role,
                'date' => $currentDateTime
            ]);

            $record = DB::table('stockcountingtrans')
                ->where('JOURNALID', $journalid)
                ->count();

            if ($record >= 1) {
                Log::warning('Items already generated', [
                    'journalId' => $journalid,
                    'recordCount' => $record
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Items have already been generated!',
                    'journalid' => $journalid,
                    'data' => null
                ], 400);
            }

            DB::beginTransaction();

            try {
                Log::info('Starting item generation transaction');
                
                if($storename == 'COMMUNITY'){
                    // Insert new records
                    DB::table('stockcountingtrans')
                        ->insertUsing(
                            ['JOURNALID', 'ITEMDEPARTMENT', 'TRANSDATE', 'ITEMID', 'ADJUSTMENT', 'RECEIVEDCOUNT', 'WASTECOUNT', 'COUNTED', 'STORENAME'],
                            function ($query) use ($journalid, $currentDateTime, $storename) {
                                $query->select(
                                    DB::raw("'{$journalid}' as JOURNALID"),
                                    'b.itemdepartment',
                                    DB::raw("'{$currentDateTime}' as TRANSDATE"),
                                    'a.itemid as ITEMID',
                                    DB::raw('0 as ADJUSTMENT'),
                                    DB::raw('0 as RECEIVEDCOUNT'),
                                    DB::raw('0 as WASTECOUNT'),
                                    DB::raw('0 as COUNTED'),
                                    DB::raw("'{$storename}' as STORENAME")
                                )
                                ->from('inventtables as a')
                                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                                ->where('b.activeondelivery', '1');
                            }
                        );
                
                }else{
                    DB::table('stockcountingtrans')
                    ->insertUsing(
                        ['JOURNALID', 'ITEMDEPARTMENT', 'TRANSDATE', 'ITEMID', 'ADJUSTMENT', 'RECEIVEDCOUNT', 'WASTECOUNT', 'COUNTED', 'STORENAME'],
                        function ($query) use ($journalid, $currentDateTime, $storename) {
                            $query->select(
                                DB::raw("'{$journalid}' as JOURNALID"),
                                'b.itemdepartment',
                                DB::raw("'{$currentDateTime}' as TRANSDATE"),
                                'a.itemid as ITEMID',
                                DB::raw('0 as ADJUSTMENT'),
                                DB::raw('0 as RECEIVEDCOUNT'),
                                DB::raw('0 as WASTECOUNT'),
                                DB::raw('0 as COUNTED'),
                                DB::raw("'{$storename}' as STORENAME")
                            )
                            ->from('inventtables as a')
                            ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                            ->where('a.itemname','not like', '%CLASS B%');
                        }
                    );
                }
                
                Log::info('Active inventory items inserted');
                
                $yesterday = Carbon::yesterday()->format('Y-m-d');
                Log::info('Fetching yesterday\'s journal records', ['date' => $yesterday]);

                /* $journalRecords = DB::connection('remote_db')
                    ->table('inventjournaltables as a')
                    ->leftJoin('inventjournaltrans as b', 'a.journalid', '=', 'b.journalid') 
                    ->whereDate('a.posteddatetime', $yesterday)
                    ->where('a.storeid', $storename)  
                    ->get();

                Log::info('Journal records retrieved', [
                    'count' => $journalRecords->count()
                ]);

                foreach ($journalRecords as $record) {
                    DB::table('stockcountingtrans')
                        ->where('ITEMID', $record->ITEMID) 
                        ->where('TRANSDATE', $currentDateTime)
                        ->update([
                            'ADJUSTMENT' => 0,
                            'RECEIVEDCOUNT' => 0,
                            'updated_at' => now()
                        ]);
                } */

                DB::commit();
                Log::info('Transaction committed successfully');

                // Get the newly generated items
                $generatedItems = DB::table('stockcountingtrans')
                    ->where('JOURNALID', $journalid)
                    ->where('STORENAME', $storename)
                    ->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Items generated successfully',
                    'journalid' => $journalid,
                    'data' => [
                        'items' => $generatedItems,
                        'total_count' => $generatedItems->count(),
                        'generation_date' => $currentDateTime
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error in transaction', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (ValidationException $e) {
            Log::warning('Validation failed', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getbwproducts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error generating items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function StockCountingLine($journalid)
    {
        Log::info('Accessing StockCountingLine', ['journalId' => $journalid]);
        try {
            $storeName = Auth::user()->storeid;
            $currentDate = Carbon::now('Asia/Manila')->toDateString();
            Log::info('Context', [
                'store' => $storeName,
                'date' => $currentDate
            ]);

            $stockcountingtrans = DB::table('stockcountingtrans AS a')
                ->select(
                    'a.*', 
                    'b.*', 
                    'c.*', 
                    'st.posted',
                    DB::raw("DATE(a.TRANSDATE) as TRANSDATE")  
                )
                ->leftJoin('inventtables AS b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('rboinventtables AS c', 'b.itemid', '=', 'c.itemid')
                ->leftJoin('stockcountingtables as st', function($join) use ($storeName) {
                    $join->on('a.journalid', '=', 'st.journalid')
                        ->where('st.storeid', '=', $storeName);
                })
                ->where('a.journalid', $journalid)
                ->where('a.storename', $storeName)
                ->OrderBy('a.ADJUSTMENT', 'DESC')
                ->get();

            Log::info('Stock counting data retrieved', [
                'count' => $stockcountingtrans->count()
            ]);

            $isPosted = DB::table('stockcountingtables')
                ->where('journalid', $journalid)
                ->where('storeid', $storeName)
                ->value('posted') ?? 0;

            return Inertia::render('StockCountingLine/index', [
                'journalid' => $journalid,
                'stockcountingtrans' => $stockcountingtrans,
                'isPosted' => $isPosted,
                'currentDate' => $currentDate, 
            ]);

        } catch (\Exception $e) {
            Log::error('Error in StockCountingLine', [
                'journalId' => $journalid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->with('message', 'Error loading stock counting: ' . $e->getMessage())
                ->with('isError', true);
        }
    }

    public function ViewOrders($journalid)
    {
        Log::info('Accessing ViewOrders', ['journalId' => $journalid]);
        try {
            $storename = Auth::user()->storeid;
            Log::info('User context', ['store' => $storename]);
            
            $currentDate = Carbon::now('Asia/Manila')->toDateString();

            $record = DB::table('stockcountingtables')
                ->where('journalid', $journalid)
                ->where('storeid', $storename)
                ->where('posted', 0)
                ->count();

            Log::info('Stock counting records found', ['count' => $record]);

            $stockcountingtrans = DB::table('stockcountingtrans as a')
                ->select('a.*', 'b.*', 'c.*')
                ->leftJoin('inventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
                ->where('a.journalid', $journalid)
                ->where('a.storename', $storename)
                ->where('a.counted', '!=', '0')
                ->get();

            Log::info('Stock counting transactions retrieved', [
                'count' => $stockcountingtrans->count()
            ]);

            return Inertia::render('StockCountingLine/index2', [
                'journalid' => $journalid,
                'stockcountingtrans' => $stockcountingtrans,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ViewOrders', [
                'journalId' => $journalid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->with('message', 'Error viewing orders: ' . $e->getMessage())
                ->with('isError', true);
        }
    }

    public function post(Request $request)
    {
        Log::info('Accessing post method', ['request' => $request->all()]);
        try {
            $currentDateTime = Carbon::now('Asia/Manila')->toDateString();
            $yesterday = Carbon::yesterday('Asia/Manila')->toDateString();
            $storename = Auth::user()->storeid;
            $journalid = $request->journalid;
            $role = Auth::user()->role;
            
            Log::info('Context', [
                'store' => $storename,
                'role' => $role,
                'date' => $currentDateTime,
                'journalId' => $journalid
            ]);
            
            DB::beginTransaction();
            Log::info('Transaction started');
            
            $affected = DB::table('stockcountingtables')
                ->whereDate(DB::raw('cast(posteddatetime as date)'), $currentDateTime)
                ->update(['posted' => '1']);

            DB::commit();
            Log::info('Transaction committed', ['affected_rows' => $affected]);

            return response()->json([
                'success' => true,
                'message' => 'Posted successfully',
                'affected' => $affected
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in post method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error posting: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateInventorySummaries($storename, $currentDateTime, $yesterday)
{
    Log::info("Starting inventory summaries update", [
        'storename' => $storename,
        'current_date' => $currentDateTime,
        'yesterday' => $yesterday
    ]);

    try {

        // Update throw_away
        Log::info("Updating throw_away for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET throw_away = (
                SELECT SUM(WASTECOUNT) 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                  AND WASTETYPE = 'Throw Away'
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
              AND EXISTS (
                SELECT 1 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                  AND WASTETYPE = 'Throw Away'
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
        ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);
        
        Log::info("Throw away update completed", ['affected_rows' => $affectedRows]);


        // Update early_molds
        Log::info("Updating early_molds for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET early_molds = (
                SELECT SUM(WASTECOUNT) 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                  AND WASTETYPE = 'Early Molds'
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
              AND EXISTS (
                SELECT 1 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                  AND WASTETYPE = 'Early Molds'
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
        ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);
        
        Log::info("Early Molds update completed", ['affected_rows' => $affectedRows]);


        // Update pull_out
        Log::info("Updating pull_out for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET pull_out = (
                SELECT SUM(WASTECOUNT) 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                  AND WASTETYPE = 'Pull Out'
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
              AND EXISTS (
                SELECT 1 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                  AND WASTETYPE = 'Pull Out'
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
        ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);
        
        Log::info("Pull Out update completed", ['affected_rows' => $affectedRows]);


        // Update rat_bites
        Log::info("Updating rat_bites for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET rat_bites = (
                SELECT SUM(WASTECOUNT) 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                  AND WASTETYPE = 'Rat Bites'
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
              AND EXISTS (
                SELECT 1 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                  AND WASTETYPE = 'Rat Bites'
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
        ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);
        
        Log::info("Rat Bites update completed", ['affected_rows' => $affectedRows]);


        // Update ant_bites
        Log::info("Updating rat_bites for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET ant_bites = (
                SELECT SUM(WASTECOUNT) 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                  AND WASTETYPE = 'Ant Bites'
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
              AND EXISTS (
                SELECT 1 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                  AND WASTETYPE = 'Ant Bites'
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
        ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);
        
        Log::info("Ant Bites update completed", ['affected_rows' => $affectedRows]);


        // Update received_delivery
        Log::info("Updating received_delivery for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET received_delivery = (
                SELECT SUM(RECEIVEDCOUNT) 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
              AND EXISTS (
                SELECT 1 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
            )
        ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);
        
        Log::info("Received delivery update completed", ['affected_rows' => $affectedRows]);

        // Update sales
        Log::info("Updating sales for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET sales = (
                SELECT COALESCE(SUM(b.qty), 0)
                FROM rbotransactiontables a 
                LEFT JOIN rbotransactionsalestrans b ON a.transactionid = b.transactionid 
                LEFT JOIN rboinventtables c ON b.itemid = c.itemid 
                WHERE a.store = ?
                AND CAST(a.createddate AS DATE) = ?
                AND b.itemgroup NOT LIKE '%PROMO%'
                AND c.itemid = inventory_summaries.itemid
            )
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
              AND inventory_summaries.itemid IN (
                SELECT DISTINCT c.itemid 
                FROM rbotransactiontables a 
                LEFT JOIN rbotransactionsalestrans b ON a.transactionid = b.transactionid 
                LEFT JOIN rboinventtables c ON b.itemid = c.itemid 
                WHERE a.store = ?
                AND CAST(a.createddate AS DATE) = ?
                AND b.itemgroup NOT LIKE '%PROMO%'
                AND c.itemid IS NOT NULL
            )
        ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);
        
        Log::info("Sales update completed", ['affected_rows' => $affectedRows]);

        // Update bundle_sales
        Log::info("Updating bundle_sales for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries invs
            JOIN (
                SELECT 
                    il.child_itemid,
                    il.quantity AS link_quantity,
                    COALESCE(SUM(rst.qty), 0) * il.quantity AS result
                FROM item_links il
                LEFT JOIN rbotransactionsalestrans rst 
                    ON rst.itemid = il.parent_itemid OR rst.itemid = il.child_itemid
                WHERE 
                    CAST(rst.createddate AS DATE) = ?
                    AND rst.store = ?
                    AND rst.itemgroup LIKE '%PROMO%'
                GROUP BY il.child_itemid, il.quantity
            ) AS bundle_data
            ON invs.itemid = bundle_data.child_itemid
               AND invs.storename = ?
            SET invs.bundle_sales = bundle_data.result
            WHERE CAST(invs.report_date AS DATE) = ?
        ", [$currentDateTime, $storename, $storename, $currentDateTime]);
        
        Log::info("Bundle sales update completed", ['affected_rows' => $affectedRows]);

        // Update beginning
        Log::info("Updating beginning inventory for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET beginning = (
                SELECT item_count 
                FROM inventory_summaries prev
                WHERE prev.ITEMID = inventory_summaries.ITEMID
                  AND prev.STORENAME = ?
                  AND CAST(prev.report_date AS DATE) = ?
            )
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
              AND EXISTS (
                SELECT 1 
                FROM inventory_summaries prev
                WHERE prev.ITEMID = inventory_summaries.ITEMID
                  AND prev.STORENAME = ?
                  AND CAST(prev.report_date AS DATE) = ?
            )
        ", [$storename, $yesterday, $currentDateTime, $storename, $storename, $yesterday]);
        
        Log::info("Beginning inventory update completed", ['affected_rows' => $affectedRows]);

        // Update ending inventory
        Log::info("Updating ending inventory for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET ending = CASE 
                WHEN beginning IS NOT NULL 
                     AND beginning != 0
                     AND COALESCE(received_delivery, 0) = 0
                     AND COALESCE(stock_transfer, 0) = 0
                     AND COALESCE(sales, 0) = 0
                     AND COALESCE(bundle_sales, 0) = 0
                     AND COALESCE(throw_away, 0) = 0
                     AND COALESCE(early_molds, 0) = 0
                     AND COALESCE(pull_out, 0) = 0
                     AND COALESCE(rat_bites, 0) = 0
                     AND COALESCE(ant_bites, 0) = 0
                THEN beginning
                ELSE COALESCE(beginning, 0) + COALESCE(received_delivery, 0) - COALESCE(stock_transfer, 0) - COALESCE(sales, 0) - COALESCE(bundle_sales, 0) - COALESCE(throw_away, 0) - COALESCE(early_molds, 0) - COALESCE(pull_out, 0) - COALESCE(rat_bites, 0) - COALESCE(ant_bites, 0)
            END
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
        ", [$currentDateTime, $storename]);
        
        Log::info("Ending inventory update completed", ['affected_rows' => $affectedRows]);

        // Update item_count with calculated ending inventory
        Log::info("Updating item_count with calculated ending inventory");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET item_count = CASE 
                WHEN beginning IS NOT NULL 
                     AND beginning != 0
                     AND COALESCE(received_delivery, 0) = 0
                     AND COALESCE(stock_transfer, 0) = 0
                     AND COALESCE(sales, 0) = 0
                     AND COALESCE(bundle_sales, 0) = 0
                     AND COALESCE(throw_away, 0) = 0
                     AND COALESCE(early_molds, 0) = 0
                     AND COALESCE(pull_out, 0) = 0
                     AND COALESCE(rat_bites, 0) = 0
                     AND COALESCE(ant_bites, 0) = 0
                THEN beginning
                ELSE 0
            END
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
        ", [$currentDateTime, $storename]);
        
        Log::info("Item count recalculation completed", ['affected_rows' => $affectedRows]);

        // Update item_count
        Log::info("Updating item_count for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET item_count = (
                SELECT COUNTED 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
                  AND COUNTED > 0
            )
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
              AND EXISTS (
                SELECT 1 
                FROM stockcountingtrans 
                WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                  AND STORENAME = ?
                  AND CAST(TRANSDATE AS DATE) = ?
                  AND COUNTED > 0
            )
        ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);
        
        Log::info("Item count update completed", ['affected_rows' => $affectedRows]);

        // Final item_count update with null safety
        Log::info("Performing final item_count update with null safety");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET item_count = CASE 
                WHEN beginning IS NOT NULL 
                     AND beginning != 0
                     AND COALESCE(received_delivery, 0) = 0
                     AND COALESCE(stock_transfer, 0) = 0
                     AND COALESCE(sales, 0) = 0
                     AND COALESCE(bundle_sales, 0) = 0
                     AND COALESCE(throw_away, 0) = 0
                     AND COALESCE(early_molds, 0) = 0
                     AND COALESCE(pull_out, 0) = 0
                     AND COALESCE(rat_bites, 0) = 0
                     AND COALESCE(ant_bites, 0) = 0
                THEN beginning 
                ELSE item_count 
            END
            WHERE CAST(report_date AS DATE) = ?
            AND storename = ?
        ", [$currentDateTime, $storename]);
        
        Log::info("Final item count update completed", ['affected_rows' => $affectedRows]);

        // Update variance
        Log::info("Updating variance for inventory summaries");
        $affectedRows = DB::statement("
            UPDATE inventory_summaries 
            SET variance = COALESCE(ending, 0) - COALESCE(item_count, 0)
            WHERE CAST(report_date AS DATE) = ?
              AND storename = ?
        ", [$currentDateTime, $storename]);
        
        Log::info("Variance update completed", ['affected_rows' => $affectedRows]);

        // Log summary of what was updated
        $summaryData = DB::select("
            SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN item_count IS NOT NULL THEN 1 ELSE 0 END) as records_with_count,
                SUM(CASE WHEN variance != 0 THEN 1 ELSE 0 END) as records_with_variance,
                SUM(CASE WHEN ending IS NOT NULL THEN 1 ELSE 0 END) as records_with_ending
            FROM inventory_summaries 
            WHERE CAST(report_date AS DATE) = ? AND storename = ?
        ", [$currentDateTime, $storename]);

        Log::info("Inventory summaries update completed successfully", [
            'summary' => $summaryData[0] ?? null,
            'storename' => $storename,
            'date' => $currentDateTime
        ]);

    } catch (\Exception $e) {
        Log::error("Error updating inventory summaries", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'storename' => $storename,
            'current_date' => $currentDateTime,
            'yesterday' => $yesterday
        ]);
        throw $e;
    }
}

public function postbatchline($itemid, $storeid, $journalid, $adjustment, $receivedcount, $transfercount, $wastecount, $wastetype, $counted)
{
    Log::info("Starting postbatchline operation", [
        'itemid' => $itemid,
        'storeid' => $storeid,
        'journalid' => $journalid,
        'adjustment' => $adjustment,
        'receivedcount' => $receivedcount,
        'transfercount' => $transfercount,
        'wastecount' => $wastecount,
        'wastetype' => $wastetype,
        'counted' => $counted
    ]);

    try {
        // Validate inputs
        if (empty($itemid) || empty($storeid) || empty($journalid)) {
            Log::error("Missing required parameters", [
                'itemid' => $itemid,
                'storeid' => $storeid,
                'journalid' => $journalid
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required parameters: itemid, storeid, or journalid'
            ], 400);
        }

        // Check if record exists before updating
        $existingRecord = stockcountingtrans::where([
            'ITEMID' => $itemid,
            'STORENAME' => $storeid,
            'JOURNALID' => $journalid
        ])
        ->whereDate('TRANSDATE', Carbon::today())
        ->first();

        if (!$existingRecord) {
            Log::warning("No existing record found for update", [
                'itemid' => $itemid,
                'storeid' => $storeid,
                'journalid' => $journalid,
                'date' => Carbon::today()->toDateString()
            ]);
        }

        Log::info("Updating stock counting transaction record");
        $updated = stockcountingtrans::where([
            'ITEMID' => $itemid,
            'STORENAME' => $storeid,
            'JOURNALID' => $journalid
        ])
        ->whereDate('TRANSDATE', Carbon::today())
        ->update([
            'ADJUSTMENT' => $adjustment,
            'COUNTED' => $counted, 
            'TRANSFERCOUNT' => $transfercount,
            'RECEIVEDCOUNT' => $receivedcount,
            'WASTECOUNT' => $wastecount,
            'WASTETYPE' => $wastetype,
            'updated_at' => Carbon::now()
        ]);

        Log::info("Stock counting transaction updated", [
            'records_updated' => $updated,
            'itemid' => $itemid
        ]);

        if ($updated === 0) {
            Log::warning("No records were updated", [
                'itemid' => $itemid,
                'storeid' => $storeid,
                'journalid' => $journalid
            ]);
        }

        // Fetch the updated record to return in response
        Log::info("Fetching updated stock counting record");
        $stockCount = stockcountingtrans::where([
            'ITEMID' => $itemid,
            'STORENAME' => $storeid,
            'JOURNALID' => $journalid
        ])
        ->whereDate('TRANSDATE', Carbon::today())
        ->first();

        if (!$stockCount) {
            Log::error("Updated record not found after update", [
                'itemid' => $itemid,
                'storeid' => $storeid,
                'journalid' => $journalid
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Record not found after update'
            ], 404);
        }

        Log::info("Successfully retrieved updated stock counting record", [
            'record_id' => $stockCount->id ?? null,
            'itemid' => $stockCount->ITEMID ?? null
        ]);

        // Prepare dates and store information
        $currentDateTime = Carbon::now('Asia/Manila')->toDateString();
        $yesterday = Carbon::yesterday('Asia/Manila')->toDateString();
        $storename = Auth::user()->storeid ?? $storeid;

        Log::info("Preparing to update inventory summaries", [
            'storename' => $storename,
            'current_date' => $currentDateTime,
            'yesterday' => $yesterday,
            'authenticated_user' => Auth::user()->id ?? 'N/A'
        ]);

        // Update inventory summaries
        $this->updateInventorySummaries($storename, $currentDateTime, $yesterday);

        Log::info("Postbatchline operation completed successfully", [
            'itemid' => $itemid,
            'storeid' => $storeid,
            'records_updated' => $updated
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Stock counting records updated successfully',
            'data' => $stockCount,
            'records_updated' => $updated
        ], 200);

    } catch (\Exception $e) {
        Log::error("Failed to update stock counting records", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'itemid' => $itemid,
            'storeid' => $storeid,
            'journalid' => $journalid,
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to update stock counting records',
            'error' => $e->getMessage()
        ], 500);
    }
}
    
    public function getstocktransfer(Request $request, $journalid)
    {
        try {
            $storename = Auth::user()->storeid;
            
            DB::beginTransaction();
            $journalRecords = DB::table('stock_transfers as a')
                ->leftJoin('stock_transfer_items as b', 'a.id', '=', 'b.stock_transfer_id')
                ->select('b.itemid', DB::raw('SUM(b.quantity) as qty'))
                ->whereDate('a.transfer_date', now())
                ->where('a.store', $storename)
                ->groupBy('b.itemid')
                ->get();

            Log::info('Stock transfer records retrieved', [
                'count' => $journalRecords->count()
            ]);

            foreach ($journalRecords as $record) {
                DB::table('stockcountingtrans')
                    ->where('ITEMID', $record->itemid)
                    ->where('JOURNALID', $journalid)
                    ->where('STORENAME', $storename)
                    ->whereDate('TRANSDATE', now())
                    ->update([
                        'TRANSFERCOUNT' => $record->qty,
                        'updated_at' => now()
                    ]);
            }

            DB::commit();
            Log::info('Transaction committed successfully');

            return redirect()
                ->route('StockCountingLine', ['journalid' => $journalid])
                ->with('message', 'Stock Transfer Updated Successfully')
                ->with('isSuccess', true);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in getstocktransfer', [
                'journalId' => $journalid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->with('message', 'Error occurred: ' . $e->getMessage())
                ->with('isError', true);
        }
    }
}
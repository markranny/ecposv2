<?php

namespace App\Http\Controllers;

use App\Models\stockcountingtables;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApisStockCountingController extends Controller
{
    public function index(Request $request, $storeids)
{
    DB::enableQueryLog(); 

    try {
        $currentDate = Carbon::now('Asia/Manila')->toDateString();
        
        $query = DB::table('stockcountingtables AS a')
            ->select(
                'a.journalid',
                'a.storeid',
                'a.description',
                DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS qty'),
                DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2)) * CAST(b.COUNTED AS UNSIGNED)) AS amount'),
                'a.posted',
                'a.updated_at',
                'a.journaltype',
                'a.createddatetime'
            )
            ->leftJoin('stockcountingtrans AS b', 'b.JOURNALID', '=', 'a.journalid')
            ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
            ->where('a.posted', '!=', '1')
            ->whereDate('a.createddatetime', '=', $currentDate);

        if ($storeids !== "HQ2") {
            $query->where('a.storeid', '=', $storeids);
        }

        $stockCounting = $query
            ->groupBy('a.journalid', 'a.storeid', 'a.description', 'a.posted', 
                     'a.updated_at', 'a.journaltype', 'a.createddatetime')
            ->orderBy('a.createddatetime', 'DESC')
            ->get();

        if ($stockCounting->isEmpty()) {
            Log::info('No unposted stock counting records found, checking for posted records', ['storeids' => $storeids]);

            // Check if there are any posted records for the current date
            $postedRecordsQuery = DB::table('stockcountingtables')
                ->where('posted', '=', '1')
                ->whereDate('createddatetime', '=', $currentDate);

            if ($storeids !== "HQ2") {
                $postedRecordsQuery->where('storeid', '=', $storeids);
            }

            $hasPostedRecords = $postedRecordsQuery->exists();

            if ($hasPostedRecords) {
                Log::info('Posted stock counting records already exist for current date', [
                    'storeids' => $storeids,
                    'date' => $currentDate
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Stock counting already completed for today'
                ]);
            }

            // If no posted records exist, create new record
            DB::beginTransaction();
            try {
                $currentDateTime = Carbon::now('Asia/Manila');

                $stocknextrec = DB::table('nubersequencevalues')
                    ->where('storeid', $storeids)
                    ->lockForUpdate()
                    ->value('stocknextrec');

                $stocknextrec = $stocknextrec !== null ? (int)$stocknextrec + 1 : 1;

                DB::table('nubersequencevalues')
                    ->where('STOREID', $storeids)
                    ->update(['stocknextrec' => $stocknextrec]);

                $journalId = $storeids . str_pad($stocknextrec, 8, '0', STR_PAD_LEFT);

                DB::table('stockcountingtables')->insert([
                    'JOURNALID' => $stocknextrec,
                    'STOREID' => $storeids,
                    'DESCRIPTION' => 'BATCH' . $journalId,
                    'POSTED' => 0,
                    'POSTEDDATETIME' => $currentDateTime->format('Y-m-d H:i:s'),
                    'JOURNALTYPE' => 1,
                    'DELETEPOSTEDLINES' => 0,
                    'CREATEDDATETIME' => $currentDateTime->format('Y-m-d H:i:s')
                ]);

                // Check if inventory summaries exist for this store and date
                $existingSummaries = DB::table('inventory_summaries')
                    ->where('storename', $storeids)
                    ->whereDate('report_date', $currentDate)
                    ->exists();

                // If no existing summaries, insert new ones
                if (!$existingSummaries) {
                    DB::table('inventory_summaries')
                        ->insertUsing(
                            ['itemid', 'itemname', 'storename', 'report_date'],
                            DB::table('inventtables')
                                ->select('itemid', 'itemname', DB::raw("'{$storeids}' as storename"), DB::raw("'{$currentDate}' as report_date"))
                        );
                }

                DB::commit();

                // Re-run the query to get the newly created record
                $stockCounting = $query
                    ->groupBy('a.journalid', 'a.storeid', 'a.description', 'a.posted', 
                             'a.updated_at', 'a.journaltype', 'a.createddatetime')
                    ->orderBy('a.createddatetime', 'DESC')
                    ->get();

                Log::info('New stock counting record created', [
                    'journalid' => $stocknextrec,
                    'storeids' => $storeids
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Detailed error in transaction: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                throw $e;
            }
        } else {
            // Check and insert missing items for both stockcountingtrans and inventory_summaries
            Log::info('Stock counting records found, checking for missing items', ['storeids' => $storeids]);
            
            foreach ($stockCounting as $stockCountRecord) {
                $this->insertMissingItemsToStockCountingTrans($stockCountRecord->journalid, $storeids, $currentDate);
                $this->insertMissingItemsToInventorySummaries($storeids, $currentDate);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $stockCounting
        ]);

    } catch (Exception $e) {
        Log::error('Error in stock counting index', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'sql' => DB::getQueryLog()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error loading stock counting data: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Insert missing items into stockcountingtrans table
 * 
 * @param int $journalId
 * @param string $storeId
 * @param string $currentDate
 * @return void
 */
private function insertMissingItemsToStockCountingTrans($journalId, $storeId, $currentDate)
{
    try {
        Log::info('Checking for missing items in stockcountingtrans', [
            'journalId' => $journalId,
            'storeId' => $storeId,
            'date' => $currentDate
        ]);

        // Check if stockcountingtrans has any records for this journal
        $existingRecordsCount = DB::table('stockcountingtrans')
            ->where('JOURNALID', $journalId)
            ->where('STORENAME', $storeId)
            ->whereDate('TRANSDATE', $currentDate)
            ->count();

        if ($existingRecordsCount === 0) {
            Log::info('No existing stockcountingtrans records found, skipping missing items check');
            return;
        }

        Log::info('Existing stockcountingtrans records found', ['count' => $existingRecordsCount]);

        // Get items that should be in stockcountingtrans but are missing
        if ($storeId == 'COMMUNITY') {
            // For COMMUNITY store, get items where activeondelivery = 1
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('stockcountingtrans as st', function($join) use ($journalId, $storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'st.ITEMID')
                         ->where('st.JOURNALID', '=', $journalId)
                         ->where('st.STORENAME', '=', $storeId)
                         ->whereDate('st.TRANSDATE', '=', $currentDate);
                })
                ->whereNull('st.ITEMID') // Items not in stockcountingtrans
                ->where('b.activeondelivery', '1')
                ->select('a.itemid', 'b.itemdepartment')
                ->get();
        } else {
            // For other stores, get items where itemname not like '%CLASS B%'
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('stockcountingtrans as st', function($join) use ($journalId, $storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'st.ITEMID')
                         ->where('st.JOURNALID', '=', $journalId)
                         ->where('st.STORENAME', '=', $storeId)
                         ->whereDate('st.TRANSDATE', '=', $currentDate);
                })
                ->whereNull('st.ITEMID') // Items not in stockcountingtrans
                ->where('a.itemname', 'not like', '%CLASS B%')
                ->select('a.itemid', 'b.itemdepartment')
                ->get();
        }

        if ($missingItems->count() > 0) {
            Log::info('Missing items found, inserting into stockcountingtrans', [
                'count' => $missingItems->count(),
                'journalId' => $journalId,
                'storeId' => $storeId
            ]);

            // Prepare data for bulk insert
            $insertData = [];
            foreach ($missingItems as $item) {
                $insertData[] = [
                    'JOURNALID' => $journalId,
                    'ITEMDEPARTMENT' => $item->itemdepartment,
                    'TRANSDATE' => $currentDate,
                    'ITEMID' => $item->itemid,
                    'ADJUSTMENT' => 0,
                    'RECEIVEDCOUNT' => 0,
                    'WASTECOUNT' => 0,
                    'COUNTED' => 0,
                    'STORENAME' => $storeId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Insert missing items in chunks to avoid memory issues
            $chunks = array_chunk($insertData, 100);
            foreach ($chunks as $chunk) {
                DB::table('stockcountingtrans')->insert($chunk);
            }

            Log::info('Missing items successfully inserted into stockcountingtrans', [
                'inserted_count' => count($insertData),
                'journalId' => $journalId,
                'storeId' => $storeId
            ]);
        } else {
            Log::info('No missing items found for stockcountingtrans', [
                'journalId' => $journalId,
                'storeId' => $storeId
            ]);
        }

    } catch (\Exception $e) {
        Log::error('Error inserting missing items to stockcountingtrans', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'journalId' => $journalId,
            'storeId' => $storeId
        ]);
        // Don't throw the exception to avoid breaking the main flow
    }
}

/**
 * Insert missing items into inventory_summaries table
 * 
 * @param string $storeId
 * @param string $currentDate
 * @return void
 */
private function insertMissingItemsToInventorySummaries($storeId, $currentDate)
{
    try {
        Log::info('Checking for missing items in inventory_summaries', [
            'storeId' => $storeId,
            'date' => $currentDate
        ]);

        // Check if inventory_summaries has any records for this store and date
        $existingRecordsCount = DB::table('inventory_summaries')
            ->where('storename', $storeId)
            ->whereDate('report_date', $currentDate)
            ->count();

        if ($existingRecordsCount === 0) {
            Log::info('No existing inventory_summaries records found, skipping missing items check');
            return;
        }

        Log::info('Existing inventory_summaries records found', ['count' => $existingRecordsCount]);

        // Get items that should be in inventory_summaries but are missing
        if ($storeId == 'COMMUNITY') {
            // For COMMUNITY store, get items where activeondelivery = 1
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('inventory_summaries as inv', function($join) use ($storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'inv.itemid')
                         ->where('inv.storename', '=', $storeId)
                         ->whereDate('inv.report_date', '=', $currentDate);
                })
                ->whereNull('inv.itemid') // Items not in inventory_summaries
                ->where('b.activeondelivery', '1')
                ->select('a.itemid', 'a.itemname')
                ->get();
        } else {
            // For other stores, get items where itemname not like '%CLASS B%'
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('inventory_summaries as inv', function($join) use ($storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'inv.itemid')
                         ->where('inv.storename', '=', $storeId)
                         ->whereDate('inv.report_date', '=', $currentDate);
                })
                ->whereNull('inv.itemid') // Items not in inventory_summaries
                ->where('a.itemname', 'not like', '%CLASS B%')
                ->select('a.itemid', 'a.itemname')
                ->get();
        }

        if ($missingItems->count() > 0) {
            Log::info('Missing items found, inserting into inventory_summaries', [
                'count' => $missingItems->count(),
                'storeId' => $storeId
            ]);

            // Prepare data for bulk insert
            $insertData = [];
            foreach ($missingItems as $item) {
                $insertData[] = [
                    'itemid' => $item->itemid,
                    'itemname' => $item->itemname,
                    'storename' => $storeId,
                    'beginning' => 0.00,
                    'received_delivery' => 0.00,
                    'stock_transfer' => 0.00,
                    'sales' => 0.00,
                    'bundle_sales' => 0.00,
                    'throw_away' => 0.00,
                    'early_molds' => 0.00,
                    'pull_out' => 0.00,
                    'rat_bites' => 0.00,
                    'ant_bites' => 0.00,
                    'item_count' => 0.00,
                    'ending' => 0.00,
                    'variance' => 0.00,
                    'report_date' => $currentDate,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Insert missing items in chunks to avoid memory issues
            $chunks = array_chunk($insertData, 100);
            foreach ($chunks as $chunk) {
                DB::table('inventory_summaries')->insert($chunk);
            }

            Log::info('Missing items successfully inserted into inventory_summaries', [
                'inserted_count' => count($insertData),
                'storeId' => $storeId
            ]);
        } else {
            Log::info('No missing items found for inventory_summaries', [
                'storeId' => $storeId
            ]);
        }

    } catch (\Exception $e) {
        Log::error('Error inserting missing items to inventory_summaries', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'storeId' => $storeId
        ]);
        // Don't throw the exception to avoid breaking the main flow
    }
}

    public function update(Request $request, $storeids, $posted, $journalid)
    {
        try {
            \Log::info('Starting stock counting update', [
                'storeids' => $storeids,
                'posted' => $posted,
                'journalid' => $journalid
            ]);
            
            // Let's try direct update first
            $affected = DB::table('stockcountingtables')
                ->where('JOURNALID', $journalid)
                ->where('STOREID', $storeids)
                ->update([
                    'POSTED' => $posted,
                    'updated_at' => now()
                ]);
                
            if ($affected === 0) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }
            
            // Fetch the updated record
            $stockCounting = stockcountingtables::where('JOURNALID', $journalid)
                ->where('STOREID', $storeids)
                ->first();
            
            \Log::info('Stock counting record updated', [
                'journalid' => $journalid,
                'posted' => $posted,
                'affected_rows' => $affected
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Stock counting record updated successfully',
                'data' => $stockCounting
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Stock counting record not found', [
                'journalid' => $journalid,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Stock counting record not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error updating stock counting record', [
                'journalid' => $journalid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the record'
            ], 500);
        }
    }   
}
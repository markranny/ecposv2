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
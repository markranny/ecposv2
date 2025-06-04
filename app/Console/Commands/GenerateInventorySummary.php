<?php

namespace App\Console\Commands;

use App\Models\InventorySummary;
use App\Models\stockcountingtrans;
use App\Models\StockTransferItem;
use App\Models\rbotransactionsalestrans;
use App\Models\StockTransfer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class GenerateInventorySummary extends Command
{
    protected $signature = 'inventory:generate-summary 
                            {date? : The date to generate summary for (YYYY-MM-DD)} 
                            {--store= : Optional store ID to process only a specific store}
                            {--force : Force regeneration of existing summaries}';
    protected $description = 'Generate inventory summary for reporting purposes';

    // Store mapping cache
    protected $storeIdToNameMap = [];

    public function handle()
    {
        $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : Carbon::yesterday();
        $reportDate = $date->format('Y-m-d');
        $beginningDate = Carbon::parse($reportDate)->subDay()->format('Y-m-d');
        $storeFilter = $this->option('store');
        $force = $this->option('force');

        $this->info("Generating inventory summary for date: {$reportDate}");
        if ($storeFilter) {
            $this->info("Filtering for store: {$storeFilter}");
        }
        if ($force) {
            $this->info("Force generation enabled");
        }

        try {
            // Load store ID to name mapping
            $this->loadStoreMapping();
            
            // Get stores to process
            $stores = $storeFilter ? [$storeFilter] : $this->getAllStoreIds();
            $this->info("Retrieved " . count($stores) . " stores for processing");
            
            // Get all items for processing
            $allItems = $this->getAllItems();
            $this->info("Found " . count($allItems) . " items to process");
            
            $processedCount = 0;
            $skippedCount = 0;
            $totalCount = count($allItems) * count($stores);
            
            $this->output->progressStart($totalCount);
            
            // Process each item for each store
            foreach ($allItems as $item) {
                foreach ($stores as $storeId) {
                    $this->output->progressAdvance();
                    
                    // Skip if store filter is applied and doesn't match
                    if ($storeFilter && $storeFilter !== $storeId) {
                        continue;
                    }
                    
                    // Get the store name from the mapping
                    $storeName = $this->getStoreNameFromId($storeId);
                    
                    if (empty($storeName)) {
                        $this->error("No store name found for ID: $storeId, skipping");
                        $skippedCount++;
                        continue;
                    }
                    
                    // Check if we should process this item (if force is disabled and record exists)
                    if (!$force) {
                        $existingRecord = InventorySummary::where([
                            'itemid' => $item->itemid,
                            'storename' => $storeName,
                            'report_date' => $reportDate
                        ])->first();
                        
                        if ($existingRecord) {
                            $this->line("Skipping existing record for item {$item->itemid} at store {$storeName}");
                            $skippedCount++;
                            continue;
                        }
                    }
                    
                    // Get actual inventory data
                    $beginning = $this->getBeginningInventory($item->itemid, $storeId, $beginningDate);
                    $receivedDelivery = $this->getReceivedInventory($item->itemid, $storeId, $reportDate);
                    $stockTransfer = $this->getStockTransfers($item->itemid, $storeId, $reportDate);
                    $sales = $this->getSales($item->itemid, $storeId, $reportDate);
                    $bundleSales = $this->getBundleSales($item->itemid, $storeId, $reportDate);
                    $wasteMetrics = $this->getWasteMetrics($item->itemid, $storeId, $reportDate);
                    $itemCount = $this->getCurrentItemCount($item->itemid, $storeId, $reportDate);
                    
                    // Calculate ending and variance
                    $totalWaste = $wasteMetrics['throw_away'] + $wasteMetrics['early_molds'] + 
                                  $wasteMetrics['pull_out'] + $wasteMetrics['rat_bites'] + 
                                  $wasteMetrics['ant_bites'];
                    
                    $ending = $beginning + $receivedDelivery + $stockTransfer - $sales - $bundleSales - $totalWaste;
                    $variance = $itemCount - $ending;

                    // Skip if there's no activity and item count is zero (reduces noise)
                    if (!$force && 
                        $beginning == 0 && 
                        $receivedDelivery == 0 && 
                        $stockTransfer == 0 && 
                        $sales == 0 && 
                        $bundleSales == 0 && 
                        $totalWaste == 0 && 
                        $itemCount == 0) {
                        $skippedCount++;
                        continue;
                    }

                    // Create inventory summary record with store NAME
                    $this->createInventorySummaryRecord(
                        $item->itemid,
                        $item->itemname,
                        $storeName, // Using store NAME here
                        $reportDate,
                        $beginning,
                        $receivedDelivery,
                        $stockTransfer,
                        $sales,
                        $bundleSales,
                        $wasteMetrics,
                        $itemCount,
                        $ending,
                        $variance
                    );
                    
                    $processedCount++;
                    
                    // If we've created at least one record per store, show success
                    if ($processedCount % 10 == 0) {
                        $this->info("Created $processedCount records so far...");
                    }
                }
            }

            $this->output->progressFinish();
            $this->info("Processed {$processedCount} items for date {$reportDate}");
            $this->info("Skipped {$skippedCount} items with no significant activity");

        } catch (\Exception $e) {
            Log::error('Inventory Summary Generation Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error("Error generating inventory summary: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    /**
     * Get beginning inventory from the previous day's item count
     */
    private function getBeginningInventory($itemId, $storeId, $beginningDate)
    {
        try {
            $storeName = $this->getStoreNameFromId($storeId);
            $previousSummary = InventorySummary::where([
                'itemid' => $itemId,
                'storename' => $storeName,
                'report_date' => $beginningDate
            ])->first();
            
            if ($previousSummary) {
                return (float) $previousSummary->item_count;
            }
            
            $previousCount = stockcountingtrans::where([
                'ITEMID' => $itemId,
                'STORENAME' => $storeName,
            ])
            ->whereDate('TRANSDATE', $beginningDate)
            ->orderBy('created_at', 'desc')
            ->value('COUNTED');
            
            return (float) ($previousCount ?? 0);
        } catch (\Exception $e) {
            $this->error("Error getting beginning inventory: " . $e->getMessage());
            Log::error("Error getting beginning inventory for item $itemId, store $storeId", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Get received inventory from stockcountingtrans.receivedcount
     */
    private function getReceivedInventory($itemId, $storeId, $reportDate)
    {
        try {
            $storeName = $this->getStoreNameFromId($storeId);
            
            $receivedCount = stockcountingtrans::where([
                'ITEMID' => $itemId,
                'STORENAME' => $storeName
            ])
            ->whereDate('TRANSDATE', $reportDate)
            ->sum('RECEIVEDCOUNT');
            
            return (float) ($receivedCount ?? 0);
        } catch (\Exception $e) {
            $this->error("Error getting received inventory: " . $e->getMessage());
            Log::error("Error getting received inventory for item $itemId, store $storeId", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Get stock transfers (positive for incoming, negative for outgoing)
     */
    private function getStockTransfers($itemId, $storeId, $reportDate)
    {
        try {
            // Get incoming transfers (positive)
            $incomingTransfers = DB::table('stock_transfer_items as sti')
                ->join('stock_transfers as st', 'sti.stock_transfer_id', '=', 'st.id')
                ->where('sti.itemid', $itemId)
                ->where('st.to_store_id', $storeId)
                ->where('st.status', 'completed')
                ->whereDate('st.transfer_date', $reportDate)
                ->sum('sti.quantity');
                
            // Get outgoing transfers (negative)
            $outgoingTransfers = DB::table('stock_transfer_items as sti')
                ->join('stock_transfers as st', 'sti.stock_transfer_id', '=', 'st.id')
                ->where('sti.itemid', $itemId)
                ->where('st.from_store_id', $storeId)
                ->where('st.status', 'completed')
                ->whereDate('st.transfer_date', $reportDate)
                ->sum('sti.quantity');
                
            // Net transfers (incoming - outgoing)
            return (float) ($incomingTransfers - $outgoingTransfers);
        } catch (\Exception $e) {
            $this->error("Error getting stock transfers: " . $e->getMessage());
            Log::error("Error getting stock transfers for item $itemId, store $storeId", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Get direct sales from rbotransactionsalestrans
     */
    private function getSales($itemId, $storeId, $reportDate)
{
    try {
        $storeName = $this->getStoreNameFromId($storeId);
        
        $sales = DB::table('rbotransactiontables as a')
            ->leftJoin('rbotransactionsalestrans as b', 'a.transactionid', '=', 'b.transactionid')
            ->where('a.store', $storeName)
            ->whereDate('a.createddate', $reportDate)
            ->where('b.itemid', $itemId)
            ->sum('b.qty');

        return (float) ($sales ?? 0);

    } catch (\Exception $e) {
        $this->error("Error getting sales: " . $e->getMessage());
        Log::error("Error getting sales for item $itemId, store $storeId", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return 0;
    }
}

    /**
     * Get bundle sales (if implemented in your system)
     */
    private function getBundleSales($itemId, $storeId, $reportDate)
    {
        // Implement your bundle sales logic here if available
        // This is a placeholder based on the field in your model
        return 0;
    }

    /**
     * Get waste metrics from stockcountingtrans
     */
    private function getWasteMetrics($itemId, $storeId, $reportDate)
    {
        $storeName = $this->getStoreNameFromId($storeId);
        $wasteMetrics = [
            'throw_away' => 0,
            'early_molds' => 0,
            'pull_out' => 0,
            'rat_bites' => 0,
            'ant_bites' => 0
        ];
        
        try {
            // Query for waste by different wastetype
            $wasteRecords = stockcountingtrans::where([
                'ITEMID' => $itemId,
                'STORENAME' => $storeName
            ])
            ->whereDate('TRANSDATE', $reportDate)
            ->whereNotNull('WASTETYPE')
            ->whereNotNull('WASTECOUNT')
            ->select('WASTETYPE', DB::raw('SUM(WASTECOUNT) as total_waste'))
            ->groupBy('WASTETYPE')
            ->get();
            
            foreach ($wasteRecords as $record) {
                $wasteType = strtolower(str_replace(' ', '_', $record->WASTETYPE));
                
                if (array_key_exists($wasteType, $wasteMetrics)) {
                    $wasteMetrics[$wasteType] = (float) $record->total_waste;
                } else if ($wasteType == 'throw_away' || $wasteType == 'throw away') {
                    $wasteMetrics['throw_away'] = (float) $record->total_waste;
                } else if ($wasteType == 'early_molds' || $wasteType == 'early molds') {
                    $wasteMetrics['early_molds'] = (float) $record->total_waste;
                } else if ($wasteType == 'pull_out' || $wasteType == 'pull out') {
                    $wasteMetrics['pull_out'] = (float) $record->total_waste;
                } else if ($wasteType == 'rat_bites' || $wasteType == 'rat bites') {
                    $wasteMetrics['rat_bites'] = (float) $record->total_waste;
                } else if ($wasteType == 'ant_bites' || $wasteType == 'ant bites') {
                    $wasteMetrics['ant_bites'] = (float) $record->total_waste;
                }
            }
            
            return $wasteMetrics;
        } catch (\Exception $e) {
            $this->error("Error getting waste metrics: " . $e->getMessage());
            Log::error("Error getting waste metrics for item $itemId, store $storeId", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $wasteMetrics;
        }
    }

    /**
     * Get current item count from stockcountingtrans
     */
    private function getCurrentItemCount($itemId, $storeId, $reportDate)
    {
        try {
            $storeName = $this->getStoreNameFromId($storeId);
            
            $itemCount = stockcountingtrans::where([
                'ITEMID' => $itemId,
                'STORENAME' => $storeName
            ])
            ->whereDate('TRANSDATE', $reportDate)
            ->orderBy('created_at', 'desc')
            ->value('COUNTED');
            
            return (float) ($itemCount ?? 0);
        } catch (\Exception $e) {
            $this->error("Error getting current item count: " . $e->getMessage());
            Log::error("Error getting current item count for item $itemId, store $storeId", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Load store ID to name mapping from rbostoretables
     */
    private function loadStoreMapping()
    {
        try {
            $stores = DB::table('rbostoretables')
                ->select(['STOREID', 'NAME'])
                ->get();
                
            foreach ($stores as $store) {
                $this->storeIdToNameMap[$store->STOREID] = $store->NAME;
            }
            
            $this->info("Loaded " . count($this->storeIdToNameMap) . " store ID-to-name mappings");
        } catch (\Exception $e) {
            $this->error("Error loading store mapping: " . $e->getMessage());
            // Create a fallback mapping for testing
            $this->storeIdToNameMap = [
                'BW0001' => 'BANACOM',
                'BW0002' => 'MARKETVIEW',
                'BW0003' => 'GAPAN',
                'BW0004' => 'LAPAZ',
                'BW0005' => 'PURA'
            ];
        }
    }
    
    /**
     * Get store name from store ID using the mapping
     */
    private function getStoreNameFromId($storeId)
    {
        if (isset($this->storeIdToNameMap[$storeId])) {
            return $this->storeIdToNameMap[$storeId];
        }
        
        // If not in cache, try to look it up directly
        try {
            $storeName = DB::table('rbostoretables')
                ->where('STOREID', $storeId)
                ->value('NAME');
                
            if ($storeName) {
                $this->storeIdToNameMap[$storeId] = $storeName;
                return $storeName;
            }
        } catch (\Exception $e) {
            $this->error("Error looking up store ID $storeId: " . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get all store IDs for processing
     */
    private function getAllStoreIds()
    {
        try {
            if (Schema::hasColumn('rbostoretables', 'ACTIVE')) {
                return DB::table('rbostoretables')
                    ->where('ACTIVE', 1)
                    ->pluck('STOREID')
                    ->toArray();
            } else {
                return DB::table('rbostoretables')
                    ->pluck('STOREID')
                    ->toArray();
            }
        } catch (\Exception $e) {
            $this->error("Error getting store IDs: " . $e->getMessage());
            return array_keys($this->storeIdToNameMap); // Use the keys from our mapping as fallback
        }
    }

    /**
     * Get all items for processing
     */
    private function getAllItems()
    {
        try {
            return DB::table('inventtables')
                ->select('itemid', 'itemname')
                ->where('itemtype', '!=', 2) // Exclude linked items
                ->get();
        } catch (\Exception $e) {
            $this->error("Error getting items: " . $e->getMessage());
            
            // Create sample items as fallback
            $items = [];
            for ($i = 1; $i <= 5; $i++) {
                $item = new \stdClass();
                $item->itemid = "TEST" . $i;
                $item->itemname = "Test Item " . $i;
                $items[] = $item;
            }
            return collect($items);
        }
    }

    /**
     * Create an inventory summary record with store NAME instead of ID
     */
    private function createInventorySummaryRecord($itemId, $itemName, $storeName, $reportDate, $beginning, $receivedDelivery, 
                                          $stockTransfer, $sales, $bundleSales, $wasteMetrics, $itemCount, $ending, $variance)
    {
        try {
            // Check if we have NaN or infinite values and replace them with 0
            $beginning = is_nan($beginning) || is_infinite($beginning) ? 0 : $beginning;
            $receivedDelivery = is_nan($receivedDelivery) || is_infinite($receivedDelivery) ? 0 : $receivedDelivery;
            $stockTransfer = is_nan($stockTransfer) || is_infinite($stockTransfer) ? 0 : $stockTransfer;
            $sales = is_nan($sales) || is_infinite($sales) ? 0 : $sales;
            $bundleSales = is_nan($bundleSales) || is_infinite($bundleSales) ? 0 : $bundleSales;
            $itemCount = is_nan($itemCount) || is_infinite($itemCount) ? 0 : $itemCount;
            $ending = is_nan($ending) || is_infinite($ending) ? 0 : $ending;
            $variance = is_nan($variance) || is_infinite($variance) ? 0 : $variance;
            
            foreach ($wasteMetrics as $key => $value) {
                $wasteMetrics[$key] = is_nan($value) || is_infinite($value) ? 0 : $value;
            }
            
            // First try the model's updateOrCreate method
            $summary = InventorySummary::updateOrCreate(
                [
                    'itemid' => $itemId,
                    'storename' => $storeName, 
                    'report_date' => $reportDate
                ],
                [
                    'itemname' => $itemName,
                    'beginning' => (float)$beginning,
                    'received_delivery' => (float)$receivedDelivery,
                    'stock_transfer' => (float)$stockTransfer,
                    'sales' => (float)$sales,
                    'bundle_sales' => (float)$bundleSales,
                    'throw_away' => (float)$wasteMetrics['throw_away'],
                    'early_molds' => (float)$wasteMetrics['early_molds'],
                    'pull_out' => (float)$wasteMetrics['pull_out'],
                    'rat_bites' => (float)$wasteMetrics['rat_bites'],
                    'ant_bites' => (float)$wasteMetrics['ant_bites'],
                    'item_count' => (float)$itemCount,
                    'ending' => (float)$ending,
                    'variance' => (float)$variance
                ]
            );
            
            if ($summary) {
                return true;
            }
            
            // If Eloquent method fails, try direct DB method
            DB::table('inventory_summaries')->updateOrInsert(
                [
                    'itemid' => $itemId,
                    'storename' => $storeName, // Using store NAME here
                    'report_date' => $reportDate
                ],
                [
                    'itemname' => $itemName,
                    'beginning' => (float)$beginning,
                    'received_delivery' => (float)$receivedDelivery,
                    'stock_transfer' => (float)$stockTransfer,
                    'sales' => (float)$sales,
                    'bundle_sales' => (float)$bundleSales,
                    'throw_away' => (float)$wasteMetrics['throw_away'],
                    'early_molds' => (float)$wasteMetrics['early_molds'],
                    'pull_out' => (float)$wasteMetrics['pull_out'],
                    'rat_bites' => (float)$wasteMetrics['rat_bites'],
                    'ant_bites' => (float)$wasteMetrics['ant_bites'],
                    'item_count' => (float)$itemCount,
                    'ending' => (float)$ending,
                    'variance' => (float)$variance,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            
            return true;
            
        } catch (\Exception $e) {
            $this->error("Error creating record: " . $e->getMessage());
            Log::error('Error creating inventory summary record: ' . $e->getMessage(), [
                'itemId' => $itemId,
                'storeName' => $storeName,
                'reportDate' => $reportDate,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
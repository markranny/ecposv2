<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\inventtrans;
use App\Models\inventtables;
use App\Models\inventtablemodules;
use App\Models\rbostoretables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class StockTransferController extends Controller
{
    /**
     * Display a listing of stock transfers
     *
     * @return \Inertia\Response
     */
    public function index()
{
    try {
        $storecode = Auth::user()->storeid; 

        $currentStore = DB::table('users as a')
        ->leftJoin('rbostoretables as b', 'a.storeid', '=', 'b.NAME')
        ->where('a.storeid', $storecode)
        ->value('b.storeid'); 
        
        if (!$currentStore) {
            return back()->with('error', 'No store assigned to current user.');
        }

        // Add logging to debug the store ID
        \Log::info('Current store ID:', ['storeid' => $currentStore]);

        $transfers = StockTransfer::with([
                'from_store:STOREID,NAME',
                'to_store:STOREID,NAME',
                'items'
            ])
            ->where(function($query) use ($currentStore) {
                $query->where('from_store_id', $currentStore)
                    ->orWhere('to_store_id', $currentStore);
            })
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->get();

        // Log the transfers query result
        \Log::info('Transfers query result:', ['count' => $transfers->count()]);

        $pendingTransfers = $transfers->where('status', 'request');
        
        $stores = rbostoretables::select(['STOREID', 'NAME'])
            ->where('STOREID', '!=', $currentStore)
            ->get();

        // Log stores data
        \Log::info('Available stores:', ['count' => $stores->count()]);

        $items = $this->getFormattedItems();

        // Log items data
        \Log::info('Available items:', ['count' => count($items)]);

        return Inertia::render('StockTransfer/list', [
            'transfers' => $transfers,
            'pendingTransfers' => $pendingTransfers,
            'stores' => $stores,
            'items' => $items,
            'currentStore' => (string) $currentStore,
        ]);
    } catch (\Exception $e) {
        \Log::error('Error in stock transfer index:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Error loading stock transfers: ' . $e->getMessage());
    }
}

    /**
     * Get formatted items with their modules
     *
     * @return \Illuminate\Support\Collection
     */
    private function getFormattedItems()
    {
        return inventtables::with(['modules' => function($query) {
                $query->select('itemid', 'unitid', 'priceincltax');
            }])
            ->get()
            ->map(function ($item) {
                $module = $item->modules->first();
                return [
                    'itemid' => $item->itemid,
                    'itemname' => $item->itemname,
                    'unitid' => $module ? $module->unitid : null,
                    'price' => $module ? $module->priceincltax : 0
                ];
            });
    }

    /**
     * Store a new stock transfer
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $currentStore = Auth::user()->storeid;  
        $userId = Auth::user()->id;

        $storecode = DB::table('users as a')
        ->leftJoin('rbostoretables as b', 'a.storeid', '=', 'b.NAME')
        ->where('a.storeid', $currentStore)
        ->value('b.storeid'); 

        
        $validated = $request->validate([
            'to_store_id' => [
                'required',
                'string',
                'exists:rbostoretables,STOREID',
                'different:from_store_id',
                Rule::notIn([$currentStore])
            ],
            'items' => 'required|array|min:1',
            'items.*.itemid' => 'required|exists:inventtables,itemid',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        $nextRec = DB::table('nubersequencevalues')
            ->where('storeid', $currentStore)
            ->lockForUpdate()
            ->value('nextrec');
        
        $nextRec = $nextRec !== null ? (int)$nextRec + 1 : 1;
        
        DB::table('nubersequencevalues')
            ->where('STOREID', $currentStore)
            ->update(['NEXTREC' => $nextRec]);

        $journalId = $userId . str_pad($nextRec, 8, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        
        try {
            $transfer = StockTransfer::create([
                'transfer_number' => $journalId,
                'from_store_id' => $storecode,  
                'to_store_id' => $validated['to_store_id'],
                'status' => 'request',
                'created_by' => $userId,
                'transfer_date' => now()
            ]);

            foreach ($validated['items'] as $item) {
                if ($item['quantity'] > 0) {
                    $itemPrice = DB::table('inventtablemodules')
                        ->where('itemid', $item['itemid'])
                        ->value('priceincltax') ?? 0;  
            
                    StockTransferItem::create([
                        'stock_transfer_id' => $transfer->id,
                        'itemid' => $item['itemid'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $itemPrice,
                        'status' => 'request'
                    ]);
                }
            }

            $journalRecords = DB::table('stock_transfers as a')
            ->leftJoin('stock_transfer_items as b', 'a.id', '=', 'b.stock_transfer_id')
            ->select('b.itemid', DB::raw('SUM(b.quantity) as qty'))
            ->whereDate('a.transfer_date', now())
            ->where('a.from_store_id', '=', $storecode)
            ->groupBy('b.itemid')
            ->get();

            foreach ($journalRecords as $record) {
                DB::table('stockcountingtrans')
                    ->where('ITEMID', $record->itemid)
                    ->whereDate('TRANSDATE', now())
                    ->update([
                        'TRANSFERCOUNT' => $record->qty,
                        'updated_at' => now()
                    ]);
            }
            

            DB::commit();
            return response()->json(['message' => 'Stock transfer created successfully', 'transfer' => $transfer]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating stock transfer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the status of a stock transfer
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockTransfer  $transfer
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, StockTransfer $transfer)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        DB::beginTransaction();
        try {
            // Update transfer status
            $transfer->update([
                'status' => (string)$validated['status'],
                'processed_by' => Auth::id(),
                'processed_at' => Carbon::now(),
            ]);

            // Update items status
            $transfer->items()->update(['status' => $validated['status']]);

            if ($validated['status'] === 'approved') {
                foreach ($transfer->items as $item) {
                    // Create inventory transactions
                    /* inventtrans::create([
                        'POSTINGDATE' => Carbon::now(),
                        'ITEMID' => $item->itemid,
                        'STOREID' => $transfer->to_store_id,
                        'ADJUSTMENT' => $item->quantity,
                        'TYPE' => 'TRANSFER_IN',
                        'REASONCODE' => 'STOCK_TRANSFER_' . $transfer->id,
                    ]); */

                    /* inventtrans::create([
                        'POSTINGDATE' => Carbon::now(),
                        'ITEMID' => $item->itemid,
                        'STOREID' => $transfer->from_store_id,
                        'ADJUSTMENT' => -$item->quantity,
                        'TYPE' => 'TRANSFER_OUT',
                        'REASONCODE' => 'STOCK_TRANSFER_' . $transfer->id,
                    ]); */
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Stock transfer status updated successfully',
                'transfer' => $transfer->fresh(['items', 'from_store', 'to_store'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating stock transfer status: ' . $e->getMessage()
            ], 500);
        }
    }
}
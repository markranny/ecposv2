<?php

namespace App\Http\Controllers;

use App\Models\StockTransferTable;
use App\Models\rbostoretables;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StockTransferController extends Controller
{
    public function index()
    {
        /* $transfers = StockTransferTable::with(['fromStore', 'toStore'])
            ->orderBy('CREATEDDATETIME', 'desc')
            ->get(); */
        /* $transfers = StockTransferTable::all(); */

        $storeId = Auth::user()->storeid;

        $transfers = StockTransferTable::where('FROM_STOREID', $storeId)
        ->orWhere('TO_STOREID', $storeId)
        ->get();
            
        $stores = rbostoretables::where('BLOCKED', 0)
            ->orderBy('NAME')
            ->get();

        return Inertia::render('StockTransfer/index', [
            'stocktransfertables' => $transfers,
            'currentStoreId' => $storeId,
            'stores' => $stores
        ]);
    }

    public function store(Request $request)
    {
        try {
            /* $request->validate([
                'from_storeid' => 'required|string|exists:rbostoretables,STOREID',
                'to_storeid' => 'required|string|exists:rbostoretables,STOREID|different:from_storeid',
                'description' => 'required|string',
            ]); */

            $currentDateTime = Carbon::now('Asia/Manila');

            StockTransferTable::create([
                'FROM_STOREID' => $request->from_storeid,
                'TO_STOREID' => $request->to_storeid,
                'DESCRIPTION' => $request->description,
                'POSTED' => 0,
                'JOURNALTYPE' => 1,
                'DELETEPOSTEDLINES' => 0,
                'CREATEDDATETIME' => $currentDateTime,
            ]);

            return redirect()->route('StockTransfer')
                ->with('message', 'Stock Transfer Created Successfully')
                ->with('isSuccess', true);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', $e->errors())
                ->with('isSuccess', false);
        }
    }

    public function post(Request $request)
    {
        try {
            $request->validate([
                'journalid' => 'required|exists:stocktransfertables,JOURNALID'
            ]);

            DB::beginTransaction();

            $transfer = StockTransferTable::with('transferLines')
                ->findOrFail($request->journalid);

            // Validate transfer can be sent
            if (!$transfer->canBeSent()) {
                throw ValidationException::withMessages([
                    'journalid' => ['This stock transfer cannot be sent in its current state.']
                ]);
            }

            // Validate items exist and quantities are valid
            if ($transfer->transferLines->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => ['No items found in this transfer']
                ]);
            }

            $invalidItems = $transfer->transferLines->filter(function ($line) {
                return $line->COUNTED <= 0;
            });

            if ($invalidItems->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'quantities' => ['All items must have quantities greater than 0']
                ]);
            }

            $currentDateTime = Carbon::now('Asia/Manila');

            // Update transfer status
            $transfer->update([
                'POSTED' => 1,
                'POSTEDDATETIME' => $currentDateTime,
                'STATUS' => StockTransferTable::STATUS_SENT
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer sent successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($journalid)
    {
        $transfer = StockTransferTable::with(['fromStore', 'toStore'])
            ->findOrFail($journalid);
            
        return Inertia::render('StockTransfer/Show', [
            'transfer' => $transfer,
        ]);
    }

    public function receive(Request $request)
    {
        try {
            $request->validate([
                'journalid' => 'required|exists:stocktransfertables,JOURNALID'
            ]);

            DB::beginTransaction();

            $transfer = StockTransferTable::with('transferLines')
                ->findOrFail($request->journalid);

            // Validate transfer can be received
            if (!$transfer->canBeReceived()) {
                throw ValidationException::withMessages([
                    'journalid' => ['This stock transfer cannot be received in its current state.']
                ]);
            }

            $currentDateTime = Carbon::now('Asia/Manila');

            // Update transfer status
            $transfer->update([
                'SENT' => 1,
                'SENTDATETIME' => $currentDateTime,
                'STATUS' => StockTransferTable::STATUS_RECEIVED
            ]);

            // Update inventory quantities here
            // This is where you'd implement the logic to update your inventory system
            foreach ($transfer->transferLines as $line) {
                // Update inventory for receiving store
                // This is a placeholder - implement your actual inventory update logic
                $this->updateInventory(
                    $transfer->TO_STOREID,
                    $line->ITEMID,
                    $line->COUNTED
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer received successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

public function posted(Request $request)
{
    try {
        $request->validate([
            'journalid' => 'required|exists:stocktransfertables,JOURNALID'
        ]);

        $transfer = StockTransferTable::findOrFail($request->journalid);

        if ($transfer->POSTED) {
            throw ValidationException::withMessages([
                'journalid' => ['This stock transfer has already been posted.']
            ]);
        }

        $currentDateTime = Carbon::now('Asia/Manila');

        $transfer->update([
            'POSTED' => 1,
            'POSTEDDATETIME' => $currentDateTime
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock transfer posted successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}
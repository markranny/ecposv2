<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CashFundController extends Controller
{
    public function getCashFundsCount()
    {
        try {
            $currentDateTime = Carbon::now()->toDateString();
            $storename = Auth::user()->storeid;
            
            $total = DB::table('cashfunds')
                ->whereDate('created_at', '=', $currentDateTime)
                ->where('storename', '=', $storename)
                ->sum('amount');

            return response()->json([
                'total' => $total,
                'date' => $currentDateTime
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getCashFundsCount: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch cash funds count'
            ], 500);
        }
    }

    public function submitCashFund(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
            ]);

            $storename = Auth::user()->storeid;

            $now = $currentDateTime = Carbon::now('Asia/Manila');
            DB::table('cashfunds')->insert([
                'amount' => $validated['amount'],
                'storename' => $storename,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->printReceipt($validated['amount'], $now);

            return response()->json([
                'message' => 'Cash fund submitted successfully',
                'timestamp' => $now
            ]);
        } catch (\Exception $e) {
            Log::error('Error in submitCashFund: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to submit cash fund'
            ], 500);
        }
    }

    private function printReceipt($amount, $timestamp)
{
    try {
        $connector = new WindowsPrintConnector("POS-80C");
        $printer = new Printer($connector);

        // Print the receipt details
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Cash Fund Receipt\n");
        $printer->text("------------------------------------------------\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Amount: " . number_format($amount, 2) . "\n");
        $printer->text("Date & Time: " . $timestamp->toDateTimeString() . "\n");
        $printer->text("------------------------------------------------\n");
        $printer->cut();
        $printer->close();
    } catch (\Exception $e) {
        Log::error('Error printing receipt: ' . $e->getMessage());
    }
}
}

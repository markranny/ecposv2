<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\inventtables;
use App\Models\rboinventtables;
use App\Models\windowtrans;
use App\Models\inventtablemodules;
use App\Models\importproducts;
use App\Models\carts;
use App\Models\carttables;
use App\Models\ars;
use App\Models\rboinventitemretailgroups;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Carbon\Carbon;
use Inertia\Inertia;

class POSController extends Controller
{
    public function index(Request $request)
    {

        $category = DB::table('rboinventitemretailgroups')
            ->select('groupid','name')   
            ->get();


        /* dd($category); */

        $items = DB::table('inventtablemodules as a')
          ->select(
              'a.ITEMID as itemid',
              'b.itemname as itemname',
              /* 'a.quantity as quantity', */
              DB::raw('CAST(a.quantity as int) as quantity'),
              'c.itemgroup as itemgroup',
              'c.itemdepartment as specialgroup',
              DB::raw('ROUND(FORMAT(a.priceincltax, "N2"), 2) as price'),
              DB::raw('ROUND(FORMAT(a.price, "N2"), 2) as cost'),
              DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
          )
          ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
          ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
          ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
          ->get();

        return Inertia::render('Menu/Index', ['items' => $items, 'category' => $category]);
    }

    /* public function menu($id)
    {

        $category = DB::table('rboinventitemretailgroups')
            ->select('groupid','name')   
            ->get();

        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

        $cashfund = DB::table('cashfunds')
            ->select('AMOUNT')
            ->whereDate('created_at', '=', $currentDateTime)
            ->first();

        $cashfundAmount = $cashfund ? $cashfund->AMOUNT : 0;

        if($cashfundAmount >= 1){

            $items = DB::table('inventtablemodules as a')
          ->select(
              'a.ITEMID as itemid',
              'b.itemname as itemname',
              DB::raw('CAST(a.quantity as int) as quantity'),
              'c.itemgroup as itemgroup',
              'c.itemdepartment as specialgroup',
              DB::raw('ROUND(FORMAT(a.priceincltax, "N2"), 2) as price'),
              DB::raw('ROUND(FORMAT(a.price, "N2"), 2) as cost'),
              DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
          )
          ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
          ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
          ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
          ->get();

          return Inertia::render('Menu/Index', ['items' => $items, 'category' => $category]);

        }else{
            return Inertia::render('Cashfunds/cashfund');
        }     
    } */

    

    public function menu($id)
    {

        $windowId = DB::table('windowtrans')
        ->where('id', $id)
        ->value('id');

        $windowDesc = DB::table('windowtrans')
        ->where('id', $id)
        ->value('DESCRIPTION');

        $category = DB::table('rboinventitemretailgroups')
            ->select('groupid', 'name')   
            ->get();

        $ar = DB::table('ar')
            ->select('ar')   
            ->get();

        $customers = DB::table('customers')
            ->select('accountnum','name')   
            ->get();

        $utcDateTime = Carbon::now('UTC');
        $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

        $cashfund = DB::table('cashfunds')
            ->select('AMOUNT')
            ->whereDate('created_at', '=', $currentDateTime)
            ->first();

        $cashfundAmount = $cashfund ? $cashfund->AMOUNT : 0;

        if($cashfundAmount >= 1){
            $items = DB::table('inventtablemodules as a')
                ->select(
                    'a.ITEMID as itemid',
                    'b.itemname as itemname',
                    DB::raw('CAST(a.quantity as int) as quantity'),
                    'c.itemgroup as itemgroup',
                    'c.itemdepartment as specialgroup',
                    DB::raw('ROUND(FORMAT(a.priceincltax, "N2"), 2) as price'),
                    DB::raw('ROUND(FORMAT(a.price, "N2"), 2) as cost'),
                    DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
                )
                ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
                ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
                ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
                ->where('a.priceincltax', '!=', '0')
                ->get();

                return Inertia::render('Menu/Index', [
                    'items' => $items, 
                    'category' => $category,
                    'windowId' => $windowId,
                    'ar' => $ar,
                    'customers' => $customers,
                    'windowDesc' => $windowDesc
                ]);
        } else {
            return Inertia::render('Cashfunds/cashfund');
        }     
    }

    /* public function addtocart($id, $winid, $ar, $customers)
{
    try {
        DB::beginTransaction();

        Log::info("Starting addtocart function", ['itemid' => $id, 'winid' => $winid]);

        $itemData = DB::table('inventtables')
            ->join('rboinventtables', 'inventtables.itemid', '=', 'rboinventtables.itemid')
            ->join('inventtablemodules', 'inventtables.itemid', '=', 'inventtablemodules.itemid')
            ->where('inventtables.itemid', $id)
            ->select('inventtables.itemname', 'rboinventtables.itemgroup', 'inventtablemodules.priceincltax as price')
            ->first();

        if (!$itemData) {
            Log::warning("Item not found", ['itemid' => $id]);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $store = Auth::user()->storeid;
        $staff = Auth::user()->name;
        $currentDateTime = Carbon::now('Asia/Manila')->toDateString();

        $existingCart = DB::table('carts')
            ->where('itemid', $id)
            ->where('wintransid', $winid)
            ->first();

        if ($existingCart) {
            Log::info("Updating existing cart item", ['itemid' => $id, 'winid' => $winid]);
            $newQty = $existingCart->qty + 1;
            $grossAmount = $itemData->price * $newQty;
            $taxInclinPrice = round($grossAmount / 1.12, 2);
            $netAmountNotInclTax = ($grossAmount / 1.12) * 1.12;
            $costAmount = $grossAmount - ($grossAmount * 0.12);
            $netAmount = $grossAmount - ($existingCart->discamount ?? 0);

            DB::table('carts')
                ->where('itemid', $id)
                ->where('wintransid', $winid)
                ->update([
                    'netprice' => $itemData->price,
                    'qty' => $newQty,
                    'costamount' => $costAmount,
                    'netamount' => $netAmount,
                    'grossamount' => $grossAmount,
                    'taxinclinprice' => $taxInclinPrice,
                    'netamountnotincltax' => $netAmountNotInclTax,
                    'updated_at' => now()
                ]);

            $message = 'Item quantity updated in cart';
        } else {
            Log::info("Adding new item to cart", ['itemid' => $id, 'winid' => $winid]);
            $grossAmount = $itemData->price;
            $taxInclinPrice = round($grossAmount / 1.12, 2);
            $netAmountNotInclTax = ($grossAmount / 1.12) * 1.12;
            $costAmount = $grossAmount - ($grossAmount * 0.12);
            $netAmount = $grossAmount;

            //carts
            DB::table('carts')->insert([
                'itemid' => $id,
                'itemname' => $itemData->itemname,
                'itemgroup' => $itemData->itemgroup,
                'price' => $itemData->price,
                'netprice' => $itemData->price - ($itemData->price * 0.12),
                'qty' => 1,
                'discamount' => 0,
                'costamount' => $costAmount,
                'netamount' => $netAmount,
                'grossamount' => $grossAmount,
                'custdiscamount' => 0,
                'taxinclinprice' => $taxInclinPrice,
                'netamountnotincltax' => $netAmountNotInclTax,
                'store' => $store,
                'custaccount' => $customers,
                'paymentmethod' => $ar,
                'staff' => $staff,
                'unit' => 'PCS',
                'createddate' => $currentDateTime,
                'currency' => 'PHP',
                'wintransid' => $winid,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            //carttables
            DB::table('carttables')->insert([
                'store' => $store,
                'staff' => $staff,
                'netamount' => currentcarts(sum($netAmount)),
                'costamount' => currentcarts(sum($costamount)),
                'grossamount' => currentcarts(sum($grossAmount)),
                'discamount' => 0,
                'custdiscamount' => 0,
                'totaldiscamount' => 0,
                'numberofitems' => numberofitems(joining both 2 id of carttables and carts),
                'createddate' => now(),
                'taxincltax' => $taxInclinPrice,
                'vat' => $netAmountNotInclTax,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $message = 'Item added to cart successfully';
        }

        DB::commit();
        Log::info("addtocart function completed successfully", ['message' => $message]);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error in addtocart function", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while processing your request: ' . $e->getMessage()
        ], 500);
    }
} */

public function addToCart($id, $winid, $ar, $customers)
    {
        try {
            DB::beginTransaction();

            Log::info("Starting addToCart function", ['itemid' => $id, 'winid' => $winid]);

            $itemData = $this->getItemData($id);

            if (!$itemData) {
                Log::warning("Item not found", ['itemid' => $id]);
                DB::rollBack();
                return $this->errorResponse('Item not found', 404);
            }

            $store = Auth::user()->storeid;
            $staff = Auth::user()->name;
            $currentDate = Carbon::now('Asia/Manila')->toDateString();

            $existingCart = $this->getExistingCartItem($id, $winid);

            if ($existingCart) {
                $this->updateExistingCartItem($existingCart, $itemData);
                $message = 'Item quantity updated in cart';
            } else {
                $this->addNewCartItem($id, $itemData, $store, $staff, $customers, $ar, $winid, $currentDate);
                $message = 'Item added to cart successfully';
            }

            $this->updateCartTables($store, $staff, $winid);

            DB::commit();
            Log::info("addToCart function completed successfully", ['message' => $message]);

            return $this->successResponse($message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in addToCart function", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('An error occurred while processing your request: ' . $e->getMessage(), 500);
        }
    }

    private function getItemData($id)
    {
        return DB::table('inventtables')
            ->join('rboinventtables', 'inventtables.itemid', '=', 'rboinventtables.itemid')
            ->join('inventtablemodules', 'inventtables.itemid', '=', 'inventtablemodules.itemid')
            ->where('inventtables.itemid', $id)
            ->select('inventtables.itemid', 'inventtables.itemname', 'rboinventtables.itemgroup', 'inventtablemodules.priceincltax as price')
            ->first();
    }

    private function getExistingCartItem($id, $winid)
    {
        return DB::table('carts')
            ->where('itemid', $id)
            ->where('wintransid', $winid)
            ->first();
    }

    private function updateExistingCartItem($existingCart, $itemData)
    {
        $newQty = $existingCart->qty + 1;
        $grossAmount = $itemData->price * $newQty;
        $taxInclinPrice = round($grossAmount / 1.12, 2);
        $netAmountNotInclTax = $grossAmount / 1.12;
        $costAmount = $netAmountNotInclTax;
        $netAmount = $grossAmount - ($existingCart->discamount ?? 0);

        DB::table('carts')
            ->where('itemid', $existingCart->itemid)
            ->where('wintransid', $existingCart->wintransid)
            ->update([
                'netprice' => $itemData->price,
                'qty' => $newQty,
                'costamount' => $costAmount,
                'netamount' => $netAmount,
                'grossamount' => $grossAmount,
                'taxinclinprice' => $taxInclinPrice,
                'netamountnotincltax' => $netAmountNotInclTax,
                'updated_at' => now()
            ]);
    }

    private function addNewCartItem($id, $itemData, $store, $staff, $customers, $ar, $winid, $currentDate)
    {
        $grossAmount = $itemData->price;
        $taxInclinPrice = round($grossAmount / 1.12, 2);
        $netAmountNotInclTax = $grossAmount / 1.12;
        $costAmount = $netAmountNotInclTax;
        $netAmount = $grossAmount;

        $storeId = Auth::user()->storeid;
        $userId = Auth::user()->id;

        $nextRec = DB::table('nubersequencevalues')
            ->where('storeid', $storeId)
            ->lockForUpdate()
            ->value('cartnextrec');

        $cartNextRec = $nextRec !== null ? (int)$nextRec + 1 : 1;

        $cartId = $userId . str_pad($cartNextRec, 8, '0', STR_PAD_LEFT);

        DB::table('carts')->insert([
            'cartid' => $cartId,
            'itemid' => $id,
            'itemname' => $itemData->itemname,
            'itemgroup' => $itemData->itemgroup,
            'price' => $itemData->price,
            'netprice' => $netAmountNotInclTax,
            'qty' => 1,
            'discamount' => 0,
            'costamount' => $costAmount,
            'netamount' => $netAmount,
            'grossamount' => $grossAmount,
            'custdiscamount' => 0,
            'taxinclinprice' => $taxInclinPrice,
            'netamountnotincltax' => $netAmountNotInclTax,
            'store' => $store,
            'custaccount' => $customers,
            'paymentmethod' => $ar,
            'staff' => $staff,
            'unit' => 'PCS',
            'createddate' => $currentDate,
            'currency' => 'PHP',
            'wintransid' => $winid,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('nubersequencevalues')
            ->where('storeid', $storeId)
            ->update(['cartnextrec' => $cartNextRec]);
    }

    private function updateCartTables($store, $staff, $winid)
    {
        $cartTotals = DB::table('carts')
            ->where('store', $store)
            ->where('wintransid', $winid)
            ->selectRaw('SUM(netamount) as total_netamount, SUM(costamount) as total_costamount, SUM(grossamount) as total_grossamount, COUNT(*) as numberofitems, SUM(discamount) as total_discamount, SUM(custdiscamount) as total_custdiscamount')
            ->first();

        $existingCartTable = DB::table('carttables')
            ->where('store', $store)
            ->where('window_number', $winid)
            ->first();

        if ($existingCartTable) {
            $this->updateExistingCartTables($existingCartTable, $cartTotals);
        } else {
            $this->createNewCartTable($store, $staff, $winid, $cartTotals);
        }
    }

    private function updateExistingCartTables($existingCartTable, $cartTotals)
    {
        DB::table('carttables')
            ->where('id', $existingCartTable->id)
            ->update([
                'netamount' => $cartTotals->total_netamount ?? 0,
                'costamount' => $cartTotals->total_costamount ?? 0,
                'grossamount' => $cartTotals->total_grossamount ?? 0,
                'discamount' => $cartTotals->total_discamount ?? 0,
                'custdiscamount' => $cartTotals->total_custdiscamount ?? 0,
                'totaldiscamount' => ($cartTotals->total_discamount ?? 0) + ($cartTotals->total_custdiscamount ?? 0),
                'numberofitems' => $cartTotals->numberofitems ?? 0,
                'taxinclinprice' => round(($cartTotals->total_grossamount ?? 0) / 1.12, 2),
                'vat' => (($cartTotals->total_grossamount ?? 0) / 1.12) * 0.12,
                'updated_at' => now()
            ]);
    }

    private function createNewCartTable($store, $staff, $winid, $cartTotals)
    {
        $storeId = Auth::user()->storeid;
        $userId = Auth::user()->id;

        $nextRec = DB::table('nubersequencevalues')
            ->where('storeid', $storeId)
            ->lockForUpdate()
            ->value('cartnextrec');

        $cartnextrec = $nextRec !== null ? (int)$nextRec + 1 : 1;

        $cartId = $userId . str_pad($cartnextrec, 8, '0', STR_PAD_LEFT);

        DB::table('carttables')->insert([
            'cartid' => $cartId,
            'store' => $store,
            'staff' => $staff,
            'netamount' => $cartTotals->total_netamount ?? 0,
            'costamount' => $cartTotals->total_costamount ?? 0,
            'grossamount' => $cartTotals->total_grossamount ?? 0,
            'discamount' => $cartTotals->total_discamount ?? 0,
            'custdiscamount' => $cartTotals->total_custdiscamount ?? 0,
            'totaldiscamount' => ($cartTotals->total_discamount ?? 0) + ($cartTotals->total_custdiscamount ?? 0),
            'numberofitems' => $cartTotals->numberofitems ?? 0,
            'createddate' => now(),
            'taxinclinprice' => round(($cartTotals->total_grossamount ?? 0) / 1.12, 2),
            'vat' => (($cartTotals->total_grossamount ?? 0) / 1.12) * 0.12,
            'window_number' => $winid,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('nubersequencevalues')
            ->where('storeid', $storeId)
            ->update(['cartnextrec' => $cartnextrec]);
    }

    private function successResponse($message)
    {
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    private function errorResponse($message, $statusCode)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }

    public function cart()
    {
        $cartItems = DB::table('carts')
            ->select('itemid', 'itemname', 
                     DB::raw('CAST(grossamount AS FLOAT) as total_price'), 
                     DB::raw('CAST(qty AS FLOAT) as total_qty'),
                     DB::raw('CAST(discamount AS FLOAT) as discamount'))
            ->get();
        
        return response()->json([
            'items' => $cartItems,
            'message' => 'Cart items retrieved successfully',
        ]);
    }


    public function deleteMultiple(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'string'
        ]);

        $itemsToDelete = $request->input('items');

        try {
            
            $deletedCount = Carts::whereIn('itemname', $itemsToDelete)
                ->delete();

            return response()->json([
                'message' => "{$deletedCount} items deleted successfully",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitPartialPayment(Request $request)
    {
        DB::beginTransaction();

        try {
            $partialPaymentAmount = $request->input('amount');
            /* $wintransid = $request->input('wintransid'); */
            $wintransid = '1';
            /* $store = Auth::user()->storeid;
            $staff = Auth::user()->name; */
            $currentDate = Carbon::now('Asia/Manila')->toDateString();

            Log::info('Partial payment submission attempt', [
                'amount' => $partialPaymentAmount,
                'wintransid' => $wintransid
            ]);

            // Validate input
            $this->validatePartialPayment($partialPaymentAmount, $wintransid);

            // Update Carts and Carttables
            $this->updateCartsAndCarttables($wintransid, $partialPaymentAmount);

            // Generate and print receipt
            $this->generateAndPrintReceipt($partialPaymentAmount, $wintransid);

            DB::commit();

            Log::info('Partial payment submitted successfully', [
                'amount' => $partialPaymentAmount,
                'wintransid' => $wintransid
            ]);

            return response()->json(['success' => true, 'message' => 'Partial payment submitted and receipt printed successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit partial payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'wintransid' => $wintransid ?? 'Not provided',
                'amount' => $partialPaymentAmount ?? 'Not provided'
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to submit partial payment: ' . $e->getMessage()], 500);
        }
    }

    private function validatePartialPayment($partialPaymentAmount, $wintransid)
    {
        if (!is_numeric($partialPaymentAmount) || $partialPaymentAmount <= 0) {
            throw new \InvalidArgumentException("Invalid partial payment amount: $partialPaymentAmount");
        }

        $cartTable = Carttables::where('window_number', $wintransid)->first();
        if (!$cartTable) {
            Log::error('Cart table not found', ['wintransid' => $wintransid]);
            throw new \Exception("Cart table not found for wintransid: $wintransid. Please ensure the wintransid is correct and the cart exists.");
        }

        $totalAmount = $cartTable->grossamount;
        $remainingBalance = $totalAmount - $cartTable->partialpayment;

        if ($partialPaymentAmount > $remainingBalance) {
            throw new \InvalidArgumentException("Partial payment amount ($partialPaymentAmount) exceeds remaining balance ($remainingBalance)");
        }

        Log::info('Partial payment validated', [
            'wintransid' => $wintransid,
            'amount' => $partialPaymentAmount,
            'remaining_balance' => $remainingBalance
        ]);
    }

    private function updateCartsAndCarttables($wintransid, $partialPaymentAmount)
    {

        // Update Carttables
        $updatedRows = Carttables::where('window_number', $wintransid)
            ->update([
                'partialpayment' => DB::raw("partialpayment + $partialPaymentAmount"),
                'updated_at' => now()
            ]);

        if ($updatedRows === 0) {
            Log::error('Failed to update cart table', ['wintransid' => $wintransid]);
            throw new \Exception("Failed to update cart table for wintransid: $wintransid");
        }

        Log::info('Carts and Carttables updated successfully', [
            'wintransid' => $wintransid,
            'partial_payment' => $partialPaymentAmount
        ]);
    }

    private function generateAndPrintReceipt($partialPaymentAmount, $wintransid)
    {
        try {
            $cartItems = Carts::where('wintransid', $wintransid)->get();
            $total = $cartItems->sum('grossamount');
            $vatRate = 0.12; // Assuming 12% VAT, adjust as needed
            $vatAmount = $total * $vatRate / (1 + $vatRate);
            $vatableSales = $total - $vatAmount;

            $cartTable = Carttables::where('window_number', $wintransid)->first();
            $totalPartialPayment = $cartTable ? $cartTable->partialpayment : $partialPaymentAmount;

            // Generate base receipt content
            $baseReceiptContent = $this->generateReceiptHeader($wintransid);
            $baseReceiptContent .= $this->generateReceiptItems($cartItems);
            $baseReceiptContent .= $this->generateReceiptSummary($total, $vatAmount, $partialPaymentAmount, $vatableSales, $totalPartialPayment);
            $baseReceiptContent .= $this->generateReceiptFooter($wintransid);

            // Generate and print Customer Copy
            $customerCopy = $this->addCopyLabel($baseReceiptContent, "Customer Copy");
            $this->printReceipt($customerCopy);

            // Generate and print Staff Copy
            $staffCopy = $this->addCopyLabel($baseReceiptContent, "Staff Copy");
            $this->printReceipt($staffCopy);

            // Generate and print Window Number
            $windowNumber = $this->generateWindowNumberPrint($wintransid);
            $this->printReceipt($windowNumber);

        } catch (\Exception $e) {
            Log::error('Error in generateAndPrintReceipt: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    private function generateReceiptHeader($wintransid)
    {
        $header = "YOUR BUSINESS NAME\n";
        $header .= "Address Line 1\n";
        $header .= "Address Line 2\n";
        $header .= "Tel: Your Phone Number\n";
        $header .= "TIN: Your TIN Number\n";
        $header .= "ACC: Your ACC Number\n";
        $header .= "Date Issued: " . now()->format('Y-m-d') . "\n";
        $header .= "Contact #: Your Contact Number\n";
        $header .= str_repeat("-", 40) . "\n";
        $header .= "Date: " . now()->format('Y-m-d H:i:s') . "\n";
        $header .= "Receipt #: " . str_pad(Carts::where('wintransid', $wintransid)->max('id'), 9, "0", STR_PAD_LEFT) . "\n";
        $header .= "Cashier: " ."\n";
        $header .= str_repeat("-", 40) . "\n";
        $header .= sprintf("%-20s %8s %3s %8s\n", "Item Name", "Price", "Qty", "Total");

        return $header;
    }

    private function generateReceiptItems($cartItems)
    {
        $items = "";
        foreach ($cartItems as $item) {
            $pricePerItem = $item->qty > 0 ? $item->price : 0;
            $items .= sprintf("%-20s %8.2f %3d %8.2f\n", 
                substr($item->itemname, 0, 20), 
                $pricePerItem, 
                $item->qty, 
                $item->grossamount
            );
        }
        return $items . str_repeat("-", 40) . "\n";
    }

    private function generateReceiptSummary($total, $vatAmount, $partialPaymentAmount, $vatableSales, $totalPartialPayment)
    {
        $summary = sprintf("%-20s %8.2f\n", "Gross Amount:", $total);
        $summary .= sprintf("%-20s %8.2f\n", "Net Amount:", $total);
        $summary .= sprintf("%-20s %8.2f\n", "VAT Amount:", $vatAmount);
        $summary .= sprintf("%-20s %8.2f\n", "Amount Paid:", $partialPaymentAmount);
        $summary .= sprintf("%-20s %8.2f\n", "Change:", 0.00);
        $summary .= str_repeat("-", 40) . "\n";
        $summary .= "Payment Method:\n";
        $summary .= sprintf("%-20s %8.2f\n", "Cash:", $partialPaymentAmount);
        $summary .= str_repeat("-", 40) . "\n";
        $summary .= sprintf("%-20s %8.2f\n", "Vatable Sales:", $vatableSales);
        $summary .= sprintf("%-20s %8.2f\n", "VAT Amount:", $vatAmount);
        $summary .= sprintf("%-20s %8.2f\n", "Total Partial Payment:", $totalPartialPayment);
        $summary .= sprintf("%-20s %8.2f\n", "Remaining Balance:", max(0, $total - $totalPartialPayment));
        $summary .= str_repeat("-", 40) . "\n";

        return $summary;
    }

    private function generateReceiptFooter($wintransid)
    {
        $footer = "Transaction ID: " . str_pad(Carts::where('wintransid', $wintransid)->max('id'), 9, "0", STR_PAD_LEFT) . "\n";
        $footer .= "Receipt ID: " . str_pad(Carts::where('wintransid', $wintransid)->max('id'), 9, "0", STR_PAD_LEFT) . "\n";
       /*  $footer .= "Store: " . Auth::user()->store->name . "\n"; */
       $footer .= "Store: " . "\n";
        $footer .= "Staff: " . "\n";
        $footer .= "Window Number: " . $wintransid . "\n";
        $footer .= "Date: " . now()->format('Y-m-d H:i:s') . "\n";
        $footer .= str_repeat("-", 40) . "\n";
        $footer .= "ID/OSCA/PWD:\nNAME:\nSignature:\n";
        $footer .= str_repeat("-", 40) . "\n";
        $footer .= "This serves as your official receipt\n";
        $footer .= "This invoice/receipt shall be valid for\n";
        $footer .= "five (5) years from the date of the\n";
        $footer .= "permit to use\n";
        $footer .= str_repeat("-", 40) . "\n";
        $footer .= "POS Provider: Maximum Ideas\n";
        $footer .= "Business Solutions\n";
        $footer .= "Alabang Muntinlupa City, PHL\n";
        $footer .= str_repeat("-", 40) . "\n";
        $footer .= "Thank you for your purchase!\n";

        return $footer;
    }

    private function generateWindowNumberPrint($wintransid)
    {
        $content = str_repeat("-", 40) . "\n";
        $content .= str_pad("Window Number", 40, " ", STR_PAD_BOTH) . "\n";
        $content .= str_repeat("-", 40) . "\n\n";
        $content .= str_pad($wintransid, 40, " ", STR_PAD_BOTH) . "\n\n";
        $content .= str_repeat("-", 40) . "\n";
        $content .= "Transaction ID: " . str_pad(Carts::where('wintransid', $wintransid)->max('id'), 9, "0", STR_PAD_LEFT) . "\n";
        $content .= "Date: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= str_repeat("-", 40) . "\n";

        return $content;
    }

    private function addCopyLabel($receiptContent, $label)
    {
        $labelLine = str_repeat("-", 40) . "\n";
        $labelLine .= str_pad($label, 40, " ", STR_PAD_BOTH) . "\n";
        $labelLine .= str_repeat("-", 40) . "\n";

        return $labelLine . $receiptContent;
    }

    private function printReceipt($content)
    {
        try {
            $connector = new WindowsPrintConnector("POS-80C");
            $printer = new Printer($connector);

            $printer->text($content);
            $printer->cut();
            $printer->close();

            Log::info('Receipt printed successfully');
        } catch (\Exception $e) {
            Log::error('Printer error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw new \Exception('Printer error: ' . $e->getMessage());
        }
    }

}

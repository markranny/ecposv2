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
use App\Models\ars;
use App\Models\rboinventitemretailgroups;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
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

        if($cashfundAmount <= 1){
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

    public function addtocart($id, $winid, $ar, $customers)
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
}

public function cart()
{
    $cartItems = DB::table('carts')
        ->select('itemid', 'itemname', 
                 DB::raw('CAST(grossamount AS FLOAT) as total_price'), 
                 DB::raw('CAST(qty AS FLOAT) as total_qty'),
                 DB::raw('CAST(discamount AS FLOAT) as discamount'))
        /* ->groupBy('itemname', 'itemid', 'grossamount', 'qty', 'discamount') */
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
}

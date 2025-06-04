<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\inventtables;
use App\Models\rboinventtables;
use App\Models\windowtrans;
use App\Models\inventtablemodules;
use App\Models\importproducts;
use App\Models\carts;
use App\Models\carttables;
use App\Models\ars;
use App\Models\rboinventitemretailgroups;
use App\Models\rbotransactiontables;
use App\Models\rbotransactionsalestrans;
use App\Models\posperiodicdiscounts;
use App\Models\posperiodicdiscountlines;
use App\Models\posmmlinegroups;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\BluetoothPrintConnector;
use Mike42\Escpos\Printer;
use Illuminate\Support\Facades\Validator;
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

        if($id == 1 || $id == 2 || $id == 3 || $id == 4 || $id == 5 || $id == 6){
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
                ->whereNotIn('ar', ['FOODPANDA', 'GRABFOOD'])   
                ->get();

            $customers = DB::table('customers')
                ->select('name')   
                ->orderBy('name', 'ASC')
                ->get();

            $utcDateTime = Carbon::now('UTC');
            $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $cashfund = DB::table('cashfunds')
                ->select(
                    DB::raw('CAST(AMOUNT as float) as AMOUNT'),
                )
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

        }elseif($id == 13 || $id == 14){
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
                ->whereIn('ar', ['FOODPANDA'])
                ->get();

                $customers = DB::table('customers')
                ->select('name')   
                ->orderBy('name', 'ASC')
                ->whereIn('name', ['FOODPANDA'])
                ->get();

            $utcDateTime = Carbon::now('UTC');
            $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();
            $storename = Auth::user()->storeid;
            $cashfund = DB::table('cashfunds')
                ->select(
                    DB::raw('CAST(AMOUNT as float) as AMOUNT'),
                )
                ->where('storename', '=', $storename)
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
                        DB::raw('ROUND(FORMAT(a.foodpanda, "N2"), 2) as price'),
                        DB::raw('ROUND(FORMAT(a.price, "N2"), 2) as cost'),
                        DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
                    )
                    ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
                    ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
                    ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
                    ->where('a.foodpanda', '!=', '0')
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
        }elseif($id == 25 || $id == 26){
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
                ->whereIn('ar', ['GRABFOOD'])
                ->get();

                $customers = DB::table('customers')
                ->select('name')   
                ->orderBy('name', 'ASC')
                ->whereIn('name', ['GRABFOOD'])
                ->get();

            $utcDateTime = Carbon::now('UTC');
            $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $cashfund = DB::table('cashfunds')
                ->select(
                    DB::raw('CAST(AMOUNT as float) as AMOUNT'),
                )
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
                        DB::raw('ROUND(FORMAT(a.grabfood, "N2"), 2) as price'),
                        DB::raw('ROUND(FORMAT(a.price, "N2"), 2) as cost'),
                        DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
                    )
                    ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
                    ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
                    ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
                    ->where('a.grabfood', '!=', '0')
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
        }elseif($id == 19 || $id == 20 || $id == 21 || $id == 22 || $id == 23 || $id == 24){
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
                ->select('name')
                ->orderBy('name', 'ASC')   
                ->get();

            $utcDateTime = Carbon::now('UTC');
            $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $cashfund = DB::table('cashfunds')
                ->select(
                    DB::raw('CAST(AMOUNT as float) as AMOUNT'),
                )
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
                    ->where('b.itemname', '=', 'PARTY CAKES')
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

        elseif($id == 7 || $id == 8 || $id == 9 || $id == 10 || $id == 11 || $id == 12){
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
                ->select('name')   
                ->orderBy('name', 'ASC')
                ->get();

            $utcDateTime = Carbon::now('UTC');
            $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $cashfund = DB::table('cashfunds')
                ->select(
                    DB::raw('CAST(AMOUNT as float) as AMOUNT'),
                )
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
                        DB::raw('ROUND(FORMAT(a.manilaprice, "N2"), 2) as price'),
                        DB::raw('ROUND(FORMAT(a.price, "N2"), 2) as cost'),
                        DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
                    )
                    ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
                    ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
                    ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
                    ->where('a.manilaprice', '!=', '0')
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
        else{
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
                ->select('name')   
                ->orderBy('name', 'ASC')
                ->get();

            $utcDateTime = Carbon::now('UTC');
            $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

            $cashfund = DB::table('cashfunds')
                ->select(
                    DB::raw('CAST(AMOUNT as float) as AMOUNT'),
                )
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

        
    }

    public function addToCart($id, $winid, $ar, $customers)
    {
        try {
            DB::beginTransaction();
    
            Log::info("Starting addToCart function", ['itemid' => $id, 'winid' => $winid]);
    
            // Validate window ID
            if (!$winid) {
                Log::warning("Invalid window ID", ['winid' => $winid]);
                DB::rollBack();
                return $this->errorResponse('Invalid window ID', 400);
            }
    
            $itemData = $this->getItemData($id, $winid);
    
            if (!$itemData) {
                Log::warning("Item not found", ['itemid' => $id]);
                DB::rollBack();
                return $this->errorResponse('Item not found', 404);
            }
    
            $store = Auth::user()->storeid;
            $staff = Auth::user()->name;
            $currentDateTime = Carbon::now('Asia/Manila');
    
            // Cast window ID to integer for consistent comparison
            $winid = (int)$winid;
            
            $existingCart = $this->getExistingCartItem($id, $winid);
    
            if ($existingCart) {
                $this->updateExistingCartItem($existingCart, $itemData, $currentDateTime);
                $message = 'Item quantity updated in cart';
            } else {
                $this->addNewCartItem($id, $itemData, $store, $staff, $customers, $ar, $winid, $currentDateTime);
                $message = 'Item added to cart successfully';
            }
    
            /* $this->updateCartTables($store, $staff, $winid); */
    
            DB::commit();
            Log::info("addToCart function completed successfully", ['message' => $message, 'winid' => $winid]);
    
            return $this->successResponse($message);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in addToCart function", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'winid' => $winid
            ]);
            return $this->errorResponse('An error occurred while processing your request: ' . $e->getMessage(), 500);
        }
    }

    private function getItemData($id, $winid)
    {
        if($winid == '13' || $winid == '14' || $winid == '25' || $winid == '26'){
            return DB::table('inventtables')
            ->join('rboinventtables', 'inventtables.itemid', '=', 'rboinventtables.itemid')
            ->join('inventtablemodules', 'inventtables.itemid', '=', 'inventtablemodules.itemid')
            ->where('inventtables.itemid', $id)
            ->select('inventtables.itemid', 'inventtables.itemname', 'rboinventtables.itemgroup', 'inventtablemodules.foodpanda as price')
            ->first();
        }else if($winid == '7' || $winid == '8' || $winid == '9' || $winid == '10' || $winid == '11' || $winid == '12'){
            return DB::table('inventtables')
            ->join('rboinventtables', 'inventtables.itemid', '=', 'rboinventtables.itemid')
            ->join('inventtablemodules', 'inventtables.itemid', '=', 'inventtablemodules.itemid')
            ->where('inventtables.itemid', $id)
            ->select('inventtables.itemid', 'inventtables.itemname', 'rboinventtables.itemgroup', 'inventtablemodules.manilaprice as price')
            ->first();
        }
        else{
            return DB::table('inventtables')
            ->join('rboinventtables', 'inventtables.itemid', '=', 'rboinventtables.itemid')
            ->join('inventtablemodules', 'inventtables.itemid', '=', 'inventtablemodules.itemid')
            ->where('inventtables.itemid', $id)
            ->select('inventtables.itemid', 'inventtables.itemname', 'rboinventtables.itemgroup', 'inventtablemodules.priceincltax as price')
            ->first();
        }
        
    }

    private function getExistingCartItem($id, $winid)
    {
        return DB::table('carts')
            ->where('itemid', $id)
            ->where('wintransid', (int)$winid)
            ->first();
    }

    private function updateExistingCartItem($existingCart, $itemData, $currentDateTime)
{
    $newQty = $existingCart->qty + 1;
    $grossAmount = $itemData->price * $newQty;
    $taxInclinPrice = round((($grossAmount - ($existingCart->discamount ?? 0)) / 1.12) * .12, 2);
    $netAmountNotInclTax = ($grossAmount - ($existingCart->discamount ?? 0)) / 1.12;
    $costAmount = $netAmountNotInclTax;
    $netAmount = $grossAmount - ($existingCart->discamount ?? 0);

    // Fetch discount type from the database
    $disctyperesult = DB::table('carts')
        ->where('itemid', $existingCart->itemid)
        ->where('wintransid', $existingCart->wintransid)
        ->value('disctypes');

    // Calculate discount based on type
    $discountValue = $existingCart->discamount ?? 0;
    $discountParameter = $existingCart->discparameter ?? 0;
    switch ($disctyperesult) {
        case 'PERCENTAGE':
            $discountAmount = $grossAmount * ($discountValue / 100);
            break;
        case 'FIXED':
            $discountAmount = $discountParameter * $newQty;
            break;
        case 'FIXEDTOTAL':
            $discountAmount = $discountParameter; 
            break;
        default:
            $discountAmount = 0;
    }

    // Update the cart item in the database
    DB::table('carts')
        ->where('itemid', $existingCart->itemid)
        ->where('wintransid', $existingCart->wintransid)
        ->update([
            'netprice' => $itemData->price,
            'qty' => $newQty,
            'costamount' => $costAmount,
            'discamount' => $discountAmount,
            'netamount' => $grossAmount - $discountAmount,
            'grossamount' => $grossAmount,
            'taxinclinprice' => $taxInclinPrice,
            'netamountnotincltax' => $netAmountNotInclTax,
            'updated_at' => $currentDateTime
        ]);
}


    private function addNewCartItem($id, $itemData, $store, $staff, $customers, $ar, $winid, $currentDateTime)
    {
        $grossAmount = $itemData->price;
        $taxInclinPrice = round(($grossAmount / 1.12)*.12, 2);
        $netAmountNotInclTax = $grossAmount / 1.12;
        $costAmount = $netAmountNotInclTax;
        $netAmount = $grossAmount;

        $storeId = Auth::user()->storeid;
        $userId = Auth::user()->id;

        $nextRec = DB::table('nubersequencevalues')
            /* ->where('storeid', $storeId) */
            ->lockForUpdate()
            ->value('cartnextrec');

        $cartNextRec = $nextRec !== null ? (int)$nextRec + 1 : 1; 
        $cartId = $userId . str_pad($cartNextRec, 8, '0', STR_PAD_LEFT);

        Log::info($cartNextRec);
        Log::info($cartId);

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
            'createddate' => $currentDateTime,
            'currency' => 'PHP',
            'wintransid' => $winid,
            'created_at' => $currentDateTime,
            'updated_at' => $currentDateTime
        ]);

        /* DB::table('nubersequencevalues')
            ->where('storeid', $storeId)
            ->update(['cartnextrec' => $cartNextRec]); */
    }

    private function updateCartTables($store, $staff, $winid)
    {
        $currentDateTime = Carbon::now('Asia/Manila');

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
            $this->updateExistingCartTables($existingCartTable, $cartTotals, $currentDateTime);
        } else {
            $this->createNewCartTable($store, $staff, $winid, $cartTotals, $currentDateTime);
        }
    }

    private function updateExistingCartTables($existingCartTable, $cartTotals, $currentDateTime)
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
                'updated_at' => $currentDateTime
            ]);
    }

    private function createNewCartTable($store, $staff, $winid, $cartTotals, $currentDateTime)
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
            'createddate' => $currentDateTime,
            'taxinclinprice' => round(($cartTotals->total_grossamount ?? 0) / 1.12, 2),
            'vat' => (($cartTotals->total_grossamount ?? 0) / 1.12) * 0.12,
            'window_number' => $winid,
            'created_at' => $currentDateTime,
            'updated_at' => $currentDateTime
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

    public function deleteMultiple(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'string'
        ]);

        $itemsToDelete = $request->input('items');

        try {
            
            /* $deletedCount = Carts::whereIn('itemname', $itemsToDelete)
                ->delete(); */

                $deletedCount = Carts::whereIn('itemname', $itemsToDelete)
                ->where(function($query) {
                    $query->where('partial_payment', '<=', 0)
                          ->orWhereNull('partial_payment');
                })
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

    public function submitPartialPayments(Request $request)
{
    Log::info('Received partial payment submission request', ['data' => $request->all()]);

    try {
        $partialPayments = $request->input('partialPayments');
        $updatedItems = [];
        $totalPartialPayment = 0;



        foreach ($partialPayments as $payment) {
            $cartItem = Carts::where('itemid', $payment['itemid'])->first();
            
            if (!$cartItem) {
                Log::warning('Cart item not found', ['itemid' => $payment['itemid']]);
                continue;
            }

            $partialPaymentAmount = $payment['amount'];
            $totalPartialPayment += $partialPaymentAmount;

            Log::info('Processing partial payment for item', [
                'itemid' => $cartItem->itemid,
                'partialPayment' => $partialPaymentAmount
            ]);

            $cartItem->partial_payment = $partialPaymentAmount;
            $cartItem->save();

            Log::info('Cart item updated with partial payment', ['itemid' => $cartItem->itemid]);

            $updatedItems[] = [
                'itemid' => $cartItem->itemid,
                'partialPayment' => $cartItem->partial_payment
            ];
        }

        $cartItems = Carts::all();
        $windowDesc = $windowDesc = $cartItems->first()->wintransid;
        $staff = $staff = $cartItems->first()->staff;
        $store = $store = $cartItems->first()->store;

        $currentDateTime = Carbon::now('Asia/Manila');

        $this->generateAndPrintReceipt($totalPartialPayment, $windowDesc, $staff, $store, $currentDateTime);

        Log::info('Partial payments processed successfully', [
            'updatedItems' => $updatedItems,
            'totalPartialPayment' => $totalPartialPayment
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Partial payments submitted successfully',
            'updatedItems' => $updatedItems,
            'totalPartialPayment' => $totalPartialPayment
        ]);
    } catch (\Exception $e) {
        Log::error('Error processing partial payments', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to process partial payments',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function submitRemovePartialPayments(Request $request)
{
    Log::info('Received remove partial payment submission request', ['data' => $request->all()]);

    try {
        $removePartialPayments = $request->input('removePartialPayments');
        $updatedItems = [];
        $totalRemovedAmount = 0;

        foreach ($removePartialPayments as $payment) {
            $cartItem = Carts::where('itemid', $payment['itemid'])->first();
            
            if (!$cartItem) {
                Log::warning('Cart item not found', ['itemid' => $payment['itemid']]);
                continue;
            }

            $removeAmount = $payment['amount'];

            Log::info('Removing partial payment for item', [
                'itemid' => $cartItem->itemid,
                'removeAmount' => $removeAmount
            ]);

            if ($removeAmount > $cartItem->partial_payment) {
                Log::warning('Remove amount exceeds current partial payment', [
                    'itemid' => $cartItem->itemid,
                    'removeAmount' => $removeAmount,
                    'currentPartialPayment' => $cartItem->partial_payment
                ]);
                continue;
            }

            $cartItem->partial_payment = max(0, $cartItem->partial_payment - $removeAmount);
            $cartItem->save();

            $totalRemovedAmount += $removeAmount;

            Log::info('Cart item updated, partial payment removed', ['itemid' => $cartItem->itemid]);

            $updatedItems[] = [
                'itemid' => $cartItem->itemid,
                'partialPayment' => $cartItem->partial_payment,
                'remainingBalance' => $cartItem->total_price - $cartItem->partial_payment
            ];
        }

        $this->generateAndPrintRemovePartialPaymentReceipt($totalRemovedAmount, $updatedItems);

        Log::info('Partial payments removed successfully', [
            'updatedItems' => $updatedItems,
            'totalRemovedAmount' => $totalRemovedAmount
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Partial payments removed successfully',
            'updatedItems' => $updatedItems,
            'totalRemovedAmount' => $totalRemovedAmount
        ]);
    } catch (\Exception $e) {
        Log::error('Error removing partial payments', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to remove partial payments',
            'error' => $e->getMessage()
        ], 500);
    }
}

private function generateAndPrintRemovePartialPaymentReceipt($totalRemovedAmount, $updatedItems)
{
    Log::info('Generating remove partial payment receipt');

    $receiptContent = "Remove Partial Payment Receipt\n\n";
    $receiptContent .= "Total Removed Amount: " . number_format($totalRemovedAmount, 2) . "\n\n";
    $receiptContent .= "Updated Items:\n";

    foreach ($updatedItems as $item) {
        $receiptContent .= "Item ID: " . $item['itemid'] . "\n";
        $receiptContent .= "--------------------\n";
    }


    try {
        $this->printReceipt($receiptContent);
        Log::info('Remove partial payment receipt printed successfully');
    } catch (\Exception $e) {
        Log::error('Failed to print remove partial payment receipt', ['error' => $e->getMessage()]);
    }
}

private function generateAndPrintPartialPaymentReceipt($cartItem, $partialPaymentAmount)
{
    Log::info('Generating partial payment receipt', ['itemid' => $cartItem->itemid]);

    $receiptContent = "Partial Payment Receipt\n\n";
    $receiptContent .= "Item: " . $cartItem->itemname . "\n";
    $receiptContent .= "Item ID: " . $cartItem->itemid . "\n";
    $receiptContent .= "Total Price: " . number_format($cartItem->total_price, 2) . "\n";
    $receiptContent .= "Partial Payment: " . number_format($partialPaymentAmount, 2) . "\n";
    $receiptContent .= $this->generateReceiptFooter();

    try {
        $this->printReceipt($receiptContent);
        Log::info('Partial payment receipt printed successfully', ['itemid' => $cartItem->itemid]);
    } catch (\Exception $e) {
        Log::error('Failed to print partial payment receipt', ['error' => $e->getMessage()]);
    }
}

private function generateAndPrintReceipt($partialPaymentAmount, $windowDesc, $staff, $store, $currentDateTime)
{
    try {
        $cartItems = Carts::all();
        $total = $cartItems->sum('netamount');
        $vatRate = 0.12; // Assuming 12% VAT, adjust as needed
        $vatAmount = $total * $vatRate / (1 + $vatRate);
        $vatableSales = $total - $vatAmount;

        // Generate base receipt content
        $baseReceiptContent = $this->generateReceiptHeader($staff, $currentDateTime);
        
        // Get items and total discount
        list($itemsContent, $totalDiscount) = $this->generateReceiptItems($cartItems);
        $baseReceiptContent .= $itemsContent;
        
        $baseReceiptContent .= $this->generateReceiptSummary($total, $vatAmount, $partialPaymentAmount, $vatableSales, $totalDiscount);
        $baseReceiptContent .= $this->generateReceiptFooter($windowDesc, $currentDateTime);

        // Generate and print Customer Copy
        $customerCopy = $this->addCopyLabel($baseReceiptContent, "Customer Copy");
        $this->printReceipt($customerCopy);

        // Generate and print Staff Copy
        $staffCopy = $this->addCopyLabel($baseReceiptContent, "Staff Copy");
        $this->printReceipt($staffCopy);

        // Generate and print Window Number
        $windowNumber = $this->generateWindowNumberPrint($windowDesc, $currentDateTime);
        $this->printReceipt($windowNumber);

    } catch (\Exception $e) {
        Log::error('Error in generateAndPrintReceipt: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        throw $e;
    }
}

private function addCopyLabel($receiptContent, $label)
{
    $labelLine = str_repeat("-", 40) . "\n";
    $labelLine .= $this->centerText($label, 40) . "\n";
    $labelLine .= str_repeat("-", 40) . "\n";

    return $labelLine . $receiptContent;
}

private function generateWindowNumberPrint($windowDesc,$currentDateTime)
{
    $content = str_repeat("-", 40) . "\n";
    $content .= str_pad("Window Number", 40, " ", STR_PAD_BOTH) . "\n";
    $content .= str_repeat("-", 40) . "\n\n";
    $content .= str_pad( $windowDesc, 40, " ", STR_PAD_BOTH) . "\n\n";
    $content .= str_repeat("-", 40) . "\n";
    $content .= "Date: " . $currentDateTime . "\n";
    $content .= str_repeat("-", 40) . "\n";

    return $content;
}

private function generateReceiptHeader($staff, $currentDateTime)
{
    $header = $this->centerText("ELJIN CORP", 40) . "\n";
    $header .= $this->centerText("Address Line 1", 40) . "\n";
    $header .= $this->centerText("Address Line 2", 40) . "\n";
    $header .= $this->centerText("Tel: Your Phone Number", 40) . "\n";
    $header .= $this->centerText("TIN: Your TIN Number", 40) . "\n";
    $header .= $this->centerText("ACC: Your ACC Number", 40) . "\n";
    $header .= $this->centerText("Date Issued: " . now()->format('Y-m-d'), 40) . "\n";
    $header .= $this->centerText("Contact #: Your Contact Number", 40) . "\n";
    $header .= str_repeat("-", 40) . "\n";
    $header .= $this->centerText("Date: " . $currentDateTime, 40) . "\n";
    $header .= $this->centerText("Receipt #: ", 40) . "\n";
    $header .= $this->centerText("Cashier: " . $staff, 40) . "\n";
    $header .= str_repeat("-", 40) . "\n";
    $header .= sprintf("%-20s %8s %3s %8s\n", "Item Name", "Price", "Qty", "Total");

    return $header;
}

private function generateReceiptItems($cartItems)
{
    $items = "";
    $totalDiscount = 0;
    foreach ($cartItems as $item) {
        $pricePerItem = $item->qty > 0 ? $item->price : 0;
        $items .= sprintf("%-20s %8.2f %3d %8.2f\n", 
            substr($item->itemname, 0, 20), 
            $pricePerItem, 
            $item->qty, 
            $item->grossamount
        );
        
        if ($item->discamount > 0) {
            $items .= sprintf("  %-18s-%8.2f\n", 
                substr($item->discofferid, 0, 18), 
                $item->discamount
            );
            $totalDiscount += $item->discamount;
        }
    }
    $items .= str_repeat("-", 40) . "\n";
    return [$items, $totalDiscount];
}

private function generateReceiptSummary($total, $vatAmount, $partialPaymentAmount, $vatableSales)
{
    $summary = sprintf("%-20s %8.2f\n", "Gross Amount:", $total);
    
    $totalDiscount = Carts::sum('discamount');
    
    $netAmount = $total - $totalDiscount;
    
    $summary .= sprintf("%-20s %8.2f\n", "Discount:", $totalDiscount);
    $summary .= sprintf("%-20s %8.2f\n", "Net Amount:", $netAmount);
    $summary .= sprintf("%-20s %8.2f\n", "VAT Amount:", $vatAmount);
    
    $totalPartialPayments = Carts::sum('partial_payment');
    
    $summary .= sprintf("%-20s %8.2f\n", "Total Partial Payments:", $totalPartialPayments);
    $summary .= sprintf("%-20s %8.2f\n", "Amount Paid:", $partialPaymentAmount);
    $summary .= sprintf("%-20s %8.2f\n", "Change:", max(0, $partialPaymentAmount - $netAmount));
    $summary .= str_repeat("-", 40) . "\n";
    $summary .= "Payment Method:\n";
    $summary .= sprintf("%-20s %8.2f\n", "Cash:", $partialPaymentAmount);
    $summary .= str_repeat("-", 40) . "\n";
    $summary .= sprintf("%-20s %8.2f\n", "Vatable Sales:", $vatableSales);
    $summary .= sprintf("%-20s %8.2f\n", "VAT Amount:", $vatAmount);
    $summary .= sprintf("%-20s %8.2f\n", "Partial Payment:", $partialPaymentAmount);
    $summary .= sprintf("%-20s %8.2f\n", "Remaining Balance:", max(0, $netAmount - $totalPartialPayments));
    $summary .= str_repeat("-", 40) . "\n";

    return $summary;
}

private function generateReceiptFooter($windowDesc, $currentDateTime)
{
    $footer = $this->centerText("Transaction ID: ", 40) . "\n";
    $footer .= $this->centerText("Receipt ID: ", 40) . "\n";
    $footer .= $this->centerText("Window Number: $windowDesc", 40) . "\n";
    $footer .= str_repeat("-", 40) . "\n";
    $footer .= "ID/OSCA/PWD:\nNAME:\nSignature:\n";
    $footer .= str_repeat("-", 40) . "\n";
    $footer .= $this->centerText("This serves as your official receipt", 40) . "\n";
    $footer .= $this->centerText("This invoice/receipt shall be valid for", 40) . "\n";
    $footer .= $this->centerText("five (5) years from the date of the", 40) . "\n";
    $footer .= $this->centerText("permit to use", 40) . "\n";
    $footer .= str_repeat("-", 40) . "\n";
    $footer .= $this->centerText("POS Provider: IT WARRIORS", 40) . "\n";
    $footer .= $this->centerText("ELJIN CORP.", 40) . "\n";
    $footer .= $this->centerText("ADDRESS", 40) . "\n";
    $footer .= str_repeat("-", 40) . "\n";
    $footer .= $this->centerText("Thank you for your purchase!", 40) . "\n";

    return $footer;
}

private function centerText($text, $width)
{
    return str_pad($text, $width, " ", STR_PAD_BOTH);
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

public function cart($windowId)
    {
        $cartItems = DB::table('carts')
            ->select('itemid', 'itemname', 'price', 'itemgroup', 'store', 'staff', 'discofferid', 'unit', 'wintransid',
                     DB::raw('CAST(netprice AS FLOAT) as total_netprice'),
                     DB::raw('CAST(grossamount AS FLOAT) as total_price'), 
                     DB::raw('CAST(partial_payment AS FLOAT) as partial_payment'), 
                     DB::raw('CAST(netamount AS FLOAT) as netamount'),
                     DB::raw('CAST(qty AS FLOAT) as total_qty'),
                     DB::raw('CAST(costamount AS FLOAT) as costamount'),
                     DB::raw('CAST(taxinclinprice AS FLOAT) as taxinclinprice'),
                     DB::raw('CAST(netamountnotincltax AS FLOAT) as netamountnotincltax'),
                     DB::raw('CAST(discamount AS FLOAT) as discamount'))
                     /* ->where('wintransid', $windowId) */
            ->get();
        
        return response()->json([
            'items' => $cartItems,
            'message' => 'Cart items retrieved successfully',
        ]);
    }


    public function submitOrder(Request $request)
    {
        \Log::info('Incoming request data:', $request->all());
    
        try {
            $validated = $request->validate([
                'cashAmount' => 'required|numeric|min:0',
                'cartItems' => 'required|array|min:1',
                'cartItems.*.itemid' => 'required|string',
                'cartItems.*.itemname' => 'required|string',
                'cartItems.*.itemgroup' => 'nullable|string',
                'cartItems.*.price' => 'required|numeric|min:0',
                'cartItems.*.total_qty' => 'required|numeric|min:1',
                'cartItems.*.total_price' => 'required|numeric|min:0',
                'cartItems.*.total_netprice' => 'required|numeric|min:0',
                'cartItems.*.discamount' => 'nullable|numeric|min:0',
                'cartItems.*.costamount' => 'nullable|numeric|min:0',
                'cartItems.*.discofferid' => 'nullable|string',
                'cartItems.*.unit' => 'nullable|string',
                'cartItems.*.taxinclinprice' => 'nullable|numeric|min:0',
                'cartItems.*.netamountnotincltax' => 'nullable|numeric|min:0',
                /* 'cartItems.*.wintransid' => 'nullable|string', */
                'totalAmount' => 'required|numeric|min:0',
                'partialpayment' => 'required|numeric|min:0',
                'selectedAR' => 'required|string',
                'selectedCustomer' => 'required|string',
                'note' => 'nullable|string|max:255',
            ]);
    
            DB::beginTransaction();
    
            $storeId = Auth::user()->storeid;
            $userId = Auth::id();
            $name = Auth::user()->name;
    
            $nextRec = DB::table('nubersequencevalues')
                /* ->where('storeid', $storeId) */
                ->lockForUpdate()
                ->value('cartnextrec');
    
            $nextRec = $nextRec !== null ? (int)$nextRec + 1 : 1;
    
            $receiptno = str_pad($nextRec, 9, '0', STR_PAD_LEFT);
            $description = "R-" . $receiptno;
    
            $transactionId = $receiptno;
            $receiptId = $receiptno;
            $now = $currentDateTime = Carbon::now('Asia/Manila');
    
            // Create sales transaction records
            $salesTransactions = [];
            foreach ($validated['cartItems'] as $index => $item) {
                $netPrice = $item['price'] - ($item['discamount'] ?? 0);
                $netAmount = $item['total_price'] - ($item['discamount'] ?? 0);
                $discountPercent = ($item['discamount'] ?? 0) > 0 ? 
                    (($item['discamount'] ?? 0) / $item['total_price'] * 100) : 0;
    
                $salesTransactions[] = [
                    'transactionid' => $transactionId,
                    'linenum' => $index + 1,
                    'receiptid' => $receiptId,
                    'itemid' => $item['itemid'],
                    'itemname' => $item['itemname'],
                    'itemgroup' => $item['itemgroup'] ?? null,
                    'price' => $item['price'],
                    'netprice' => $netPrice,
                    'qty' => $item['total_qty'],
                    'discamount' => $item['discamount'] ?? 0,
                    'discofferid' => $item['discofferid'],
                    'costamount' => $item['costamount'] ?? 0,
                    'netamount' => $netAmount,
                    'grossamount' => $item['total_price'],
                    'custaccount' => $validated['selectedCustomer'],
                    'store' => $storeId,
                    'paymentmethod' => $validated['selectedAR'],
                    'staff' => $name,
                    'discofferid' => $item['discofferid'] ?? null,
                    'linedscamount' => $item['discamount'] ?? 0,
                    'linediscpct' => $discountPercent,
                    'unit' => $item['unit'] ?? null,
                    'unitqty' => $item['total_qty'],
                    'unitprice' => $item['price'],
                    'taxinclinprice' => $item['taxinclinprice'] ?? 0,
                    'netamountnotincltax' => $item['netamountnotincltax'] ?? 0,
                    'createddate' => $now,
                    'wintransid' => $item['wintransid'] ?? null,
                    /* 'remarks' => json_encode([
                        'cashAmount' => $validated['cashAmount'],
                        'changeAmount' => $validated['cashAmount'] - $validated['totalAmount'],
                        'partialPayment' => $item['partial_payment'] ?? 0,
                    ]), */
                ];
            }
    
            // Bulk insert sales transactions
            RboTransactionSalesTrans::insert($salesTransactions);

            $paymentMethod = strtolower($validated['selectedAR']);
            $paymentAmounts = [
                'charge' => 0,
                'gcash' => 0,
                'paymaya' => 0,
                'cash' => 0,
                'card' => 0,
                'loyaltycard' => 0,
                'foodpanda' => 0,
                'grabfood' => 0,
            ];

        // Set the amount for the selected payment method
        $paymentAmounts[$paymentMethod] = $validated['totalAmount'];

        // Calculate change amount (only applicable for cash payments)
        $changeAmount = $paymentMethod === 'cash' ? ($validated['cashAmount'] - $validated['totalAmount']) : 0;

        // Create summary record
        RboTransactionTables::create([
            'transactionid' => $transactionId,
            'linenum' => count($validated['cartItems']) + 1,
            'receiptid' => $receiptId,
            'store' => $storeId,
            'staff' => $name,
            'comment' => $validated['note'] ?? null,
            'grossamount' => collect($salesTransactions)->sum('grossamount'),
            'partialpayment' => $validated['partialpayment'],
            'discamount' => collect($salesTransactions)->sum('discamount'),
            'netamount' => collect($salesTransactions)->sum('netamount'),
            'costamount' => collect($salesTransactions)->sum('costamount'),
            'custaccount' => $validated['selectedCustomer'],
            'paymentmethod' => $validated['selectedAR'],
            'partialpayment' => $validated['partialpayment'],
            'cashamount' => $paymentMethod === 'cash' ? $validated['cashAmount'] : 0,
            'taxinclinprice' => collect($salesTransactions)->sum('taxinclinprice'),
            'netamountnotincltax' => collect($salesTransactions)->sum('netamountnotincltax'),
            'numberofitems' => count($salesTransactions),
            'window_number' => $validated['cartItems'][0]['wintransid'] ?? null,
            'currency' => 'PHP',
            'createddate' => $now,
            'charge' => $paymentAmounts['charge'],
            'gcash' => $paymentAmounts['gcash'],
            'paymaya' => $paymentAmounts['paymaya'],
            'cash' => $paymentAmounts['cash'],
            'card' => $paymentAmounts['card'],
            'loyaltycard' => $paymentAmounts['loyaltycard'],
            'foodpanda' => $paymentAmounts['foodpanda'],
            'grabfood' => $paymentAmounts['grabfood'],
            'remarks' => json_encode([
                'cashAmount' => $paymentMethod === 'cash' ? $validated['cashAmount'] : 0,
                'changeAmount' => $changeAmount,
                'totalItems' => count($validated['cartItems']),
            ]),
        ]);
    
            // Update sequence number
            DB::table('nubersequencevalues')
                /* ->where('storeid', $storeId) */
                ->update(['cartnextrec' => $nextRec]);
    
            DB::commit();
    
            $transactionSummary = (object)[
                'cash' => $paymentAmounts['cash'],
                'card' => $paymentAmounts['card'],
                'gcash' => $paymentAmounts['gcash'],
                'paymaya' => $paymentAmounts['paymaya'],
                'charge' => $paymentAmounts['charge'],
                'loyaltycard' => $paymentAmounts['loyaltycard'],
                'foodpanda' => $paymentAmounts['foodpanda'],
                'grabfood' => $paymentAmounts['grabfood'],
                'createddate' => $now,
                'receiptid' => $receiptId,
                'staff' => $name,
                'store' => $storeId,
                'note' => $validated['note'] ?? null, 
                'custaccount' => $validated['selectedCustomer'] ?? null, 
                'window_number' => $validated['cartItems'][0]['wintransid'] ?? null,
                'grossamount' => collect($salesTransactions)->sum('grossamount'),
                'taxinclinprice' => collect($salesTransactions)->sum('taxinclinprice'),
                'netamountnotincltax' => collect($salesTransactions)->sum('netamountnotincltax'),
                'partialpayment' => $validated['partialpayment'],
                'netamount' => collect($salesTransactions)->sum('netamount'),
                'cashamount' => $validated['cashAmount']
            ];
    
            $this->generateReceipt($validated['cartItems'], $transactionSummary);
    
            DB::statement('TRUNCATE TABLE carts');

            return response()->json([
                'success' => true,
                'message' => 'Order submitted successfully',
                'transactionId' => $transactionId,
                'receiptId' => $receiptId
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error submitting order:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error submitting order: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateReceipt($cartItems, $transactionSummary)
    {
        $currentDateTime = Carbon::now('Asia/Manila');

    try {
        $connector = new WindowsPrintConnector("POS-80C");
        $printer = new Printer($connector);

        // Header
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text("ELJIN CORP\n");
        $printer->setEmphasis(false);
        $printer->text("Address Line 1\n");
        $printer->text("Address Line 2\n");
        $printer->text("Tel: Your Phone Number\n");
        $printer->text("TIN: Your TIN Number\n");
        $printer->text("ACC: Your ACC Number\n");
        $printer->text("Date Issued: " . $currentDateTime->format('Y-m-d') . "\n");
        $printer->text("Contact #: Your Contact Number\n");
        $printer->text(str_repeat("-", 40) . "\n");

        // Transaction details
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Date: " . $currentDateTime->format('Y-m-d H:i:s') . "\n");
        $printer->text("Receipt #: " . str_pad($transactionSummary->receiptid, 9, '0', STR_PAD_LEFT) . "\n");
        $printer->text("Cashier: " . $transactionSummary->staff . "\n");
        $printer->text(str_repeat("-", 40) . "\n");

        // Items
        $printer->text(sprintf("%-20s %8s %3s %8s\n", "Item Name", "Price", "Qty", "Total"));
        /* foreach ($cartItems as $item) {
            $printer->text(sprintf("%-20s %8.2f %3d %8.2f\n", 
                substr($item['itemname'], 0, 20), 
                $item['price'], 
                $item['total_qty'], 
                $item['total_price']
            ));
        }
        $printer->text(str_repeat("-", 40) . "\n"); */

        // Print items with discounts and partial payments
        $totalDiscount = 0;
        $totalPartialPayment = 0;
        
        foreach ($cartItems as $item) {
            // Print item details
            $printer->text(sprintf("%-20s %8.2f %3d %8.2f\n", 
                substr($item['itemname'], 0, 20), 
                $item['price'], 
                $item['total_qty'], 
                $item['total_price']
            ));
            
            // If item has discount, print it with the discount name
            if (isset($item['discamount']) && $item['discamount'] > 0) {
                // Display discount name (discofferid) and amount
                $printer->text(sprintf(" %-20s -%8.2f\n", 
                    substr($item['discofferid'], 0, 20),  // Display discount name
                    $item['discamount']
                ));
                $totalDiscount += $item['discamount'];
            }
    
                // If item has partial payment, track it
                if (isset($item['partialpayment']) && $item['partialpayment'] > 0) {
                    $totalPartialPayment += $item['partialpayment'];
                }
            }
            $printer->text(str_repeat("-", 40) . "\n");
    
            // Print summary
            $printer->text(sprintf("Gross Amount:%28.2f\n", $transactionSummary->grossamount));
            if ($totalDiscount > 0) {
                $printer->text(sprintf("Total Discount: %26.2f\n", $totalDiscount));
            }
            $printer->text(sprintf("Net Amount:%30.2f\n", $transactionSummary->netamount));
            /* $printer->text(sprintf("VAT Amount:%30.2f\n", $transactionSummary->grossamount * 0.12)); */
            
            // Partial Payment Section
            $printer->text(str_repeat("-", 40) . "\n");
            $printer->text("PAYMENT DETAILS\n");

            $previouspayment = $transactionSummary->partialpayment;
            $printer->text(sprintf("Previous Payment:%26.2f\n", $previouspayment));
            
            // Current payment
            $currentPayment = $transactionSummary->cashamount;
            $printer->text(sprintf("Current Payment:%26.2f\n", $currentPayment));
            
            // Calculate remaining balance
            $remainingBalance = $transactionSummary->netamount - ($totalPartialPayment + $currentPayment);
            
            // Show total paid amount
            /* $totalPaidAmount = $totalPartialPayment + $currentPayment;
            $printer->text(sprintf("Total Paid Amount:%24.2f\n", $totalPaidAmount)); */
            
            // Show remaining balance if any
            if ($remainingBalance > 0) {
                $printer->text(sprintf("Remaining Balance:%24.2f\n", $remainingBalance));
            }
            
            // Only show change if this payment completes the transaction
            if ($remainingBalance <= 0 && $currentPayment > ($transactionSummary->netamount - $totalPartialPayment)) {
                $change = $currentPayment - ($transactionSummary->netamount - $totalPartialPayment);
                $printer->text(sprintf("Change:%34.2f\n", $change));
            }
            
            $printer->text(str_repeat("-", 40) . "\n");
    
            // Payment Method
            $printer->text("Payment Method:\n");
            $paymentMethods = [
                'cash' => 'Cash',
                'card' => 'Card',
                'gcash' => 'GCash',
                'paymaya' => 'PayMaya',
                'charge' => 'Charge',
                'loyaltycard' => 'Loyalty Card',
                'foodpanda' => 'foodpanda',
                'grabfood' => 'grabfood'
            ];
    
            foreach ($paymentMethods as $key => $label) {
                if (isset($transactionSummary->$key) && $transactionSummary->$key > 0) {
                    $amount = $transactionSummary->$key;
                    $printer->text(sprintf("%s:%36.2f\n", $label, $amount));
                }
            }
    
            $printer->text(str_repeat("-", 40) . "\n");
    
            // VAT Details
            /* $vatableSales = $transactionSummary->netamount / 1.12; */
            /* $printer->text(sprintf("Vatable Sales:%28.2f\n", $vatableSales));
            $printer->text(sprintf("VAT Amount:%30.2f\n", $vatableSales * 0.12)); */
            $printer->text(sprintf("Vatable Sales:%28.2f\n", $transactionSummary->netamountnotincltax));
            $printer->text(sprintf("VAT Amount:%30.2f\n", $transactionSummary->taxinclinprice));
            $printer->text(str_repeat("-", 40) . "\n");

            // Print cart note if it exists
            if (!empty($transactionSummary->note)) {
                $printer->text("Note: " . $transactionSummary->note . "\n");
                $printer->text(str_repeat("-", 40) . "\n");
            }

            // Print cart customer if it exists
            if (($transactionSummary->custaccount != 000000)) {
                $printer->text("Customer: " . $transactionSummary->custaccount . "\n");
                $printer->text(str_repeat("-", 40) . "\n");
            }
            
            // Payment Status
            $printer->text("Payment Status: " . 
                ($remainingBalance > 0 ? "PARTIAL PAYMENT\n" : "FULLY PAID\n"));
            
            $printer->text(str_repeat("-", 40) . "\n");
    
            // ID Section
            $printer->text("ID/OSCA/PWD:\n");
            $printer->text("NAME:\n");
            $printer->text("Signature:\n");
            $printer->text(str_repeat("-", 40) . "\n");
    
            // Footer
            $printer->text("This serves as your official receipt\n");
            $printer->text("This invoice/receipt shall be valid for\n");
            $printer->text("five (5) years from the date of the\n");
            $printer->text("permit to use\n");
            $printer->text(str_repeat("-", 40) . "\n");
    
            $printer->text("POS Provider: IT WARRIORS\n");
            $printer->text("ELJIN CORP.\n");
            $printer->text("ADDRESS\n");
            $printer->text(str_repeat("-", 40) . "\n");
    
            // Additional note for partial payments
            if ($remainingBalance > 0) {
                $printer->text("*** PARTIAL PAYMENT RECEIPT ***\n");
                $printer->text("Please keep this receipt for your\n");
                $printer->text("next payment transaction.\n");
                $printer->text(str_repeat("-", 40) . "\n");
            }
    
            $printer->text("Thank you for your purchase!\n");
    
            $printer->cut();
            $printer->close();
    
            \Log::info('Receipt printed successfully');
            
        } catch (\Exception $e) {
            \Log::error('Printer error: ' . $e->getMessage());
            throw new \Exception('Printer error: ' . $e->getMessage());
        }
    }

public function generateXRead()
    {
        $today = Carbon::today();
        $transactions = rbotransactiontables::whereDate('createddate', $today)->get();

        $comments = $transactions->pluck('comment')->filter()->implode(', ');

        $xReadData = [
            'comment' => $comments, 
            'partialpayment' => $transactions->sum('partial_payment'),
            'grossSales' => $transactions->sum('grossamount'),
            'netSales' => $transactions->sum('netamount'),
            'discount' => $transactions->sum('discamount'),
            'totalVATabalesales' => $transactions->sum('netamount') / 1.12, 
            'totalVAT' => ($transactions->sum('netamount') / 1.12) * .12, 
            'transactionCount' => $transactions->count(),
            'voidCount' => $transactions->where('transactionstatus', 1)->count(),
            'paymentBreakdown' => [
                'cash' => $transactions->sum('cash'),
                'card' => $transactions->sum('card'),
                'gcash' => $transactions->sum('gcash'),
                'paymaya' => $transactions->sum('paymaya'),
                'charge' => $transactions->sum('charge'),
                'loyaltycard' => $transactions->sum('loyaltycard'),
                'foodpanda' => $transactions->sum('foodpanda'),
                'grabfood' => $transactions->sum('grabfood'),
            ],
        ];

        return response()->json($xReadData);
    }

    public function printXRead(Request $request)
    {
        $xReadData = $request->input('reportData');

        $storeId = Auth::user()->storeid;
        $name = Auth::user()->name;

        try {
            $printerName = env('POS_PRINTER_NAME', 'POS-80C');
            $connector = new WindowsPrintConnector($printerName);
            /* $bluetoothAddress = env('POS_BLUETOOTH_ADDRESS', 'DC:0D:30:70:09:19');
            $connector = new BluetoothPrintConnector($bluetoothAddress); */
            $printer = new Printer($connector);

            $this->printXReadHeader($printer, $storeId, $name);
            $this->printXReadContent($printer, $xReadData);
            $this->printXReadFooter($printer, $xReadData, $storeId, $name);

            $printer->cut();
            $printer->close();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('X-READ printing failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function printXReadHeader(Printer $printer, $storeId, $name)
{
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("ELJIN CORP.\n");
    $printer->text("Address Line 1\n");
    $printer->text("Address Line 2\n");
    $printer->text("Tel: Your Phone Number\n");
    $printer->text("TIN: Your TIN Number\n");
    $printer->text("ACC: Your ACC Number\n");
    $printer->text("Date Issued: " . Carbon::now('Asia/Manila') . "\n");
    $printer->text("Contact #: Your Contact Number\n");
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("Date: " . Carbon::now('Asia/Manila') . "\n");
    /* $printer->text("Receipt #: " . str_pad(rand(1, 999999), 9, '0', STR_PAD_LEFT) . "\n"); */
    $printer->text("Store: $name\n");
    $printer->text("Cashier: $name\n");
    $printer->text(str_repeat("-", 40) . "\n");

    $printer->text("X-READ:\n");
    $printer->text(str_repeat("-", 40) . "\n");
}

private function printXReadContent(Printer $printer, $xReadData)
{
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text(sprintf("%-20s %11s\n", "Gross Amount:", number_format($xReadData['grossSales'], 2)));
    $printer->text(sprintf("%-20s %11s\n", "Discount Amount:", number_format($xReadData['discount'], 2)));
    $printer->text(sprintf("%-20s %11s\n", "Net Amount:", number_format($xReadData['netSales'], 2)));
    /* $printer->text(sprintf("%-20s %11s\n", "VAT Amount:", number_format($xReadData['totalVAT'], 2)));
    $printer->text(sprintf("%-20s %11s\n", "Amount Paid:", number_format($xReadData['grossSales'], 2))); */
    $printer->text(str_repeat("-", 40) . "\n");

    /* $printer->text(sprintf("%-20s %11s\n", "Partial Payment:", "_______________"));
    $printer->text(str_repeat("-", 40) . "\n"); */
    
    $printer->text("Payment Method:\n");
    foreach ($xReadData['paymentBreakdown'] as $method => $amount) {
        $printer->text(sprintf("%-20s %11s\n", ucfirst($method) . ":", number_format($amount, 2)));
    }
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("Tax Report:\n");
    $printer->text(str_repeat("-", 40) . "\n");
    
    $printer->text(sprintf("%-20s %11s\n", "Vatable Sales:", number_format($xReadData['totalVATabalesales'], 2)));
    $printer->text(sprintf("%-20s %11s\n", "VAT Amount:", number_format($xReadData['totalVAT'], 2)));
    $printer->text(sprintf("%-20s %11s\n", "Transaction Count:", $xReadData['transactionCount']));
    $printer->text(sprintf("%-20s %11s\n", "Void Count:", $xReadData['voidCount']));
}

private function printXReadFooter(Printer $printer, $xReadData, $storeId, $name)
{
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("ID/OSCA/PWD:\n");
    $printer->text("NAME:\n");
    $printer->text("Signature:\n");
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("This serves as your official receipt\n");
    $printer->text("This invoice/receipt shall be valid for\n");
    $printer->text("five (5) years from the date of the\n");
    $printer->text("permit to use\n");
    $printer->text(str_repeat("-", 32) . "\n");
    $printer->text("POS Provider: IT WARRIORS\n");
    $printer->text("ELJIN CORP.\n");
    $printer->text("ADDRESS\n");
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("Thank you for your purchase!\n");
}

public function getDailyJournal()
    {
        // Fetch today's transactions
        $transactions = rbotransactiontables::whereDate('createddate', today())->orderByDesc('receiptid')->get();
        return response()->json($transactions);
    }

    public function getTransactionSales($transactionid)
    {
        $salesTrans = rbotransactionsalestrans::where('transactionid', $transactionid)->get();
        return response()->json($salesTrans);
    }

    public function reprintReceipt(Request $request)
    {
        try {
            $transactionId = $request->input('transactionId');
            $transaction = rbotransactiontables::findOrFail($transactionId);
            $salesTransactions = rbotransactionsalestrans::where('transactionid', $transactionId)->get();
    
            $printerName = env('POS_PRINTER_NAME', 'IT-AKLYDE\POS-80C');
            $connector = new WindowsPrintConnector($printerName);
            $printer = new Printer($connector);
    
            // Print receipt content
            $this->printReceiptContent($printer, $transaction, $salesTransactions);
    
            $printer->cut();
            $printer->close();
    
            return response()->json([
                'success' => true,
                'message' => 'Receipt reprinted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reprint receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function printReceiptContent(Printer $printer, $transaction, $salesTransactions)
{
    $currentDateTime = Carbon::now('Asia/Manila');

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setEmphasis(true);
    $printer->text("ELJIN CORP\n");
    $printer->setEmphasis(false);
    $printer->text("Address Line 1\n");
    $printer->text("Address Line 2\n");
    $printer->text("Tel: Your Phone Number\n");
    $printer->text("TIN: Your TIN Number\n");
    $printer->text("ACC: Your ACC Number\n");
    $printer->text("Date Issued: " . $currentDateTime->format('Y-m-d H:i:s') . "\n");
    $printer->text("Contact #: Your Contact Number\n");
    $printer->text(str_repeat("-", 40) . "\n");

    $printer->text("Date: " . $currentDateTime->format('Y-m-d H:i:s') . "\n");
    $printer->text("Receipt #: " . $transaction->receiptid . "\n");
    $printer->text("Cashier: " . $transaction->staff . "\n");

    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("REPRINT:\n");
    $printer->text(str_repeat("-", 40) . "\n");

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text(sprintf("%-20s %8s %3s %8s\n", "Item Name", "Price", "Qty", "Total"));
    $printer->text(str_repeat("-", 40) . "\n");

    foreach ($salesTransactions as $item) {
        $printer->text(sprintf(
            "%-20s %8.2f %3d %8.2f\n",
            substr($item->itemname, 0, 20),
            $item->price,
            $item->qty,
            $item->price * $item->qty
        ));
    }

    $printer->text(str_repeat("-", 40) . "\n");
    $printer->setEmphasis(true);
    $printer->text(sprintf("%-25s %14.2f\n", "Gross Amount:", $transaction->grossamount));
    $printer->text(sprintf("%-25s %14.2f\n", "Total Discount:", $transaction->discamount));
    $printer->text(sprintf("%-25s %14.2f\n", "Net Amount:", $transaction->netamount));
    $printer->setEmphasis(false);

    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("PAYMENT DETAILS:\n");
    $printer->text(sprintf("%-25s %14.2f\n", "Previous Payment:", $transaction->partialpayment));
    $printer->text(sprintf("%-25s %14.2f\n", "Current Payment:", $transaction->cashamount));
    $printer->text(sprintf("%-25s %14.2f\n", "Change:", $transaction->cashamount - $transaction->netamount));

    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("Payment Method:\n");
    $paymentMethods = ['cash', 'gcash', 'paymaya', 'card', 'loyaltycard'];
    foreach ($paymentMethods as $method) {
        if ($transaction->$method > 0) {
            $printer->text(sprintf("%-20s %11s\n", ucfirst($method) . ":", number_format($transaction->$method, 2)));
        }
    }
    $printer->text(str_repeat("-", 40) . "\n");

    $printer->text(sprintf("%-20s %11s\n", "VAT Amount:", number_format($transaction->taxinclinprice, 2)));
    $printer->text(sprintf("%-20s %11s\n", "Vatable Sales:", number_format($transaction->netamountnotincltax, 2)));
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("Payment Status: " . "FULLY PAID\n");

    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("ID/OSCA/PWD:\n");
    $printer->text("NAME:\n");
    $printer->text("Signature:\n");
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("This serves as your official receipt\n");
    $printer->text("This invoice/receipt shall be valid for\n");
    $printer->text("five (5) years from the date of the\n");
    $printer->text("permit to use\n");
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("POS Provider: IT WARRIORS\n");
    $printer->text("ELJIN CORP.\n");
    $printer->text("ADDRESS\n");
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("Thank you for your purchase!\n");
}

public function returnTransaction(Request $request)
{
    try {
        DB::beginTransaction();

        $originalTransactionId = $request->transactionId;
        $returnItems = $request->returnItems;
        $totalReturnAmount = $request->totalReturnAmount;

        $originalTransaction = rbotransactiontables::findOrFail($originalTransactionId);

        $fieldsToNegate = ['grossamount'];

        foreach ($fieldsToNegate as $field) {
            $originalTransaction->$field -= $totalReturnAmount;
        }

        $originalTransaction->costamount = $originalTransaction->grossamount / 1.12;
        $totalReturnDiscAmount = collect($returnItems)->sum('returnDiscAmount');
        $originalTransaction->discamount = $totalReturnDiscAmount;
        $originalTransaction->netamount =  $originalTransaction->grossamount -$originalTransaction->discamount;
        $originalTransaction->transactionstatus = 1;
        $originalTransaction->custaccount = null;
        $originalTransaction->numberofitems = count($returnItems);
        $originalTransaction->refundreceiptid = $originalTransaction->receiptid;
        $originalTransaction->refunddate = now();
        $originalTransaction->returnedby = auth()->user()->name;

        $originalTransaction->taxinclinprice = $originalTransaction->grossamount / 1.12;
        $originalTransaction->netamountnotincltax = ($originalTransaction->grossamount / 1.12) * 0.12;

        $paymentFields = ['cash', 'gcash', 'paymaya', 'card', 'loyaltycard', 'charge'];

        foreach ($paymentFields as $field) {
            if ($originalTransaction->$field <= 0) {
                $originalTransaction->$field = 0.00; 
            } else {
                $originalTransaction->$field = $originalTransaction->netamount ?? 0.00;
            }
        }

        $originalTransaction->save();

        if (!is_array($returnItems)) {
            throw new \Exception('$returnItems is not an array');
        }

        foreach ($returnItems as $item) {
            if (!isset($item['linenum'], $item['itemid'], $item['price'], $item['returnQty'])) {
                throw new \Exception('Invalid item structure in $returnItems');
            }

            try {
                $originalSalesTrans = rbotransactionsalestrans::where([
                    'transactionid' => $originalTransactionId,
                    'linenum' => $item['linenum']
                ])->firstOrFail();

                DB::table('rbotransactionsalestrans')
                    ->where('transactionid', $originalTransactionId)
                    ->where('linenum', $item['linenum'])
                    ->update([
                        'price' => -abs($item['price']),
                        'netprice' => -abs($item['price']),
                        'qty' => -abs($item['returnQty']),
                        'netamount' => -abs($item['price'] * $item['returnQty']),
                        'grossamount' => -abs($item['price']),
                        'returnqty' => abs($item['returnQty']),
                        'refunddate' => now(),
                        'returnedby' => auth()->user()->name
                    ]);

            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                throw new \Exception("Original transaction not found for linenum: {$item['linenum']}");
            } catch (\Exception $e) {
                throw new \Exception("Error processing return item: " . $e->getMessage());
            }
        }

        DB::commit();

        // Auto-printing
        $this->printReturnReceipt($originalTransaction, $returnItems);

        return response()->json([
            'success' => true,
            'message' => 'Return transaction processed successfully'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Failed to process return: ' . $e->getMessage()
        ], 500);
    }
}

private function printReturnReceipt($transaction, $returnItems)
{
    try {
        $connector = new WindowsPrintConnector("POS-80C");
        $printer = new Printer($connector);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text("Return Receipt\n");
        $printer->setEmphasis(false);
        $printer->feed();

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Date: " . now()->format('Y-m-d H:i:s') . "\n");
        $printer->text("Transaction ID: " . $transaction->transactionid . "\n");
        $printer->text("Refund Receipt ID: " . $transaction->refundreceiptid . "\n");
        $printer->text("Returned By: " . $transaction->returnedby . "\n");
        $printer->feed();

        $printer->text("Returned Items:\n");
        foreach ($returnItems as $item) {
            $printer->text($item['itemid'] . " x " . $item['returnQty'] . " @ " . number_format($item['price'], 2) . "\n");
        }
        $printer->feed();

        /* $printer->setEmphasis(true);
        $printer->text("Total Return Amount: " . number_format($transaction->netamount, 2) . "\n");
        $printer->setEmphasis(false); */

        $printer->feed(2);
        $printer->text("Thank you for your business!\n");

        $printer->cut();
        $printer->close();
    } catch (\Exception $e) {
        // Log the error, but don't stop the transaction process
        \Log::error("Printer error: " . $e->getMessage());
    }
}

public function tender(Request $request)
    {
        // Validate incoming request
        /* $request->validate([
            'total' => 'required|numeric',
            'bills' => 'array',
            'coins' => 'array',
            'ar' => 'array',
        ]); */

        $sumCash = DB::table('rbotransactiontables')
        ->whereDate('createddate', today())
        ->sum('cash');

        // Extract data from the request
        $tenderData = $request->only('total', 'bills', 'coins', 'ar');

        // Here, you would typically save $tenderData to your database

        // Print the tender declaration
        $this->printTenderDeclaration($tenderData, $sumCash);

        return response()->json(['success' => true, 'message' => 'Tender declaration submitted successfully!']);
    }

    private function printTenderDeclaration($tenderData, $sumCash)
    {
    // Set the printer name (ensure this matches your printer configuration)
    $connector = new WindowsPrintConnector("POS-80C");
    $printer = new Printer($connector);

    // Initialize totals
    $totalBills = 0;
    $totalCoins = 0;
    $totalCash = 0;
    $totalAR = 0;

    // Print the receipt
    try {
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Tender Declaration\n");
        $printer->text("================================\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Total: " . number_format($tenderData['total'], 2) . "\n");
        $printer->text("CASH: Short/Over: " . number_format($tenderData['total'] - $sumCash, 2) . "\n");
        
        // Print bills
        $printer->text("================================\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Bills:\n");
        $printer->text("================================\n");
        foreach ($tenderData['bills'] as $amount => $count) {
            if ($count > 0) {
                $subtotal = $amount * $count;
                $printer->text("$amount x $count = " . number_format($subtotal, 2) . "\n");
                $totalBills += $subtotal; // Calculate total bills
            }
        }

        // Print coins
        $printer->text("================================\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Coins:\n");
        $printer->text("================================\n");
        foreach ($tenderData['coins'] as $amount => $count) {
            if ($count > 0) {
                $subtotal = $amount * $count;
                $printer->text("$amount x $count = " . number_format($subtotal, 2) . "\n");
                $totalCoins += $subtotal; // Calculate total coins
            }
        }

        // Print totals for bills and coins
        $printer->text("================================\n");
        $printer->text("Total Bills: " . number_format($totalBills, 2) . "\n");
        $printer->text("Total Coins: " . number_format($totalCoins, 2) . "\n");
        $printer->text("================================\n");
        $printer->text("Total Cash: " . number_format($totalBills + $totalCoins, 2) . "\n");
        
        // Print alternative methods
        $printer->text("================================\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Digital Transaction:\n");
        $printer->text("================================\n");
        foreach ($tenderData['ar'] as $method => $amount) {
            if ($amount > 0) {
                $artotal = $amount;
                $printer->text("$method: $amount\n");
                $totalAR += $artotal;
            }
        }

        $printer->text("================================\n");
        $printer->text("Total DT: " . number_format($totalAR, 2) . "\n");

        $printer->text("================================\n");
        $printer->text("Thank you\n");
        $printer->cut();
        $printer->close();
    } catch (\Exception $e) {
        // Handle any printing exceptions
        error_log("Printing error: " . $e->getMessage());
    }
}


public function generateZRead()
    {
        $today = Carbon::today();
        $transactions = rbotransactiontables::whereDate('createddate', $today)->get();

        $comments = $transactions->pluck('comment')->filter()->implode(', ');

        $zReadData = [
            'comment' => $comments, 
            'partialpayment' => $transactions->sum('partial_payment'),
            'grossSales' => $transactions->sum('grossamount'),
            'netSales' => $transactions->sum('netamount'),
            'discount' => $transactions->sum('discamount'),
            'totalVATabalesales' => $transactions->sum('netamount') / 1.12, 
            'totalVAT' => ($transactions->sum('netamount') / 1.12) * .12, 
            'transactionCount' => $transactions->count(),
            'voidCount' => $transactions->where('transactionstatus', 1)->count(),
            'paymentBreakdown' => [
                'cash' => $transactions->sum('cash'),
                'card' => $transactions->sum('card'),
                'gcash' => $transactions->sum('gcash'),
                'paymaya' => $transactions->sum('paymaya'),
                'charge' => $transactions->sum('charge'),
                'loyaltycard' => $transactions->sum('loyaltycard'),
                'foodpanda' => $transactions->sum('foodpanda'),
                'grabfood' => $transactions->sum('grabfood'),
            ],
        ];

        return response()->json($zReadData);
    }

    public function printZRead(Request $request)
    {
        $zReadData = $request->input('reportData');

        $storeId = Auth::user()->storeid;
        $name = Auth::user()->name;

        try {
            $printerName = env('POS_PRINTER_NAME', 'POS-80C');
            $connector = new WindowsPrintConnector($printerName);
            $printer = new Printer($connector);

            $this->printZReadHeader($printer, $storeId, $name);
            $this->printZReadContent($printer, $zReadData);
            $this->printZReadFooter($printer, $zReadData, $storeId, $name);

            $printer->cut();
            $printer->close();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('X-READ printing failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function printZReadHeader(Printer $printer, $storeId, $name)
{
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("ELJIN CORP.\n");
    $printer->text("Address Line 1\n");
    $printer->text("Address Line 2\n");
    $printer->text("Tel: Your Phone Number\n");
    $printer->text("TIN: Your TIN Number\n");
    $printer->text("ACC: Your ACC Number\n");
    $printer->text("Date Issued: " . Carbon::now('Asia/Manila') . "\n");
    $printer->text("Contact #: Your Contact Number\n");
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("Date: " . Carbon::now('Asia/Manila') . "\n");
    /* $printer->text("Receipt #: " . str_pad(rand(1, 999999), 9, '0', STR_PAD_LEFT) . "\n"); */
    $printer->text("Store: $name\n");
    $printer->text("Cashier: $name\n");
    $printer->text(str_repeat("-", 40) . "\n");

    $printer->text("Z-READ:\n");
    $printer->text(str_repeat("-", 40) . "\n");
}

private function printZReadContent(Printer $printer, $zReadData)
{
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text(sprintf("%-20s %11s\n", "Gross Amount:", number_format($zReadData['grossSales'], 2)));
    $printer->text(sprintf("%-20s %11s\n", "Discount Amount:", number_format($zReadData['discount'], 2)));
    $printer->text(sprintf("%-20s %11s\n", "Net Amount:", number_format($zReadData['netSales'], 2)));
    $printer->text(sprintf("%-20s %11s\n", "VAT Amount:", number_format($zReadData['totalVAT'], 2)));
    $printer->text(sprintf("%-20s %11s\n", "Amount Paid:", number_format($zReadData['grossSales'], 2)));
    $printer->text(str_repeat("-", 40) . "\n");

    /* $printer->text(sprintf("%-20s %11s\n", "Partial Payment:", "_______________"));
    $printer->text(str_repeat("-", 40) . "\n"); */
    
    $printer->text("Payment Method:\n");
    foreach ($zReadData['paymentBreakdown'] as $method => $amount) {
        $printer->text(sprintf("%-20s %11s\n", ucfirst($method) . ":", number_format($amount, 2)));
    }

    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("Tax Report\n");
    $printer->text(str_repeat("-", 40) . "\n");
    
    $printer->text(sprintf("%-20s %11s\n", "Vatable Sales:", number_format($zReadData['totalVATabalesales'], 2)));
    $printer->text(sprintf("%-20s %11s\n", "VAT Amount:", number_format($zReadData['totalVAT'], 2)));
    $printer->text(sprintf("%-20s %11s\n", "Transaction Count:", $zReadData['transactionCount']));
    $printer->text(sprintf("%-20s %11s\n", "Void Count:", $zReadData['voidCount']));
}

private function printZReadFooter(Printer $printer, $zReadData, $storeId, $name)
{
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("ID/OSCA/PWD:\n");
    $printer->text("NAME:\n");
    $printer->text("Signature:\n");
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("This serves as your official receipt\n");
    $printer->text("This invoice/receipt shall be valid for\n");
    $printer->text("five (5) years from the date of the\n");
    $printer->text("permit to use\n");
    $printer->text(str_repeat("-", 32) . "\n");
    $printer->text("POS Provider: IT WARRIORS\n");
    $printer->text("ELJIN CORP.\n");
    $printer->text("ADDRESS\n");
    $printer->text(str_repeat("-", 40) . "\n");
    $printer->text("Thank you for your purchase!\n");
}

/* public function updatePriceAndQuantity(Request $request)
{
    try {
        \Log::info('Update Price and Quantity Request:', $request->all());

        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.itemid' => 'required|string', 
            'items.*.price' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            \Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        $updatedItems = [];
        foreach ($request->items as $itemData) {
            \Log::info('Processing item:', $itemData);

            try {
                $cart = carts::where('itemid', $itemData['itemid'])->first();
                
                if (!$cart) {
                    throw new \Exception("Cart item not found with ID: {$itemData['itemid']}");
                }

                $grossAmount = $itemData['price'] * $itemData['qty'];
                $netAmountNotInclTax = $grossAmount / 1.12;
                $taxAmount = $netAmountNotInclTax * 0.12;
                $currentDiscount = $cart->discount ?? 0;

                $cart->update([
                    'price' => $itemData['price'],
                    'qty' => $itemData['qty'],
                    'costamount' => $netAmountNotInclTax,
                    'grossamount' => $grossAmount,
                    'netamount' => $grossAmount - $currentDiscount,
                    'netamountnotincltax' => $netAmountNotInclTax,
                    'taxinclinprice' => $taxAmount
                ]);

                $updatedItems[] = $cart->fresh();

            } catch (\Exception $itemError) {
                DB::rollBack();
                \Log::error('Error processing item:', [
                    'item' => $itemData,
                    'error' => $itemError->getMessage(),
                    'trace' => $itemError->getTraceAsString()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error processing item: ' . $itemError->getMessage()
                ], 500);
            }
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Items updated successfully',
            'updatedItems' => $updatedItems
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        
        \Log::error('Update failed:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'details' => [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ], 500);
    }
} */

    /* private const TAX_RATE = 0.12;
    private const GROSS_DIVISOR = 1.12;

    public function updatePriceAndQuantity(Request $request)
    {
        Log::info('Update Price and Quantity Request:', $request->all());

        try {
            $validator = $this->validateRequest($request);
            
            if ($validator->fails()) {
                Log::warning('Validation failed:', $validator->errors()->toArray());
                return $this->validationErrorResponse($validator);
            }

            return DB::transaction(function () use ($request) {
                $updatedItems = $this->processItems($request->items);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Items updated successfully',
                    'updatedItems' => $updatedItems
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Update failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return $this->errorResponse($e);
        }
    }

    private function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.itemid' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:0'
        ]);
    }

    private function processItems(array $items)
    {
        $updatedItems = [];

        foreach ($items as $itemData) {
            $cart = Carts::where('itemid', $itemData['itemid'])->firstOrFail();
            
            $calculations = $this->calculateAmounts(
                $itemData['price'],
                $itemData['qty'],
                $cart->discount ?? 0
            );
            
            $cart->update([
                'price' => $itemData['price'],
                'qty' => $itemData['qty'],
                'costamount' => $calculations['netAmountNotInclTax'],
                'grossamount' => $calculations['grossAmount'],
                'netamount' => $calculations['netAmount'],
                'netamountnotincltax' => $calculations['netAmountNotInclTax'],
                'taxinclinprice' => $calculations['taxAmount']
            ]);

            $updatedItems[] = $cart->fresh();
        }

        return $updatedItems;
    }

    private function calculateAmounts(float $price, int $quantity, float $discount): array
    {
        $grossAmount = $price * $quantity;
        $netAmountNotInclTax = $grossAmount / self::GROSS_DIVISOR;
        
        return [
            'grossAmount' => $grossAmount,
            'netAmountNotInclTax' => $netAmountNotInclTax,
            'taxAmount' => $netAmountNotInclTax * self::TAX_RATE,
            'netAmount' => $grossAmount - $discount
        ];
    }

    private function validationErrorResponse($validator)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    } */

private const TAX_RATE = 0.12;
private const GROSS_DIVISOR = 1.12; // If 1.12 is for gross-to-net ratio

public function updatePriceAndQuantity(Request $request)
{
    Log::info('Update Price and Quantity Request:', $request->all());

    try {
        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return $this->validationErrorResponse($validator);
        }

        return DB::transaction(function () use ($request) {
            $updatedItems = $this->processItems($request->items);

            return response()->json([
                'status' => 'success',
                'message' => 'Items updated successfully',
                'updatedItems' => $updatedItems
            ]);
        });

    } catch (\Exception $e) {
        Log::error('Update failed:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);

        return $this->errorResponse($e);
    }
}

private function validateRequest(Request $request)
{
    return Validator::make($request->all(), [
        'items' => 'required|array',
        'items.*.itemid' => 'required|string',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.qty' => 'required|integer|min:0'
    ]);
}

private function processItems(array $items)
{
    $updatedItems = [];
    foreach ($items as $itemData) {
        $cart = Carts::where('itemid', $itemData['itemid'])->firstOrFail();
        
        // Set default values and validate discount parameters
        $discType = $itemData['disctypes'] ?? 'NONE';
        $discParameter = isset($itemData['discparameter']) ? (float)$itemData['discparameter'] : 0;
        $price = (float)($itemData['price'] ?? 0);
        $quantity = (int)($itemData['qty'] ?? 0);
        
        // Calculate gross amount first
        $grossAmount = $price * $quantity;
        
        // Calculate discount amount based on discount type
        $discamount = 0; // Default to no discount
        
        switch ($discType) {
            case 'FIXEDTOTAL':
                $discamount = min($grossAmount, $discParameter);
                break;
            case 'FIXED':
                $discamount = min($grossAmount, $quantity * $discParameter);
                break;
            case 'PERCENTAGE':
                $percentage = $discParameter / 100; // Convert percentage to decimal
                $discamount = $grossAmount * $percentage;
                break;
            case 'NONE':
            default:
                $discamount = 0;
        }
        
        // Ensure discount doesn't exceed gross amount
        $discamount = min($discamount, $grossAmount);
        
        // Calculate amounts after applying discount
        $calculations = $this->calculateAmounts(
            $price,
            $quantity,
            $discamount
        );
        
        $cart->update([
            'price' => $price,
            'qty' => $quantity,
            'discamount' => $discamount,
            'costamount' => $calculations['netAmountNotInclTax'],
            'grossamount' => $calculations['grossAmount'],
            'netamount' => $calculations['netAmount'],
            'netamountnotincltax' => $calculations['netAmountNotInclTax'],
            'taxinclinprice' => $calculations['taxAmount']
        ]);
        
        $updatedItems[] = $cart->fresh();
    }
    return $updatedItems;
}

private function calculateAmounts(float $price, int $quantity, float $discount): array
{
    $grossAmount = $price;
    $netAmount = $grossAmount - $discount;
    $netAmountNotInclTax = $netAmount / (1 + self::TAX_RATE);
    $taxAmount = $netAmount - $netAmountNotInclTax;
    
    return [
        'grossAmount' => $grossAmount,
        'netAmount' => $netAmount,
        'netAmountNotInclTax' => $netAmountNotInclTax,
        'taxAmount' => $taxAmount,
    ];
}

private function validationErrorResponse($validator)
{
    return response()->json([
        'status' => 'error',
        'message' => 'Validation failed',
        'errors' => $validator->errors()
    ], 422);
}

}

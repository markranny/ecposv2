<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\carts;
use Exception;
use App\Models\posperiodicdiscounts;
use App\Models\posperiodicdiscountlines;
use App\Models\posmmlinegroups;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use Illuminate\Http\Request;

class MixAndMatchController extends Controller
{
    

    /* public function getDiscounts()
{
    try {
        $discounts = Posperiodicdiscounts::with(['lineGroups.discountLines'])
            ->where('status', 1)
            ->get();

        $items = DB::table('inventtablemodules as a')
            ->select(
                'a.ITEMID as itemid',
                'c.Activeondelivery as Activeondelivery',
                'b.itemname as itemname',
                'c.itemgroup as itemgroup',
                'c.itemdepartment as specialgroup',
                'c.production as production',
                'c.moq as moq',
                DB::raw('ROUND(FORMAT(a.priceincltax, "N2"), 2) as price'),
                DB::raw('CAST(a.price as float) as cost'),
                DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
            )
            ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
            ->where('c.itemdepartment', '=', 'REGULAR PRODUCT') 
            ->get()
            ->keyBy('itemid'); 

        Log::info('Fetched Discounts:', $discounts->toArray());

        $transformedDiscounts = $discounts->map(function ($discount) use ($items) {
            return [
                'id' => $discount->offerid,
                'description' => $discount->description,
                'discounttype' => $discount->discounttype,
                'dealpricevalue'=> $discount->dealpricevalue,
                'discountpctvalue'=> $discount->discountpctvalue,
                'discountamountvalue'=> $discount->discountamountvalue,
                'line_groups' => $discount->lineGroups->map(function ($group) use ($items) {
                    $lines = $group->discountLines; 
                    Log::info('Group:', ['linegroup' => $group->linegroup, 'lines' => $lines->toArray()]); 
                    
                    return [
                        'linegroup' => $group->linegroup,
                        'description' => $group->description,
                        'noofitemsneeded' => $group->noofitemsneeded,
                        'discount_lines' => $lines->map(function ($line) use ($items) {
                            $itemData = $items->get($line->itemid);
                            
                            return [
                                'id' => $line->id,
                                'itemid' => $itemData ? $itemData->itemname : null,
                                'disctype' => $line->disctype,
                                'dealpriceordiscpct' => ($line->dealpriceordiscpct == 0 ) ? $itemData->price : 
                                                        ($itemData ? $itemData->price - $line->dealpriceordiscpct : null),
                                'linegroup' => $line->linegroup,
                                'qty' => $line->qty,
                                'itemData' => $itemData ? $itemData : null,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ];
        })->toArray();

        Log::info('Discounts Response:', $transformedDiscounts);

        return response()->json($transformedDiscounts);
    } catch (\Exception $e) {
        Log::error('Error in getDiscounts:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => 'Failed to fetch discounts'], 500);
    }
} */

public function getDiscounts()
{
    try {
        // Define query for items once to avoid repetition
        $itemsQuery = DB::table('inventtablemodules as a')
            ->select([
                'a.ITEMID as itemid',
                'c.Activeondelivery',
                'b.itemname',
                'c.itemgroup',
                'c.itemdepartment as specialgroup',
                'c.production',
                'c.moq',
                DB::raw('ROUND(FORMAT(a.priceincltax, "N2"), 2) as price'),
                DB::raw('CAST(a.price as float) as cost'),
                DB::raw("COALESCE(d.ITEMBARCODE, 'N/A') as barcode")
            ])
            ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE');
            /* ->where('c.itemdepartment', '=', 'REGULAR PRODUCT'); */

        // Eager load relationships and cache items
        $items = $itemsQuery->get()->keyBy('itemid');
        
        // Log items for debugging
        Log::info('Fetched items:', [
            'count' => $items->count(),
            'item_ids' => $items->keys()->toArray()
        ]);

        $discounts = Posperiodicdiscounts::with(['lineGroups.discountLines'])
            ->where('status', 1)
            ->get();

        $transformedDiscounts = $discounts->map(function ($discount) use ($items) {
            return $this->transformDiscount($discount, $items);
        })->values()->all();

        return response()->json($transformedDiscounts);

    } catch (\Exception $e) {
        Log::error('Error in getDiscounts', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Failed to fetch discounts',
            'message' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

/**
 * Transform a single discount record with its relationships
 *
 * @param Posperiodicdiscounts $discount
 * @param Collection $items
 * @return array
 */
private function transformDiscount($discount, $items)
{
    return [
        'id' => $discount->offerid,
        'description' => $discount->description,
        'discounttype' => $discount->discounttype,
        'dealpricevalue' => $discount->dealpricevalue,
        'discountpctvalue' => $discount->discountpctvalue,
        'discountamountvalue' => $discount->discountamountvalue,
        'line_groups' => $this->transformLineGroups($discount->lineGroups, $items)
    ];
}

/**
 * Transform line groups for a discount
 *
 * @param Collection $lineGroups
 * @param Collection $items
 * @return array
 */
private function transformLineGroups($lineGroups, $items)
{
    return $lineGroups->map(function ($group) use ($items) {
        // Log the group and its lines for debugging
        Log::debug('Processing line group', [
            'linegroup' => $group->linegroup,
            'items_count' => $group->discountLines->count()
        ]);

        return [
            'linegroup' => $group->linegroup,
            'description' => $group->description,
            'noofitemsneeded' => $group->noofitemsneeded,
            'discount_lines' => $this->transformDiscountLines($group->discountLines, $items)
        ];
    })->all();
}

/**
 * Transform discount lines with item data
 *
 * @param Collection $lines
 * @param Collection $items
 * @return array
 */
private function transformDiscountLines($lines, $items)
{
    return $lines->map(function ($line) use ($items) {
        $itemData = $items->get($line->itemid);
        
        // Log missing items for debugging
        if (!$itemData) {
            Log::warning('Missing item data for discount line', [
                'itemid' => $line->itemid,
                'linegroup' => $line->linegroup
            ]);
        }

        return [
            'id' => $line->id,
            'itemid' => $line->itemid,
            'itemname' => $itemData ? $itemData->itemname : 'Item Not Found',
            'disctype' => $line->disctype,
            'dealpriceordiscpct' => $this->calculateDiscountPrice($line, $itemData),
            'linegroup' => $line->linegroup,
            'qty' => $line->qty,
            'itemData' => $itemData ? [
                'price' => $itemData->price,
                'cost' => $itemData->cost,
                'barcode' => $itemData->barcode,
                'itemgroup' => $itemData->itemgroup,
                'specialgroup' => $itemData->specialgroup,
                'production' => $itemData->production,
                'moq' => $itemData->moq,
            ] : null,
        ];
    })->all();
}

/**
 * Calculate the final discount price
 *
 * @param mixed $line
 * @param mixed|null $itemData
 * @return float|null
 */
private function calculateDiscountPrice($line, $itemData = null)
{
    // If no item data is found, return the original dealpriceordiscpct
    if (!$itemData) {
        return (float) $line->dealpriceordiscpct;
    }

    // If dealpriceordiscpct is 0, return the item's price
    if ($line->dealpriceordiscpct == 0) {
        return (float) $itemData->price;
    }

    // Otherwise calculate the discounted price
    return (float) ($itemData->price - $line->dealpriceordiscpct);
}

public function saveOnTheCarts(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'cart' => 'required|array',
            'cart.*.itemid' => 'required|string',
            'cart.*.qty' => 'required|numeric|min:1',
            'cart.*.price' => 'required|numeric|min:0',
            'metadata.discountInfo' => 'required|array',
            'metadata.totals' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $cartItems = $request->input('cart');
            $metadata = $request->input('metadata');
            $currentDateTime = now();
            $store = auth()->user()->store ?? '';  
            $staff = auth()->user()->name ?? '';    
            $storeId = Auth::user()->storeid;
            $userId = Auth::user()->id;

            foreach ($cartItems as $item) {
                $inventory = DB::table('inventtablemodules as a')
                    ->select(
                        'a.ITEMID as itemid',
                        'c.Activeondelivery as Activeondelivery',
                        'b.itemname as itemname',
                        'c.itemgroup as itemgroup',
                        'c.itemdepartment as specialgroup',
                        'c.production as production',
                        'c.moq as moq',
                        DB::raw('ROUND(FORMAT(a.priceincltax, "N2"), 2) as price'),
                        DB::raw('CAST(a.price as float) as cost'),
                        DB::raw("CASE WHEN d.ITEMBARCODE <> '' THEN d.itembarcode ELSE 'N/A' END as barcode")
                    )
                    ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
                    ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
                    ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
                    ->where('a.ITEMID', $item['itemid'])
                    ->first();

                if (!$inventory) {
                    throw new Exception("Inventory not found for item: {$item['itemid']}");
                }

                $discountAmount = 0;
                $netAmount = $inventory->price * $item['qty'];
                $totalPrice = array_sum(array_column($cartItems, 'price'));

                // Calculate discount based on discount type
                if ($metadata['discountInfo']['discounttype'] == '0') {
                    $discountAmount = ($totalPrice - $metadata['discountInfo']['dealpricevalue']) / count($cartItems); 
                } elseif ($metadata['discountInfo']['discounttype'] == '1') {
                    $discountAmount = ($netAmount * ($metadata['discountInfo']['discountpctvalue'] ?? 0)) / 100;
                } elseif ($metadata['discountInfo']['discounttype'] == '2') {
                    $totalQuantity = array_sum(array_column($cartItems, 'qty'));
                    $discountAmount = ($metadata['discountInfo']['discountamountvalue'] ?? 0) / $totalQuantity;
                    
                    // Round off the discount amount to 13 decimal places
                    $discountAmount = round($discountAmount, 13);
                
                
                
                }else{
                    if(($inventory->price * $item['qty']) - $item['price'] === $inventory->price){
                        $discountAmount = null;
                    }else{
                        $discountAmount = ($inventory->price * $item['qty']) - $item['price'];
                    }
                    
                }

                $netAmount -= $discountAmount;

                $nextRec = DB::table('nubersequencevalues')
                    ->where('storeid', $storeId)
                    ->lockForUpdate()
                    ->value('cartnextrec');

                $cartNextRec = $nextRec !== null ? (int)$nextRec + 1 : 1;

                $cartId = $userId . str_pad($cartNextRec, 8, '0', STR_PAD_LEFT);

                $cart = new Carts([
                    'cartid' =>$cartId ,
                    'itemid' => $inventory->itemid,
                    'itemname' => $inventory->itemname,
                    'itemgroup' => $inventory->itemgroup,
                    'price' => $inventory->price,
                    'netprice' => $inventory->price / 1.12,
                    'qty' => $item['qty'],
                    'discamount' => $discountAmount,
                    'costamount' => ($inventory->price * $item['qty']) / 1.12,
                    'netamount' => $netAmount,
                    'grossamount' => $inventory->price * $item['qty'],
                    'store' => $storeId,
                    'staff' => $staff,
                    'custdiscamount' => '0.00',
                    'discofferid' => $metadata['discountInfo']['description'],
                    /* 'linedscamount' => $item['linedscamount'],
                    'linediscpct' => $item['linediscpct'], */
                    'custdiscamount' => $item['custdiscamount'] ?? 0,
                    /* 'unit' => 'PCS', */
                    'taxinclinprice' => ($inventory->price * $item['qty']) / 1.12,
                    'netamountnotincltax' => ($inventory->price * $item['qty']) * .12,
                    'createddate' => $currentDateTime,
                    'currency' => 'PHP'
                ]);
                $cart->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Cart items saved successfully',
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            \Log::error('Cart save error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save cart items: ' . $e->getMessage()
            ], 500);
        }

    } catch (Exception $e) {
        \Log::error('Cart validation error: ' . $e->getMessage(), [
            'request' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid request data: ' . $e->getMessage()
        ], 400);
    }
}



}

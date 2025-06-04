<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\discounts;
use App\Models\carts;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = discounts::all();
        return response()->json($discounts);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'DISCOFFERNAME' => 'required|string|max:255',
            'PARAMETER' => 'required|string|max:255',
            'discountsTYPE' => 'required|string|max:255',
        ]);

        $discounts = discounts::create($validatedData);
        return response()->json($discounts, 201);
    }

    public function update(Request $request, discounts $discounts)
    {
        $validatedData = $request->validate([
            'DISCOFFERNAME' => 'string|max:255',
            'PARAMETER' => 'string|max:255',
            'discountsTYPE' => 'string|max:255',
        ]);

        $discounts->update($validatedData);
        return response()->json($discounts);
    }

    public function destroy(discounts $discounts)
    {
        $discounts->delete();
        return response()->json(null, 204);
    }

    public function updateCartDiscount(Request $request)
{
    DB::beginTransaction();

    try {
        $validatedData = $request->validate([
            'items' => 'required|array',
            'items.*.itemid' => 'required',
            'items.*.discamount' => 'required|numeric',
            'discount' => 'required|array',
            'discount.PARAMETER' => 'required|numeric',
            'discount.DISCOFFERNAME' => 'required|string',
            'discount.DISCOUNTTYPE' => 'required|string',
        ]);

        $updatedItems = [];
        foreach ($validatedData['items'] as $item) {
            $cartItem = Carts::where('itemid', $item['itemid'])->first();
            
            if ($cartItem) {
                $discountAmount = $this->calculateDiscountAmount($cartItem, $item['discamount'], $validatedData['discount']['DISCOUNTTYPE']);
                
                $costamount = ($cartItem->costamount - $discountAmount) < 0 ? 0 : $cartItem->costamount - $discountAmount;
                
                $updatedData = [
                    'discamount' => $discountAmount,
                    'discofferid' => $validatedData['discount']['DISCOFFERNAME'],
                    'disctypes' => $validatedData['discount']['DISCOUNTTYPE'],
                    'discparameter' => $validatedData['discount']['PARAMETER'],
                    'costamount' => $costamount,
                    'netamount' => $cartItem->netamount - $discountAmount,
                    'grossamount' => $cartItem->grossamount,
                    'taxinclinprice' => (($cartItem->netamount - $discountAmount) / 1.12) * .12,
                    'netamountnotincltax' => ($cartItem->netamount - $discountAmount) / 1.12,
                    'updated_at' => now()
                ];
                
                $cartItem->update($updatedData);
                $updatedItems[] = $cartItem->fresh();
            } else {
                Log::warning("Attempted to update non-existent cart item", ['itemid' => $item['itemid']]);
            }
        }

        DB::commit();
        return response()->json($updatedItems);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update cart items: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to update cart items: ' . $e->getMessage()], 500);
    }
}

private function calculateDiscountAmount($cartItem, $discountValue, $discountType)
{
    switch ($discountType) {
        case 'PERCENTAGE':
            return $cartItem->grossamount * ($discountValue / 100);
        case 'FIXED':
            return $discountValue * $cartItem->qty;
        case 'FREE':
            return $cartItem->grossamount;
        default:
            return $discountValue;
    }
}
}
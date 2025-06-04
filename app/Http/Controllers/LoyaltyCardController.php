<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyCard;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoyaltyCardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        return Inertia::render('LoyaltyCards/Index', [
            'loyaltyCards' => LoyaltyCard::with('customer')
                ->when($search, function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('card_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($query) use ($search) {
                                $query->where('NAME', 'like', "%{$search}%")
                                    ->orWhere('ACCOUNTNUM', 'like', "%{$search}%");
                            });
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($card) {
                    return [
                        'id' => $card->id,
                        'card_number' => $card->card_number,
                        'customer_id' => $card->customer_id,
                        'customer_name' => $card->customer ? $card->customer->NAME : '-',
                        'points' => $card->points,
                        'points_formatted' => number_format($card->points),
                        'tier' => $card->tier,
                        'status' => $card->status,
                        'expiry_date' => $card->expiry_date,
                        'created_at_formatted' => $card->created_at->format('Y-m-d H:i'),
                        'is_active' => $card->is_active,
                    ];
                }),
            'customers' => Customer::whereDoesntHave('loyaltyCard')
                ->where('BLOCKED', false)
                ->select('id', 'NAME', 'ACCOUNTNUM')
                ->orderBy('NAME')
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->NAME,
                        'account' => $customer->ACCOUNTNUM
                    ];
                }),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function show($customerId)
{
    $loyaltyCard = LoyaltyCard::where('customer_id', $customerId)
        ->with(['customer', 'transactions' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])
        ->firstOrFail();

    // Debug line to check the transaction data
    \Log::debug('Transaction Data:', $loyaltyCard->transactions->toArray());

    return Inertia::render('LoyaltyCards/Show', [
        'loyaltyCard' => array_merge($loyaltyCard->toArray(), [
            'points_formatted' => number_format($loyaltyCard->points),
            'expiry_date_formatted' => optional($loyaltyCard->expiry_date)->format('Y-m-d'),
            'is_active' => $loyaltyCard->status === 'active',
        ]),
        'transactions' => $loyaltyCard->transactions->map(function ($transaction) {
            // Ensure all necessary fields are explicitly mapped
            return [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'points' => $transaction->points,
                'description' => $transaction->description,
                'balance_after' => $transaction->balance_after,
                'created_at' => $transaction->created_at,
                'points_formatted' => number_format($transaction->points),
                'balance_after_formatted' => number_format($transaction->balance_after),
                'created_at_formatted' => $transaction->created_at->format('Y-m-d H:i'),
            ];
        })->values()->all(), // Convert to array and reindex
        'maxPointsPerTransaction' => config('loyalty.max_points_per_transaction', 10000),
        'storeId' => Auth::user()->storeid
    ]);
}

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|in:active,inactive,suspended',
            'card_number' => 'required|unique:loyalty_cards,card_number'
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        
        if (!$customer->canHaveLoyaltyCard()) {
            throw ValidationException::withMessages([
                'customer_id' => ['This customer cannot have a loyalty card']
            ]);
        }

        DB::transaction(function () use ($request, $customer) {
            LoyaltyCard::create([
                'customer_id' => $customer->id,
                'status' => $request->status,
                'card_number' => $request->card_number,
                'points' => 0,
                'tier' => 'bronze'
            ]);
        });

        return redirect()->back()->with('success', 'Loyalty card created successfully');
    }

    public function update(Request $request, LoyaltyCard $loyaltyCard)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended'
        ]);

        DB::transaction(function () use ($request, $loyaltyCard) {
            $loyaltyCard->update([
                'status' => $request->status
            ]);
        });

        return redirect()->back()->with('success', 'Loyalty card updated successfully');
    }

    public function addPoints(Request $request)
    {
        if (Auth::user()->storeid !== 'HEADOFFICE') {
            throw ValidationException::withMessages([
                'authorization' => ['Only HEADOFFICE users can add points']
            ]);
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => [
                'required',
                'integer',
                'min:1',
                'max:' . config('loyalty.max_points_per_transaction', 10000)
            ],
            'description' => 'required|string|max:255'
        ]);

        $loyaltyCard = LoyaltyCard::where('customer_id', $request->customer_id)
            ->firstOrFail();

        if (!$loyaltyCard->is_active) {
            throw ValidationException::withMessages([
                'points' => ['Cannot add points to an inactive or suspended card']
            ]);
        }

        try {
            DB::transaction(function () use ($loyaltyCard, $request) {
                $loyaltyCard->addPoints($request->points, $request->description);
            });
            
            return redirect()->back()->with('success', 'Points added successfully');
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'points' => [$e->getMessage()]
            ]);
        }
    }

    public function redeemPoints(Request $request)
    {
        if (Auth::user()->storeid !== 'HEADOFFICE') {
            throw ValidationException::withMessages([
                'authorization' => ['Only HEADOFFICE users can redeem points']
            ]);
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer|min:1',
            'description' => 'required|string|max:255'
        ]);

        $loyaltyCard = LoyaltyCard::where('customer_id', $request->customer_id)
            ->firstOrFail();

        if (!$loyaltyCard->is_active) {
            throw ValidationException::withMessages([
                'points' => ['Cannot redeem points from an inactive or suspended card']
            ]);
        }

        if ($loyaltyCard->points < $request->points) {
            throw ValidationException::withMessages([
                'points' => ['Insufficient points available for redemption']
            ]);
        }

        try {
            DB::transaction(function () use ($loyaltyCard, $request) {
                $loyaltyCard->redeemPoints($request->points, $request->description);
            });
            
            return redirect()->back()->with('success', 'Points redeemed successfully');
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'points' => [$e->getMessage()]
            ]);
        }
    }

    public function destroy(LoyaltyCard $loyaltyCard)
    {
        if ($loyaltyCard->points > 0) {
            throw ValidationException::withMessages([
                'points' => ['Cannot delete card with outstanding points']
            ]);
        }

        DB::transaction(function () use ($loyaltyCard) {
            $loyaltyCard->delete();
        });

        return redirect()->back()->with('success', 'Loyalty card deleted successfully');
    }
}
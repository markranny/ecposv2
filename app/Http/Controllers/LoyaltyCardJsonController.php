<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyCard;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoyaltyCardJsonController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $loyaltyCards = LoyaltyCard::with('customer')
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
                    'created_at' => $card->created_at->format('Y-m-d H:i'),
                    'is_active' => $card->is_active,
                ];
            });

        $availableCustomers = Customer::whereDoesntHave('loyaltyCard')
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
            });

        return response()->json([
            'data' => [
                'loyalty_cards' => $loyaltyCards,
                'available_customers' => $availableCustomers,
            ],
            'meta' => [
                'filters' => [
                    'search' => $search,
                ]
            ]
        ]);
    }

    public function show($customerId)
    {
        $loyaltyCard = LoyaltyCard::where('customer_id', $customerId)
            ->with(['customer', 'transactions' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->firstOrFail();

        $transactions = $loyaltyCard->transactions->map(function ($transaction) {
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
        });

        return response()->json([
            'data' => [
                'loyalty_card' => array_merge($loyaltyCard->toArray(), [
                    'points_formatted' => number_format($loyaltyCard->points),
                    'expiry_date_formatted' => optional($loyaltyCard->expiry_date)->format('Y-m-d'),
                    'is_active' => $loyaltyCard->status === 'active',
                ]),
                'transactions' => $transactions,
            ],
            'meta' => [
                'max_points_per_transaction' => config('loyalty.max_points_per_transaction', 10000),
                'store_id' => Auth::user()->storeid
            ]
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

        $loyaltyCard = DB::transaction(function () use ($request, $customer) {
            return LoyaltyCard::create([
                'customer_id' => $customer->id,
                'status' => $request->status,
                'card_number' => $request->card_number,
                'points' => 0,
                'tier' => 'bronze'
            ]);
        });

        return response()->json([
            'message' => 'Loyalty card created successfully',
            'data' => $loyaltyCard
        ], 201);
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

        return response()->json([
            'message' => 'Loyalty card updated successfully',
            'data' => $loyaltyCard
        ]);
    }

    public function addPoints(Request $request)
    {
        if (Auth::user()->storeid !== 'HEADOFFICE') {
            return response()->json([
                'message' => 'Only HEADOFFICE users can add points'
            ], 403);
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
            return response()->json([
                'message' => 'Cannot add points to an inactive or suspended card'
            ], 422);
        }

        try {
            DB::transaction(function () use ($loyaltyCard, $request) {
                $loyaltyCard->addPoints($request->points, $request->description);
            });
            
            return response()->json([
                'message' => 'Points added successfully',
                'data' => $loyaltyCard->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function redeemPoints(Request $request)
    {
        if (Auth::user()->storeid !== 'HEADOFFICE') {
            return response()->json([
                'message' => 'Only HEADOFFICE users can redeem points'
            ], 403);
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer|min:1',
            'description' => 'required|string|max:255'
        ]);

        $loyaltyCard = LoyaltyCard::where('customer_id', $request->customer_id)
            ->firstOrFail();

        if (!$loyaltyCard->is_active) {
            return response()->json([
                'message' => 'Cannot redeem points from an inactive or suspended card'
            ], 422);
        }

        if ($loyaltyCard->points < $request->points) {
            return response()->json([
                'message' => 'Insufficient points available for redemption'
            ], 422);
        }

        try {
            DB::transaction(function () use ($loyaltyCard, $request) {
                $loyaltyCard->redeemPoints($request->points, $request->description);
            });
            
            return response()->json([
                'message' => 'Points redeemed successfully',
                'data' => $loyaltyCard->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(LoyaltyCard $loyaltyCard)
    {
        if ($loyaltyCard->points > 0) {
            return response()->json([
                'message' => 'Cannot delete card with outstanding points'
            ], 422);
        }

        DB::transaction(function () use ($loyaltyCard) {
            $loyaltyCard->delete();
        });

        return response()->json([
            'message' => 'Loyalty card deleted successfully'
        ]);
    }

    public function loyaltydetails(Request $request)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'loyalty_card_id' => 'required|exists:loyalty_cards,id',
            'type' => 'required|in:earn,redeem',
            'points' => 'required|integer',
            'balance_after' => 'required|integer',
            'description' => 'required|string',
        ]);

        // Create a new loyalty card transaction
        $loyaltyCardTransaction = LoyaltyCardTransaction::create($validated);

        // Return a success response with the created transaction
        return response()->json([
            'message' => 'Loyalty card transaction created successfully.',
            'data' => $loyaltyCardTransaction
        ], 201);
    }

    public function updatePointsByCardNumber($card_number, $points)
    {
        if (!is_numeric($points) || $points < 0) {
            return response()->json([
                'message' => 'Points must be a non-negative number'
            ], 422);
        }

        // Find loyalty card by card number
        $loyaltyCard = LoyaltyCard::where('card_number', $card_number)->first();

        if (!$loyaltyCard) {
            return response()->json([
                'message' => 'Loyalty card not found'
            ], 404);
        }

        try {
            DB::transaction(function () use ($loyaltyCard, $points) {
                $oldPoints = $loyaltyCard->points;
                $newPoints = (int)$points;
                $difference = $newPoints - $oldPoints;
                
                $loyaltyCard->update([
                    'points' => $newPoints,
                    'updated_by' => Auth::id() ?? 1 
                ]);

                $loyaltyCard->transactions()->create([
                    'type' => $difference >= 0 ? 'earn' : 'redeem',
                    'points' => abs($difference),
                    'description' => 'Points updated via API',
                    'balance_after' => $newPoints,
                    'created_by' => Auth::id() ?? 1
                ]);
            });

            return response()->json([
                'message' => 'Points updated successfully',
                'data' => [
                    'card_number' => $loyaltyCard->card_number,
                    'old_points' => $oldPoints ?? 0,
                    'new_points' => $points,
                    'updated_at' => $loyaltyCard->updated_at->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update points: ' . $e->getMessage()
            ], 500);
        }
    }

}
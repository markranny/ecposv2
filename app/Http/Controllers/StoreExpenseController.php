<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StoreExpense;
use Illuminate\Http\Request;
use App\Http\Requests\StoreExpenseRequest;
use Illuminate\Http\JsonResponse;

class StoreExpenseController extends Controller
{
    public function index(): JsonResponse
    {
        $storeExpense = StoreExpense::all(); 
        return response()->json([
            'storeExpense' => $storeExpense 
        ]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = StoreExpense::create($request->validated());
        return response()->json($expense, 201);
    }

    public function show(StoreExpense $storeExpense): JsonResponse
    {
        return response()->json($storeExpense);
    }

    public function update(StoreExpenseRequest $request, StoreExpense $storeExpense): JsonResponse
    {
        $storeExpense->update($request->validated());
        return response()->json($storeExpense);
    }

    public function destroy(StoreExpense $storeExpense): JsonResponse
    {
        $storeExpense->delete();
        return response()->json(null, 204);
    }

    public function updateExpense(Request $request, $storeId)
    {
        $validatedData = $request->validate([
            'expense_amount' => 'required|numeric',
            'expense_type' => 'required|string',
        ]);

        $store = Store::findOrFail($storeId);
        $expense = StoreExpense::updateOrCreate(
            [
                'store_id' => $storeId,
            ],
            [
                'amount' => $validatedData['expense_amount'],
                'type' => $validatedData['expense_type'],
            ]
        );

        return response()->json([
            'message' => 'Expense updated successfully',
            'expense' => $expense
        ], 200);
    }
}

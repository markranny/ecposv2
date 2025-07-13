<?php

namespace App\Http\Controllers;

use App\Models\discounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DiscountV2Controller extends Controller
{
    /**
     * Display a listing of the discounts.
     */
    public function index()
    {
        try {
            $discounts = discounts::orderBy('created_at', 'desc')->get();

            return Inertia::render('Discounts/Index', [
                'discounts' => $discounts
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in discounts index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Discounts/Index', [
                'discounts' => collect([]),
                'error' => 'Error loading discounts: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new discount.
     */
    public function create()
    {
        return Inertia::render('Discounts/Create');
    }

    /**
     * Store a newly created discount in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'DISCOFFERNAME' => 'required|string|max:255|unique:discounts,DISCOFFERNAME',
                'PARAMETER' => 'required|numeric|min:0',
                'DISCOUNTTYPE' => 'required|in:FIXED,FIXEDTOTAL,PERCENTAGE'
            ], [
                'DISCOFFERNAME.required' => 'Discount name is required',
                'DISCOFFERNAME.unique' => 'Discount name already exists',
                'PARAMETER.required' => 'Discount value is required',
                'PARAMETER.numeric' => 'Discount value must be a number',
                'PARAMETER.min' => 'Discount value must be greater than or equal to 0',
                'DISCOUNTTYPE.required' => 'Discount type is required',
                'DISCOUNTTYPE.in' => 'Invalid discount type selected'
            ]);

            // Additional validation for percentage
            if ($request->DISCOUNTTYPE === 'PERCENTAGE' && $request->PARAMETER > 100) {
                throw ValidationException::withMessages([
                    'PARAMETER' => ['Percentage discount cannot exceed 100%']
                ]);
            }

            DB::beginTransaction();

            discounts::create([
                'DISCOFFERNAME' => strtoupper(trim($request->DISCOFFERNAME)),
                'PARAMETER' => $request->PARAMETER,
                'DISCOUNTTYPE' => $request->DISCOUNTTYPE
            ]);

            DB::commit();

            return redirect()->route('discounts.index')
                ->with('message', 'Discount created successfully')
                ->with('isSuccess', true);

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', 'Validation failed')
                ->with('isSuccess', false);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating discount', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return back()
                ->with('message', 'Error creating discount: ' . $e->getMessage())
                ->with('isSuccess', false)
                ->withInput();
        }
    }

    /**
     * Display the specified discount.
     */
    public function show(discounts $discount)
    {
        return Inertia::render('Discounts/Show', [
            'discount' => $discount
        ]);
    }

    /**
     * Show the form for editing the specified discount.
     */
    public function edit(discounts $discount)
    {
        return Inertia::render('Discounts/Edit', [
            'discount' => $discount
        ]);
    }

    /**
     * Update the specified discount in storage.
     */
    public function update(Request $request, discounts $discount)
    {
        try {
            $request->validate([
                'DISCOFFERNAME' => 'required|string|max:255|unique:discounts,DISCOFFERNAME,' . $discount->id,
                'PARAMETER' => 'required|numeric|min:0',
                'DISCOUNTTYPE' => 'required|in:FIXED,FIXEDTOTAL,PERCENTAGE'
            ], [
                'DISCOFFERNAME.required' => 'Discount name is required',
                'DISCOFFERNAME.unique' => 'Discount name already exists',
                'PARAMETER.required' => 'Discount value is required',
                'PARAMETER.numeric' => 'Discount value must be a number',
                'PARAMETER.min' => 'Discount value must be greater than or equal to 0',
                'DISCOUNTTYPE.required' => 'Discount type is required',
                'DISCOUNTTYPE.in' => 'Invalid discount type selected'
            ]);

            // Additional validation for percentage
            if ($request->DISCOUNTTYPE === 'PERCENTAGE' && $request->PARAMETER > 100) {
                throw ValidationException::withMessages([
                    'PARAMETER' => ['Percentage discount cannot exceed 100%']
                ]);
            }

            DB::beginTransaction();

            $discount->update([
                'DISCOFFERNAME' => strtoupper(trim($request->DISCOFFERNAME)),
                'PARAMETER' => $request->PARAMETER,
                'DISCOUNTTYPE' => $request->DISCOUNTTYPE
            ]);

            DB::commit();

            return redirect()->route('discounts.index')
                ->with('message', 'Discount updated successfully')
                ->with('isSuccess', true);

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', 'Validation failed')
                ->with('isSuccess', false);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating discount', [
                'error' => $e->getMessage(),
                'discount_id' => $discount->id,
                'data' => $request->all()
            ]);
            
            return back()
                ->with('message', 'Error updating discount: ' . $e->getMessage())
                ->with('isSuccess', false)
                ->withInput();
        }
    }

    /**
     * Remove the specified discount from storage.
     */
    public function destroy(discounts $discount)
    {
        try {
            DB::beginTransaction();

            $discountName = $discount->DISCOFFERNAME;
            $discount->delete();

            DB::commit();

            return redirect()->route('discounts.index')
                ->with('message', "Discount '{$discountName}' deleted successfully")
                ->with('isSuccess', true);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting discount', [
                'error' => $e->getMessage(),
                'discount_id' => $discount->id
            ]);
            
            return back()
                ->with('message', 'Error deleting discount: ' . $e->getMessage())
                ->with('isSuccess', false);
        }
    }

    /**
     * Get all discounts for API
     */
    public function getDiscounts()
    {
        try {
            $discounts = discounts::orderBy('DISCOFFERNAME')->get();
            
            return response()->json([
                'success' => true,
                'data' => $discounts
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching discounts API', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching discounts'
            ], 500);
        }
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(Request $request)
    {
        try {
            $request->validate([
                'discount_id' => 'required|exists:discounts,id',
                'amount' => 'required|numeric|min:0'
            ]);

            $discount = discounts::findOrFail($request->discount_id);
            $originalAmount = $request->amount;
            $discountAmount = 0;
            $finalAmount = $originalAmount;

            switch ($discount->DISCOUNTTYPE) {
                case 'FIXED':
                    $discountAmount = min($discount->PARAMETER, $originalAmount);
                    $finalAmount = $originalAmount - $discountAmount;
                    break;
                    
                case 'FIXEDTOTAL':
                    $discountAmount = $discount->PARAMETER;
                    $finalAmount = max(0, $originalAmount - $discountAmount);
                    break;
                    
                case 'PERCENTAGE':
                    $discountAmount = ($originalAmount * $discount->PARAMETER) / 100;
                    $finalAmount = $originalAmount - $discountAmount;
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'original_amount' => $originalAmount,
                    'discount_amount' => round($discountAmount, 2),
                    'final_amount' => round($finalAmount, 2),
                    'discount_name' => $discount->DISCOFFERNAME,
                    'discount_type' => $discount->DISCOUNTTYPE,
                    'discount_value' => $discount->PARAMETER
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error calculating discount', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error calculating discount'
            ], 500);
        }
    }

    /**
     * Export discounts data
     */
    public function export()
    {
        try {
            $discounts = discounts::orderBy('DISCOFFERNAME')->get();

            return response()->json([
                'success' => true,
                'data' => $discounts
            ]);

        } catch (\Exception $e) {
            \Log::error('Error exporting discounts', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error exporting discounts'
            ], 500);
        }
    }
}
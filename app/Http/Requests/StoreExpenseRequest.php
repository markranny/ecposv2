<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add your authorization logic here
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'expense_type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'received_by' => 'required|string|max:255',
            'approved_by' => 'required|string|max:255',
            'effect_date' => 'required|date',
            'store_id' => 'required|string|max:255',
        ];
    }
}
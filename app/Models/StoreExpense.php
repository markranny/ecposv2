<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreExpense extends Model
{
    protected $fillable = [
        'name',
        'expense_type',
        'amount',
        'received_by',
        'approved_by',
        'effect_date',
        'store_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effect_date' => 'date',
    ];
}
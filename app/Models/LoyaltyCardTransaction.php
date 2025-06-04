<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoyaltyCardTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'loyalty_card_id',
        'type',
        'points',
        'description',
        'balance_after'
    ];

    protected $casts = [
        'points' => 'integer',
        'balance_after' => 'integer'
    ];

    public function loyaltyCard()
    {
        return $this->belongsTo(LoyaltyCard::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    protected $fillable = [
        'loyalty_card_id',
        'points',
        'type',
        'description'
    ];

    public function loyaltyCard()
    {
        return $this->belongsTo(LoyaltyCard::class);
    }
}
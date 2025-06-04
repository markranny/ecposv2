<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'ACCOUNTNUM',
        'NAME',
        'ADDRESS',
        'PHONE',
        'CURRENCY',
        'BLOCKED',
        'CREDITMAX',
        'COUNTRY',
        'ZIPCODE',
        'STATE',
        'EMAIL',
        'CELLULARPHONE',
        'GENDER',
    ];

    // Add the relationship
    public function loyaltyCard()
    {
        return $this->hasOne(LoyaltyCard::class);
    }

    // Helper method to check if customer can have a loyalty card
    public function canHaveLoyaltyCard()
    {
        return !$this->BLOCKED && !$this->loyaltyCard()->exists();
    }
}
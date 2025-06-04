<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inventtrans extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'POSTINGDATE',
        'ITEMID',
        'STOREID',
        'ADJUSTMENT',
        'TYPE',
        'COSTPRICEPERITEM',
        'SALESPRICEWITHOUTTAXPERITEM',
        'SALESPRICEWITHTAXPERITEM',
        'REASONCODE',
        'DISCOUNTAMOUNTPERITEM',
        'UNITID',
        'ADJUSTMENTININVENTORYUNIT',
    ];
}

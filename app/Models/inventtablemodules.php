<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inventtablemodules extends Model
{
    use HasFactory;

    protected $table = 'inventtablemodules';
    protected $primaryKey = 'itemid';
    public $timestamps = true; 

    protected $fillable = [
        'itemid',
        'moduletype',
        'unitid',
        'price',
        'priceunit',
        'priceincltax',
        'quantity',
        'lowestqty',
        'highestqty',
        'blocked',
        'inventlocationid',
        'pricedate',
        'taxitemgroupid',
        // Added new price fields
        'manilaprice',
        'grabfood',
        'foodpanda',
        'mallprice',
        'foodpandamall',
        'grabfoodmall'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'priceincltax' => 'decimal:2',
        'manilaprice' => 'decimal:2',
        'grabfood' => 'decimal:2',
        'foodpanda' => 'decimal:2',
        'mallprice' => 'decimal:2',
        'foodpandamall' => 'decimal:2',
        'grabfoodmall' => 'decimal:2',
        'quantity' => 'decimal:2',
        'lowestqty' => 'decimal:2',
        'highestqty' => 'decimal:2',
        'pricedate' => 'datetime'
    ];
}
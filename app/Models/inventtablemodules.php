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
        'taxitemgroupid'
    ];
}

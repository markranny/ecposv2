<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sptrans extends Model
{
    use HasFactory;
    protected $fillable = [
        'JOURNALID',
        'STORENAME',
        'TRANSDATE',
        'ITEMID',
        'ADJUSTMENT',
        'COSTPRICE',
        'PRICEUNIT',
        'SALESAMOUNT',
        'INVENTONHAND',
        'COUNTED',
        'REASONREFRECID',
        'VARIANTID',
        'POSTED',
        'POSTEDDATETIME',
        'CHECKINGCOUNT',
        'MGCOUNT',
        'BALANCECOUNT',
        'UNITID',
        'REMARKS',
    ];
}

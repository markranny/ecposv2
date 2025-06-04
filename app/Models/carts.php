<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class carts extends Model
{
    use HasFactory;

        protected $fillable = [
        'id',
        'cartid',
        'itemid',
        'itemname',
        'itemgroup',
        'price',
        'netprice',
        'qty',
        'discamount',
        'costamount',
        'netamount',
        'grossamount',
        'custaccount',
        'store',
        'priceoverride',
        'paymentmethod',
        'staff',
        'discofferid',
        'linedscamount',
        'linediscpct',
        'disctypes',
        'discparameter',
        'custdiscamount',
        'unit',
        'unitqty',
        'unitprice',
        'taxamount',
        'createddate',
        'remarks',
        'inventbatchid',
        'inventbatchexpdate',
        'giftcard',
        'returntransactionid',
        'returnqty',
        'creditmemonumber',
        'taxinclinprice',
        'description',
        'returnlineid',
        'priceunit',
        'netamountnotincltax',
        'storetaxgroup',
        'currency',
        'taxexempt',
        'wintransid',
        'update_at',
        'created_at',
    ];
}

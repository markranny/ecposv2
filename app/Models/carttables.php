<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class carttables extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'cartid',
        'store',
        'staff',
        'custaccount',
        'netamount',
        'costamount',
        'grossamount',
        'partialpayment',
        'transactionstatus',
        'discamount',
        'custdiscamount',
        'totaldiscamount',
        'numberofitems',
        'refundreceiptid',
        'createddate',
        'priceoverride',
        'comment',
        'receiptemail',
        'markupamount',
        'markupdescription',
        'taxinclinprice',
        'vat',
        'window_number',
        'update_at',
        'created_at',
    ];
}

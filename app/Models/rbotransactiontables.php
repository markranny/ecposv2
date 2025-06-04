<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rbotransactiontables extends Model
{
    use HasFactory;

    protected $table = 'rbotransactiontables';

    protected $primaryKey = 'transactionid';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',        
        'transactionid',
        'type',
        'receiptid',
        'zreadid',
        'store',
        'staff',
        'custaccount',
        'cashamount',
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
        'refunddate',
        'refundby',
        'currency',
        'zreportid',
        'createddate',
        'priceoverride',
        'comment',
        'receiptemail',
        'markupamount',
        'markupdescription',
        'taxinclinprice',
        'netamountnotincltax',
        'window_number',
        'charge',
        'gcash',
        'paymaya',
        'cash',
        'card',
        'loyaltycard',
        'foodpanda',
        'grabfood',
        'representation',
        'storekey',
        'storesequence',
    ];

    protected $casts = [
        'netamount' => 'decimal:2',
        'costamount' => 'decimal:2',
        'grossamount' => 'decimal:2',
        'partialpayment' => 'decimal:2',
        'transactionstatus' => 'integer',
        'discamount' => 'decimal:2',
        'custdiscamount' => 'decimal:2',
        'totaldiscamount' => 'decimal:2',
        'numberofitems' => 'decimal:2',
        'priceoverride' => 'decimal:2',
        'markupamount' => 'decimal:2',
        'taxinclinprice' => 'decimal:2',
        'gcash' => 'decimal:2',
        'paymaya' => 'decimal:2',
        'cash' => 'decimal:2',
        'card' => 'decimal:2',
        'foodpanda' => 'decimal:2',
        'grabfood' => 'decimal:2',
        'loyaltycard' => 'decimal:2',
        'createddate' => 'datetime',
        'window_number' => 'integer',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rbotransactionsalestrans extends Model
{
    use HasFactory;

    protected $table = 'rbotransactionsalestrans';

    /* protected $primaryKey = ['transactionid', 'linenum'];

    public $incrementing = false;

    protected $keyType = 'array';

    public $timestamps = false; */

    protected $fillable = [
        'id',
        'transactionid',
        'linenum',
        'receiptid',
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
        'refunddate',
        'refundby',
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
        'cash',
        'charge',
        'representation',
        'gcash',
        'foodpanda',
        'grabfood',
        'mrktgdisc',
        'rddisc',
        'commision',
        'boamount',
        'emamount',
        'poamount',
        'rbamount',
        'abqty',
        'boqty',
        'emqty',
        'poqty',
        'rbqty',
        'abqty'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'netprice' => 'decimal:2',
        'qty' => 'decimal:2',
        'discamount' => 'decimal:2',
        'costamount' => 'decimal:2',
        'netamount' => 'decimal:2',
        'grossamount' => 'decimal:2',
        'linedscamount' => 'decimal:2',
        'linediscpct' => 'decimal:2',
        'custdiscamount' => 'decimal:2',
        'unitqty' => 'decimal:2',
        'unitprice' => 'decimal:2',
        'taxamount' => 'decimal:2',
        'returnqty' => 'decimal:2',
        'returnlineid' => 'decimal:2',
        'priceunit' => 'decimal:2',
        'netamountnotincltax' => 'decimal:2',
        'createddate' => 'datetime',
        'inventbatchexpdate' => 'datetime',
        'taxinclinprice' => 'decimal:2',
        'taxexempt' => 'decimal:2',
    ];

    /* public function getKeyType()
    {
        return 'array';
    }

    public function getIncrementing()
    {
        return false;
    } */
}

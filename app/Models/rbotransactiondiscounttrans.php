<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rbotransactiondiscounttrans extends Model
{
    use HasFactory;
    protected $fillable = [
        'TRANSACTIONID',
        'LINENUM',
        'DISCLINENUM',
        'STORE',
        'DISCOUNTTYPE',
        'DISCOUNTPCT',
        'DISCOUNTAMT',
        'DISCOUNTAMTWITHTAX',
        'PERIODICDISCTYPE',
        'DISCOFFERID',
        'DISCOFFERNAME',
        'QTYDISCOUNTED',
    ];
}

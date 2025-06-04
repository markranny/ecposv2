<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class partycakes extends Model
{
    use HasFactory;

    protected $fillable = [
        'COSNO',
        'BRANCH',
        'DATEORDER',
        'CUSTOMERNAME',
        'ADDRESS',
        'TELNO',
        'DATEPICKEDUP',
        'TIMEPICKEDUP',
        'DELIVERED',
        'TIMEDELIVERED',
        'DEDICATION',
        'BDAYCODENO',
        'FLAVOR',
        'MOTIF',
        'ICING',
        'OTHERS',
        'SRP',
        'DISCOUNT',
        'PARTIALPAYMENT',
        'NETAMOUNT',
        'BALANCEAMOUNT',
        'STATUS',
        'file_path',
        'TRANSACTSTORE'
        ];

    public $timestamps = true;
}

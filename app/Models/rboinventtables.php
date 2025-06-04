<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rboinventtables extends Model
{
    use HasFactory;

    protected $table = 'rboinventtables';
    protected $primaryKey = 'itemid';
    public $timestamps = true;

    protected $fillable = [
        'itemid',
        'itemtype',
        'itemgroup',
        'itemdepartment',
        'zeropricevalid',
        'dateblocked',
        'datetobeblocked',
        'blockedonpos',
        'barcode',
        'datetoactivateitem',
        'mustselectuom',
        'production',
        'moq',
        'stocks',
        'transparentstocks',
        'activeondelivery'
    ];
}

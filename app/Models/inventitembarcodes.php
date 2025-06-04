<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inventitembarcodes extends Model
{
    use HasFactory;
    protected $table = 'inventitembarcodes';
    public $timestamps = true; 

    protected $fillable = [
        'itembarcode',
        'itemid',
        'barcodesetupid',
        'description',
        'qty',
        'unitid',
        'rbovariantid',
        'blocked',
        'modifiedby'
    ];
}

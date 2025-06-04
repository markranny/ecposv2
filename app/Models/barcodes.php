<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class barcodes extends Model
{
    use HasFactory;

    protected $table = 'barcodes';
    public $timestamps = true;

    protected $fillable = [
        'barcode',
        'description',
        'isuse',
        'generateby',
        'generatedate',
        'modifiedby'
    ];
}

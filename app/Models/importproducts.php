<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportProducts extends Model
{
    protected $table = 'importproducts';
    
    protected $fillable = [
        'itemid',
        'description',
        'costprice',
        'salesprice',
        'searchalias',
        'notes',
        'retailgroup',
        'retaildepartment',
        'barcode',
        'activestatus',
        'barcodesetup',
    ];

    protected $casts = [
        'costprice' => 'decimal:2',
        'salesprice' => 'decimal:2',
        'activestatus' => 'boolean',
    ];

    /**
     * Scope to get active products
     */
    public function scopeActive($query)
    {
        return $query->where('activestatus', 1);
    }

    /**
     * Scope to get products by retail group
     */
    public function scopeByRetailGroup($query, $group)
    {
        return $query->where('retailgroup', $group);
    }

    /**
     * Scope to get products with barcode
     */
    public function scopeWithBarcode($query)
    {
        return $query->whereNotNull('barcode')->where('barcode', '!=', '');
    }
}
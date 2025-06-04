<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'from_store_id',
        'to_store_id',
        'status',
        'created_by',
        'transfer_date',
        'notes'
    ];

    // Add this to ensure proper casting
    protected $casts = [
        'from_store_id' => 'string',
        'to_store_id' => 'string',
        'transfer_date' => 'datetime',
        'created_by' => 'integer'
    ];

    public function from_store()
    {
        return $this->belongsTo(rbostoretables::class, 'from_store_id', 'STOREID');
    }

    public function to_store()
    {
        return $this->belongsTo(rbostoretables::class, 'to_store_id', 'STOREID');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
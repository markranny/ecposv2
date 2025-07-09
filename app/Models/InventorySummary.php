<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventorySummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'itemid',
        'itemname',
        'storename',
        'beginning',
        'received_delivery',
        'stock_transfer',
        'sales',
        'bundle_sales',
        'throw_away',
        'early_molds',
        'pull_out',
        'rat_bites',
        'ant_bites',
        'item_count',
        'ending',
        'variance',
        'report_date',
        'remarks'
    ];

    protected $casts = [
        'beginning' => 'float',
        'received_delivery' => 'float',
        'stock_transfer' => 'float',
        'sales' => 'float',
        'bundle_sales' => 'float',
        'throw_away' => 'float',
        'early_molds' => 'float',
        'pull_out' => 'float',
        'rat_bites' => 'float',
        'ant_bites' => 'float',
        'item_count' => 'float',
        'ending' => 'float',
        'variance' => 'float',
        'report_date' => 'date'
    ];
}
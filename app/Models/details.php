<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class details extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'FGENCODER',
        'PLENCODER',
        'DISPATCHER',
        'LOGISTICS',
        'ROUTES',
        'CREATEDDATE',
        'DELIVERYDATE',
    ];
    
    public $timestamps = true;
}

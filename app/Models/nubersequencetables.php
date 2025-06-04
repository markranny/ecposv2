<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class nubersequencetables extends Model
{
    use HasFactory;
    protected $fillable = [
        'NUMBERSEQUENCE',
        'TXT',
        'LOWEST',
        'HIGHEST',
        'BLOCKED',
        'STOREID',
        'CANBEDELETED',
    ];
}

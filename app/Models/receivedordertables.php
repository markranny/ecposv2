<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class receivedordertables extends Model
{
    use HasFactory;

    protected $fillable = [
        'DESCRIPTION',
        'POSTED',
        'POSTEDDATETIME',
        'JOURNALTYPE',
        'DELETEPOSTEDLINES',
        'CREATEDDATETIME',
        'STOREID',
    ];
}

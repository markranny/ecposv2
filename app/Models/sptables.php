<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sptables extends Model
{
    use HasFactory;

    protected $fillable = [
        'JOURNALID',
        'STOREID',
        'DESCRIPTION',
        'POSTED',
        'POSTEDDATETIME',
        'JOURNALTYPE',
        'DELETEPOSTEDLINES',
        'CREATEDDATETIME'

        /* 'journalid',
        'storeid',
        'description',
        'posted',
        'posteddatetime',
        'journaltype',
        'deletedpostedlines',
        'createddatetime' */
        
    ];
}

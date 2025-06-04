<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class stockcountingtables extends Model
{
    use HasFactory;
    protected $fillable = [
        'JOURNALID',
        'DESCRIPTION',
        'POSTED',
        'POSTEDDATETIME',
        'JOURNALTYPE',
        'DELETEPOSTEDLINES',
        'CREATEDDATETIME',
        'STOREID',
    ];
}

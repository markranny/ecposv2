<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wastedeclarations extends Model
{
    use HasFactory;

    protected $fillable = [
        'JOURNALID',
        'STOREID',
        'DESCRIPTION',
        'POSTED',
        'POSTEDDATETIME',
        'CREATEDDATETIME',
        
    ];
}

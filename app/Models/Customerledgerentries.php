<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customerledgerentries extends Model
{
    use HasFactory;
    protected $fillable = [
    'entryno',
    'postingdate',
    'customer',
    'type',
    'documentno',
    'description',
   'reasoncode',
    'currency',
    'currencyamount',
    'amount',
   'remainingamount',
    'userid', 
    ];
    
}

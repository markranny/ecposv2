<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class posvalidationperiods extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'description',
        'startingdate',
        'endingdate',
        'startingtime',
        'endingtime',
    ];
}

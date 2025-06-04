<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class posdiscvalidationperiods extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'description',
        'startingdate',
        'endingdate',

    ];
}

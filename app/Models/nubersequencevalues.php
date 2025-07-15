<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nubersequencevalues extends Model
{
    use HasFactory;

    protected $table = 'nubersequencevalues'; // Specify the table name if it doesn't follow the plural naming convention
    protected $primaryKey = 'STOREID'; // Define the primary key column
    public $incrementing = false; // Set this to false if the primary key is not auto-incrementing
    protected $keyType = 'string'; // Change this to 'integer' if your primary key is numeric

    protected $fillable = [
        'NUMBERSEQUENCE',
        'NEXTREC',
        'CARTNEXTREC',
        'BUNDLENEXTREC',
        'DISCOUNTNEXTREC',
        'STOREID',
        'TONEXTREC',
        'STOCKNEXTREC',
    ];
}

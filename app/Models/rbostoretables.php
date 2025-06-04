<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rbostoretables extends Model
{
    use HasFactory;

    protected $primaryKey = 'STOREID';
    protected $keyType = 'string';  // Add this line to specify the key type
    public $incrementing = false;   // Add this since STOREID is not auto-incrementing

    protected $fillable = [
        'STOREID',
        'NAME',
        'ROUTES',
        'TYPES',
        'BLOCKED',
        'ADDRESS',
        'STREET',
        'ZIPCODE',
        'CITY',
        'STATE',
        'COUNTRY',
        'PHONE',
        'CURRENCY',
        'SQLSERVERNAME',
        'DATABASENAME',
        'USERNAME',
        'PASSWORD',
        'WINDOWSAUTHENTICATION',
        'FORMINFOFIELD1',
        'FORMINFOFIELD2',
        'FORMINFOFIELD3',
        'FORMINFOFIELD4',
    ];

    public $timestamps = true;
}

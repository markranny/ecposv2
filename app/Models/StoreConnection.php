<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreConnection extends Model
{
    protected $fillable = [
        'store_id',
        'database_name',
        'host',
        'username',
        'password',
        'is_active'
    ];

    protected $hidden = ['password'];
}
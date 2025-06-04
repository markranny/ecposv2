<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class announcements extends Model
{
    use HasFactory;

    protected $fillable = [
        'ID',
        'SUBJECT',
        'DESCRIPTION',
        'file_path'
    ];

    public $timestamps = true;
}

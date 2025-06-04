<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $table = 'attendance_records';  
    protected $fillable = [
        'staffId',
        'storeId', 
        'date',
        'timeIn',
        'timeInPhoto',
        'breakIn',
        'breakInPhoto',
        'breakOut',
        'breakOutPhoto',
        'timeOut',
        'timeOutPhoto',
        'status'
    ];
}

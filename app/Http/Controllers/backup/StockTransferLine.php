<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferLine extends Model
{
    // Define the table associated with the model
    protected $table = 'stocktransferline';

    // Define the primary key for the table
    protected $primaryKey = 'JOURNALID';

    // Disable the incrementing of the primary key if it's not auto-incrementing
    public $incrementing = false;

    // Define the types for the columns (if needed for casting)
    protected $casts = [
        'TRANSDATE' => 'datetime',
        'POSTEDDATETIME' => 'datetime',
        'COUNTED' => 'decimal:12',
        'POSTED' => 'integer',
    ];

    // Allow mass assignment for the columns
    protected $fillable = [
        'JOURNALID',
        'TRANSDATE',
        'ITEMID',
        'ITEMDEPARTMENT',
        'STORENAME',
        'COUNTED',
        'POSTED',
        'POSTEDDATETIME',
    ];

    // If you have any custom timestamps or behavior for created_at/updated_at
    public $timestamps = true;

    // Optionally, define the format for the timestamps
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}

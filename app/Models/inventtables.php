<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inventtables extends Model
{
    use HasFactory;

    protected $table = 'inventtables';
    protected $primaryKey = 'itemid';
    public $timestamps = true;
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected $fillable = [
        'itemid',
        'itemgroupid',
        'itemname',
        'itemtype',
        'namealias',
        'notes'
    ];

    protected $casts = [
        'itemid' => 'string',
        'itemtype' => 'integer',
    ];

    public function modules()
    {
        return $this->hasMany(inventtablemodules::class, 'itemid', 'itemid');
    }
}
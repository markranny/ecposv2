<?php

// app/Models/Posmmlinegroups.php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class posmmlinegroups extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'offerid',
        'linegroup',
        'noofitemsneeded',
        'description'
    ];

    public function discount()
    {
        return $this->belongsTo(posperiodicdiscounts::class, 'offerid', 'offerid');
    }

    public function discountLines()
{
    return $this->hasMany(posperiodicdiscountlines::class, 'linegroup', 'linegroup');
}
}


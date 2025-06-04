<?php

// app/Models/Posperiodicdiscountlines.php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class posperiodicdiscountlines extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'offerid',
        'itemid',
        'dealpriceordiscpct',
        'linegroup',
        'disctype'
    ];

    public function discount()
    {
        return $this->belongsTo(posperiodicdiscounts::class, 'offerid', 'offerid');
    }
}

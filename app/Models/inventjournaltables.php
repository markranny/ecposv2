<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inventjournaltables extends Model
{
    use HasFactory;

    protected $fillable = [
        'JOURNALID',
        'STOREID',
        'DESCRIPTION',
        'POSTED',
        'SENT',
        'POSTEDDATETIME',
        'JOURNALTYPE',
        'DELETEPOSTEDLINES',
        'ENCODER',
        'DISPATCHER',
        'LOGISTICS',
        'DELIVERYDATE',
        'FGENCODER',
        'PLENCODER',
        'OPICPOSTED',
        'EMPANADACRATES',
        'ORANGECRATES',
        'BLUECRATES',
        'BOX',
        'CREATEDDATETIME',
        
    ];
}

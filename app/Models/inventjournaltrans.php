<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inventjournaltrans extends Model
{
    use HasFactory;
    protected $fillable = [
        'JOURNALID',
        'LINENUM',
        'TRANSDATE',
        'ITEMID',
        'ADJUSTMENT',
        'COSTPRICE',
        'PRICEUNIT',
        'SALESAMOUNT',
        'INVENTONHAND',
        'COUNTED',
        'REASONREFRECID',
        'VARIANTID',
        'POSTED',
        'POSTEDDATETIME',
        'CHECKINGCOUNT',
        'MGCOUNT',
        'BALANCECOUNT',
        'UNITID',
    ];

    // This is necessary for composite keys
    protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('JOURNALID', '=', $this->getAttribute('JOURNALID'))
            ->where('ITEMID', '=', $this->getAttribute('ITEMID'));

        return $query;
    }
}

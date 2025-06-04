<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inventjournaltransrepos extends Model
{
    use HasFactory;

    protected $primaryKey = ['JOURNALID', 'ITEMID'];
    public $incrementing = false;

    protected $fillable = [
        'JOURNALID',
        'LINENUM',
        'TRANSDATE',
        'ITEMID',
        'COUNTED',
        'STORENAME',
        'MOQ',
        'STATUS',
        'ITEMDEPARTMENT',
    ];

    protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('JOURNALID', '=', $this->getAttribute('JOURNALID'))
            ->where('ITEMID', '=', $this->getAttribute('ITEMID'));

        return $query;
    }

    public $timestamps = true;
}
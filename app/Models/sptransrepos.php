<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sptransrepos extends Model
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
    ];

    // This is necessary for composite keys
    protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('JOURNALID', '=', $this->getAttribute('JOURNALID'))
            ->where('ITEMID', '=', $this->getAttribute('ITEMID'));

        return $query;
    }

    public $timestamps = true;
}
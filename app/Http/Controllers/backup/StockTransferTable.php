<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferTable extends Model
{
    use HasFactory;

    protected $table = 'stocktransfertables';
    protected $primaryKey = 'JOURNALID';

    protected $fillable = [
        'DESCRIPTION',
        'FROM_STOREID',
        'TO_STOREID',
        'POSTED',
        'SENT',
        'POSTEDDATETIME',
        'SENTDATETIME',
        'JOURNALTYPE',
        'DELETEPOSTEDLINES',
        'CREATEDDATETIME',
        'STATUS' // New field to track overall status
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';

    public function fromStore()
    {
        return $this->belongsTo(rbostoretables::class, 'FROM_STOREID', 'STOREID');
    }

    public function toStore()
    {
        return $this->belongsTo(rbostoretables::class, 'TO_STOREID', 'STOREID');
    }

    public function transferLines()
    {
        return $this->hasMany(StockTransferLine::class, 'JOURNALID', 'JOURNALID');
    }

    public function canBeSent()
    {
        return !$this->POSTED && $this->STATUS === self::STATUS_DRAFT;
    }

    public function canBeReceived()
    {
        return $this->POSTED && !$this->SENT && $this->STATUS === self::STATUS_SENT;
    }
}
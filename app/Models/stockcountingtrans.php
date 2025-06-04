<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class stockcountingtrans extends Model
    {
    protected $table = 'stockcountingtrans';
    protected $primaryKey = 'JOURNALID';
    public $incrementing = true;
    protected $keyType = 'int';
    
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    
        use HasFactory;
        protected $fillable = [
            'JOURNALID',
            'LINENUM',
            'TRANSDATE',
            'ITEMID',
            'ITEMDEPARTMENT',
            'STORENAME',
            'ADJUSTMENT',
            'COSTPRICE',
            'PRICEUNIT',
            'SALESAMOUNT',
            'INVENTONHAND',
            'COUNTED',
            'REASONREFECID',
            'VARIANTID',
            'POSTED',
            'POSTEDDATETIME',
            'created_at',
            'updated_at',
            'WASTECOUNT',
            'RECEIVEDCOUNT',
            'WASTETYPE',
            'TRANSFERCOUNT',
            'WASTEDATE'
        ];

    }

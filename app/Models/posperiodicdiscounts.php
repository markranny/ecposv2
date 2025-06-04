<?php
// app/Models/Posperiodicdiscounts.php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class posperiodicdiscounts extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'offerid';
    protected $keyType = 'string';
    
    protected $fillable = [
        'offerid',
        'description',
        'status',
        'discvalidperiodid',
        'discounttype',
        'dealpricevalue',
        'discountpctvalue',
        'discountamountvalue',
        'pricegroup',
        'triggered'
    ];

    public function lineGroups()
    {
        return $this->hasMany(posmmlinegroups::class, 'offerid', 'offerid');
    }

    public function discountLines()
    {
        return $this->hasMany(posperiodicdiscountlines::class, 'offerid', 'offerid');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LoyaltyCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'card_number',
        'points',
        'status',
        'expiry_date',
        'tier'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'points' => 'integer'
    ];

    protected $appends = ['is_active'];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions()
    {
        return $this->hasMany(LoyaltyCardTransaction::class);
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    // Card number generation
    public static function generateCardNumber()
    {
        $prefix = 'LC';
        $year = date('y');
        $random = strtoupper(Str::random(6));
        $number = $prefix . $year . $random;
        
        // Add checksum digit
        $sum = 0;
        for ($i = 0; $i < strlen($number); $i++) {
            $digit = ord($number[$i]);
            $sum += $digit;
        }
        $checksum = $sum % 10;
        
        return $number . $checksum;
    }

    // Methods for points management
    public function addPoints($points, $description)
    {
        if (!$this->is_active) {
            throw new \Exception('Cannot add points to inactive or suspended card');
        }

        $this->transactions()->create([
            'type' => 'earn',
            'points' => $points,
            'description' => $description,
            'balance_after' => $this->points + $points
        ]);

        $this->increment('points', $points);
        
        // Check for tier upgrades
        $this->checkAndUpdateTier();
    }

    public function redeemPoints($points, $description)
    {
        if (!$this->is_active) {
            throw new \Exception('Cannot redeem points from inactive or suspended card');
        }

        if ($this->points < $points) {
            throw new \Exception('Insufficient points balance');
        }

        $this->transactions()->create([
            'type' => 'redeem',
            'points' => $points,
            'description' => $description,
            'balance_after' => $this->points - $points
        ]);

        $this->decrement('points', $points);
    }

    private function checkAndUpdateTier()
    {
        $newTier = match(true) {
            $this->points >= 10000 => 'platinum',
            $this->points >= 5000 => 'gold',
            $this->points >= 1000 => 'silver',
            default => 'bronze'
        };

        if ($this->tier !== $newTier) {
            $this->update(['tier' => $newTier]);
        }
    }

    /* protected static function boot()
    {
        parent::boot();

        static::creating(function ($card) {
            if (empty($card->card_number)) {
                $card->card_number = self::generateCardNumber();
            }
            
            $card->points = $card->points ?? 0;
            $card->tier = $card->tier ?? 'bronze';
            $card->expiry_date = $card->expiry_date ?? now()->addYears(2);
        });
    } */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($card) {
            $card->points = $card->points ?? 0;
            $card->tier = $card->tier ?? 'bronze';
            $card->expiry_date = $card->expiry_date ?? now()->addYears(2);
        });
    }
}
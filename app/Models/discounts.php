<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class discounts extends Model
{
    use HasFactory;

    protected $table = 'discounts';
    
    protected $fillable = [
        'DISCOFFERNAME',
        'PARAMETER',
        'DISCOUNTTYPE'
    ];

    protected $casts = [
        'PARAMETER' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Add timestamps
    public $timestamps = true;

    // Scope for active discounts (if you need this later)
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Helper method to format discount value
    public function getFormattedValueAttribute()
    {
        switch ($this->DISCOUNTTYPE) {
            case 'PERCENTAGE':
                return $this->PARAMETER . '%';
            case 'FIXED':
            case 'FIXEDTOTAL':
                return '₱' . number_format($this->PARAMETER, 2);
            default:
                return $this->PARAMETER;
        }
    }

    // Helper method to get discount description
    public function getDescriptionAttribute()
    {
        switch ($this->DISCOUNTTYPE) {
            case 'PERCENTAGE':
                return $this->PARAMETER . '% off the total amount';
            case 'FIXED':
                return '₱' . number_format($this->PARAMETER, 2) . ' off per item (max discount per item)';
            case 'FIXEDTOTAL':
                return '₱' . number_format($this->PARAMETER, 2) . ' off the total amount';
            default:
                return 'Unknown discount type';
        }
    }

    // Calculate discount for given amount
    public function calculateDiscount($amount)
    {
        $originalAmount = $amount;
        $discountAmount = 0;
        $finalAmount = $originalAmount;

        switch ($this->DISCOUNTTYPE) {
            case 'FIXED':
                $discountAmount = min($this->PARAMETER, $originalAmount);
                $finalAmount = $originalAmount - $discountAmount;
                break;
                
            case 'FIXEDTOTAL':
                $discountAmount = $this->PARAMETER;
                $finalAmount = max(0, $originalAmount - $discountAmount);
                break;
                
            case 'PERCENTAGE':
                $discountAmount = ($originalAmount * $this->PARAMETER) / 100;
                $finalAmount = $originalAmount - $discountAmount;
                break;
        }

        return [
            'original_amount' => $originalAmount,
            'discount_amount' => round($discountAmount, 2),
            'final_amount' => round($finalAmount, 2),
            'savings_percentage' => $originalAmount > 0 ? round(($discountAmount / $originalAmount) * 100, 1) : 0
        ];
    }
}
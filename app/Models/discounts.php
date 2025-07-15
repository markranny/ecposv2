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
        'DISCOUNTTYPE',
        'GRABFOOD_PARAMETER',
        'FOODPANDA_PARAMETER',
        'FOODPANDAMALL_PARAMETER',
        'GRABFOODMALL_PARAMETER',
        'MANILAPRICE_PARAMETER',
        'MALLPRICE_PARAMETER'
    ];

    protected $casts = [
        'PARAMETER' => 'decimal:2',
        'GRABFOOD_PARAMETER' => 'decimal:2',
        'FOODPANDA_PARAMETER' => 'decimal:2',
        'FOODPANDAMALL_PARAMETER' => 'decimal:2',
        'GRABFOODMALL_PARAMETER' => 'decimal:2',
        'MANILAPRICE_PARAMETER' => 'decimal:2',
        'MALLPRICE_PARAMETER' => 'decimal:2',
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

    // Helper method to format discount value for specific platform
    public function getFormattedValueAttribute($platform = null)
    {
        $parameter = $this->getParameterForPlatform($platform);
        
        switch ($this->DISCOUNTTYPE) {
            case 'PERCENTAGE':
                return $parameter . '%';
            case 'FIXED':
            case 'FIXEDTOTAL':
                return '₱' . number_format($parameter, 2);
            default:
                return $parameter;
        }
    }

    // Get parameter value for specific platform
    public function getParameterForPlatform($platform = null)
    {
        switch ($platform) {
            case 'grabfood':
                return $this->GRABFOOD_PARAMETER ?? $this->PARAMETER;
            case 'foodpanda':
                return $this->FOODPANDA_PARAMETER ?? $this->PARAMETER;
            case 'foodpandamall':
                return $this->FOODPANDAMALL_PARAMETER ?? $this->PARAMETER;
            case 'grabfoodmall':
                return $this->GRABFOODMALL_PARAMETER ?? $this->PARAMETER;
            case 'manila':
                return $this->MANILAPRICE_PARAMETER ?? $this->PARAMETER;
            case 'mall':
                return $this->MALLPRICE_PARAMETER ?? $this->PARAMETER;
            default:
                return $this->PARAMETER;
        }
    }

    // Helper method to get discount description for specific platform
    public function getDescriptionAttribute($platform = null)
    {
        $parameter = $this->getParameterForPlatform($platform);
        
        switch ($this->DISCOUNTTYPE) {
            case 'PERCENTAGE':
                return $parameter . '% off the total amount';
            case 'FIXED':
                return '₱' . number_format($parameter, 2) . ' off per item (max discount per item)';
            case 'FIXEDTOTAL':
                return '₱' . number_format($parameter, 2) . ' off the total amount';
            default:
                return 'Unknown discount type';
        }
    }

    // Calculate discount for given amount and platform
    public function calculateDiscount($amount, $platform = null)
    {
        $originalAmount = $amount;
        $parameter = $this->getParameterForPlatform($platform);
        $discountAmount = 0;
        $finalAmount = $originalAmount;

        switch ($this->DISCOUNTTYPE) {
            case 'FIXED':
                $discountAmount = min($parameter, $originalAmount);
                $finalAmount = $originalAmount - $discountAmount;
                break;
                
            case 'FIXEDTOTAL':
                $discountAmount = $parameter;
                $finalAmount = max(0, $originalAmount - $discountAmount);
                break;
                
            case 'PERCENTAGE':
                $discountAmount = ($originalAmount * $parameter) / 100;
                $finalAmount = $originalAmount - $discountAmount;
                break;
        }

        return [
            'original_amount' => $originalAmount,
            'discount_amount' => round($discountAmount, 2),
            'final_amount' => round($finalAmount, 2),
            'savings_percentage' => $originalAmount > 0 ? round(($discountAmount / $originalAmount) * 100, 1) : 0,
            'platform' => $platform,
            'parameter_used' => $parameter
        ];
    }

    // Get all platform parameters
    public function getAllPlatformParameters()
    {
        return [
            'default' => $this->PARAMETER,
            'grabfood' => $this->GRABFOOD_PARAMETER,
            'foodpanda' => $this->FOODPANDA_PARAMETER,
            'foodpandamall' => $this->FOODPANDAMALL_PARAMETER,
            'grabfoodmall' => $this->GRABFOODMALL_PARAMETER,
            'manila' => $this->MANILAPRICE_PARAMETER,
            'mall' => $this->MALLPRICE_PARAMETER
        ];
    }

    // Check if discount has platform-specific parameters
    public function hasPlatformSpecificParameters()
    {
        return $this->GRABFOOD_PARAMETER !== null ||
               $this->FOODPANDA_PARAMETER !== null ||
               $this->FOODPANDAMALL_PARAMETER !== null ||
               $this->GRABFOODMALL_PARAMETER !== null ||
               $this->MANILAPRICE_PARAMETER !== null ||
               $this->MALLPRICE_PARAMETER !== null;
    }
}
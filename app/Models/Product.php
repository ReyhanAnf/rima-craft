<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'description', 'base_price', 'reseller_price', 'current_stock', 'image_path', 'media_assets', 'variants'])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    public function productionResults()
    {
        return $this->hasMany(ProductionResult::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockAdjustments()
    {
        return $this->morphMany(StockAdjustment::class, 'adjustable');
    }

    public function regionPrices()
    {
        return $this->hasMany(ProductRegionPrice::class);
    }

    public function userPrices()
    {
        return $this->hasMany(ProductUserPrice::class);
    }

    /**
     * Get price based on user role and optional region
     * 
     * @param \App\Models\User|null $user
     * @param \App\Models\Region|null $region
     * @return float
     */
    public function getPriceForUser(?User $user = null, ?Region $region = null): float
    {
        // 1. User-specific reseller override (Highest Priority)
        if ($user) {
            $userPrice = $this->userPrices()->where('user_id', $user->id)->first();
            if ($userPrice) {
                return (float) $userPrice->price;
            }
        }

        // 2. Region Price Override (Second Priority: City -> Province)
        if ($region) {
            // First check specific region price (e.g., City)
            $regionPrice = $this->regionPrices()->where('region_id', $region->id)->first();
            
            // If not found, and the region is a city/regency with parent province, check parent
            if (!$regionPrice && $region->parent_id) {
                $regionPrice = $this->regionPrices()->where('region_id', $region->parent_id)->first();
            }
            
            if ($regionPrice) {
                if ($user && $user->hasRole('reseller')) {
                    return $regionPrice->reseller_price && $regionPrice->reseller_price > 0
                        ? (float) $regionPrice->reseller_price
                        : (float) $regionPrice->base_price;
                }
                return (float) $regionPrice->base_price;
            }
        }

        // 3. Fallback to product default reseller or base price
        if ($user && $user->hasRole('reseller')) {
            return $this->reseller_price && $this->reseller_price > 0 
                ? (float) $this->reseller_price 
                : (float) $this->base_price;
        }
        
        return (float) $this->base_price;
    }

    protected function casts(): array
    {
        return [
            'base_price'     => 'decimal:2',
            'current_stock'  => 'integer',
            'media_assets'   => 'array',
            'variants'       => 'array',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\User;

class ProductPriceService
{
    /**
     * Get formatted price for display
     * 
     * @param Product $product
     * @param User|null $user
     * @param \App\Models\Region|null $region
     * @return array{price: float, formatted: string, is_reseller: bool, has_discount: bool}
     */
    public function getProductPrice(Product $product, ?User $user = null, ?\App\Models\Region $region = null): array
    {
        $isReseller = $user && $user->hasRole('reseller');
        $price = $product->getPriceForUser($user, $region);
        
        $regionPrice = $region ? $product->regionPrices()->where('region_id', $region->id)->first() : null;
        $basePrice = $regionPrice ? (float) $regionPrice->base_price : (float) $product->base_price;
        $resellerPrice = $regionPrice ? (float) $regionPrice->reseller_price : (float) $product->reseller_price;
        
        $hasDiscount = $isReseller && $resellerPrice && $resellerPrice < $basePrice;
        
        return [
            'price' => $price,
            'formatted' => $this->formatRupiah($price),
            'is_reseller' => $isReseller,
            'has_discount' => $hasDiscount,
            'base_price' => $basePrice,
            'base_price_formatted' => $this->formatRupiah($basePrice),
            'reseller_price' => $resellerPrice ? (float) $resellerPrice : null,
            'reseller_price_formatted' => $resellerPrice ? $this->formatRupiah((float) $resellerPrice) : null,
        ];
    }

    /**
     * Format price to Indonesian Rupiah
     * 
     * @param float $price
     * @return string
     */
    private function formatRupiah(float $price): string
    {
        return 'Rp ' . number_format($price, 0, ',', '.');
    }

    /**
     * Calculate discount percentage
     * 
     * @param Product $product
     * @param \App\Models\Region|null $region
     * @return float
     */
    public function getDiscountPercentage(Product $product, ?\App\Models\Region $region = null): float
    {
        $regionPrice = $region ? $product->regionPrices()->where('region_id', $region->id)->first() : null;
        $basePrice = $regionPrice ? (float) $regionPrice->base_price : (float) $product->base_price;
        $resellerPrice = $regionPrice ? (float) $regionPrice->reseller_price : (float) $product->reseller_price;

        if (!$resellerPrice || $resellerPrice <= 0) {
            return 0;
        }
        
        if ($basePrice <= 0) {
            return 0;
        }
        
        $discount = (($basePrice - $resellerPrice) / $basePrice) * 100;
        return round($discount, 1);
    }

    /**
     * Get bulk pricing info for partner
     * 
     * @param Product $product
     * @param int $quantity
     * @param User $user
     * @param \App\Models\Region|null $region
     * @return array{unit_price: float, total: float, savings: float}
     */
    public function getBulkPrice(Product $product, int $quantity, User $user, ?\App\Models\Region $region = null): array
    {
        $unitPrice = $product->getPriceForUser($user, $region);
        $total = $unitPrice * $quantity;
        
        $regionPrice = $region ? $product->regionPrices()->where('region_id', $region->id)->first() : null;
        $basePrice = $regionPrice ? (float) $regionPrice->base_price : (float) $product->base_price;
        
        $baseTotal = $basePrice * $quantity;
        $savings = $baseTotal - $total;
        
        return [
            'unit_price' => $unitPrice,
            'total' => $total,
            'savings' => max(0, $savings),
        ];
    }
}

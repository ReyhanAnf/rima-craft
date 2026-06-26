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
     * @return array{price: float, formatted: string, is_reseller: bool, has_discount: bool}
     */
    public function getProductPrice(Product $product, ?User $user = null): array
    {
        $isReseller = $user && $user->hasRole('partner');
        $price = $product->getPriceForUser($user);
        $basePrice = (float) $product->base_price;
        $hasDiscount = $isReseller && $product->reseller_price && $product->reseller_price < $basePrice;
        
        return [
            'price' => $price,
            'formatted' => $this->formatRupiah($price),
            'is_reseller' => $isReseller,
            'has_discount' => $hasDiscount,
            'base_price' => $basePrice,
            'base_price_formatted' => $this->formatRupiah($basePrice),
            'reseller_price' => $product->reseller_price ? (float) $product->reseller_price : null,
            'reseller_price_formatted' => $product->reseller_price ? $this->formatRupiah((float) $product->reseller_price) : null,
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
     * @return float
     */
    public function getDiscountPercentage(Product $product): float
    {
        if (!$product->reseller_price || $product->reseller_price <= 0) {
            return 0;
        }
        
        $basePrice = (float) $product->base_price;
        if ($basePrice <= 0) {
            return 0;
        }
        
        $discount = (($basePrice - $product->reseller_price) / $basePrice) * 100;
        return round($discount, 1);
    }

    /**
     * Get bulk pricing info for partner
     * 
     * @param Product $product
     * @param int $quantity
     * @param User $user
     * @return array{unit_price: float, total: float, savings: float}
     */
    public function getBulkPrice(Product $product, int $quantity, User $user): array
    {
        $unitPrice = $product->getPriceForUser($user);
        $total = $unitPrice * $quantity;
        $baseTotal = (float) $product->base_price * $quantity;
        $savings = $baseTotal - $total;
        
        return [
            'unit_price' => $unitPrice,
            'total' => $total,
            'savings' => max(0, $savings),
        ];
    }
}

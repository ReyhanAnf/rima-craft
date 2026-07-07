<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Product;
use App\Services\ProductPriceService;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    /**
     * Get cities by province
     */
    public function getCities(Region $province)
    {
        if ($province->type !== 'province') {
            return response()->json(['error' => 'Invalid province'], 400);
        }

        $cities = $province->children()
            ->with('shippingRate')
            ->orderBy('name')
            ->get()
            ->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'shipping_cost' => $city->shippingRate ? (float) $city->shippingRate->shipping_cost : 0.0,
                ];
            });

        return response()->json($cities);
    }

    /**
     * Calculate cart totals based on region
     */
    public function calculateTotals(Request $request, ProductPriceService $priceService)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:regions,id',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.variantLabel' => 'nullable|string|max:255',
        ]);

        $city = Region::with('shippingRate')->find($validated['city_id']);
        $user = auth()->user();
        
        $shippingCost = $city->shippingRate ? (float) $city->shippingRate->shipping_cost : 0.0;
        $subtotal = 0;
        $updatedItems = [];

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['id']);
            $priceData = $priceService->getProductPrice($product, $user, $city);
            
            $variantLabel = $item['variantLabel'] ?? null;
            $price = (float) $priceData['price'];
            if ($variantLabel && is_array($product->variants)) {
                foreach ($product->variants as $variant) {
                    if (($variant['label'] ?? '') === $variantLabel) {
                        $price += (float) ($variant['price_adj'] ?? 0);
                        break;
                    }
                }
            }

            $itemSubtotal = $price * $item['qty'];
            $subtotal += $itemSubtotal;
            
            $updatedItems[] = [
                'id' => $product->id,
                'name' => $product->name,
                'qty' => $item['qty'],
                'price' => $price,
                'subtotal' => $itemSubtotal,
                'image' => $product->image_path ? (str_starts_with($product->image_path, 'http') || str_starts_with($product->image_path, '/') ? $product->image_path : '/storage/' . $product->image_path) : null,
                'variantLabel' => $variantLabel,
            ];
        }

        $total = $subtotal + $shippingCost;

        return response()->json([
            'items' => $updatedItems,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $total,
        ]);
    }
}

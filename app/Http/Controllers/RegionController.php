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
        ]);

        $city = Region::with('shippingRate')->find($validated['city_id']);
        $user = auth()->user();
        
        $shippingCost = $city->shippingRate ? (float) $city->shippingRate->shipping_cost : 0.0;
        $subtotal = 0;
        $updatedItems = [];

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['id']);
            $priceData = $priceService->getProductPrice($product, $user, $city);
            
            $itemSubtotal = $priceData['price'] * $item['qty'];
            $subtotal += $itemSubtotal;
            
            $updatedItems[] = [
                'id' => $product->id,
                'name' => $product->name,
                'qty' => $item['qty'],
                'price' => $priceData['price'],
                'subtotal' => $itemSubtotal,
                'image' => $product->image_path ?? null,
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

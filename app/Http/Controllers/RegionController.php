<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Product;
use App\Services\ProductPriceService;
use App\Services\RajaOngkirService;
use App\Models\Setting;
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
    public function calculateTotals(Request $request, ProductPriceService $priceService, RajaOngkirService $rajaOngkir)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:regions,id',
            'courier' => 'nullable|string|in:jne,pos,tiki',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.variantLabel' => 'nullable|string|max:255',
        ]);

        $city = Region::with('shippingRate')->find($validated['city_id']);
        $user = auth()->user();
        
        $shippingCost = $city->shippingRate ? (float) $city->shippingRate->shipping_cost : 0.0;
        $subtotal = 0;
        $totalWeight = 0;
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
            $totalWeight += ((int) ($product->weight ?? 1000)) * $item['qty'];
            
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

        // Jika RajaOngkir diaktifkan dan valid, override shippingCost
        $rajaOngkirCostData = null;
        if ($rajaOngkir->isConfigured()) {
            $origin = Setting::where('key', 'store_origin_city_id')->value('value');
            $courier = $validated['courier'] ?? 'jne';
            
            if ($origin) {
                $destinationId = $this->getRajaOngkirCityId($validated['city_id'], $rajaOngkir);
                $originId = $origin; // Already a Komerce ID from admin settings

                if ($destinationId && $originId) {
                    $results = $rajaOngkir->getCost($originId, $destinationId, $totalWeight > 0 ? $totalWeight : 1000, $courier);
                    if (!empty($results)) {
                        $rajaOngkirCostData = $results[0]['costs'];
                        if (count($rajaOngkirCostData) > 0) {
                            $shippingCost = (float) $rajaOngkirCostData[0]['cost'][0]['value'];
                        }
                    }
                }
            }
        }

        $total = $subtotal + $shippingCost;

        return response()->json([
            'items' => $updatedItems,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $total,
            'rajaongkir_costs' => $rajaOngkirCostData,
        ]);
    }

    private function getRajaOngkirCityId($localCityId, $rajaOngkir)
    {
        $city = Region::find($localCityId);
        if (!$city) return null;
        
        $province = Region::find($city->parent_id);
        if (!$province) return null;

        $roProvinces = $rajaOngkir->getProvinces();
        $roProvinceId = null;
        
        $localProvName = strtolower($province->name);
        $cleanLocalProv = trim(str_replace(['daerah istimewa ', 'kepulauan ', 'nanggroe ', ' darussalam', 'di '], '', $localProvName));

        foreach ($roProvinces as $roProv) {
            $roProvName = strtolower($roProv['name'] ?? $roProv['province'] ?? '');
            $cleanRoProv = trim(str_replace(['daerah istimewa ', 'kepulauan ', 'nanggroe ', ' darussalam', ' (nad)', ' (ntb)', ' (ntt)', 'di '], '', $roProvName));
            
            if ($cleanRoProv === $cleanLocalProv || str_contains($cleanRoProv, $cleanLocalProv) || str_contains($cleanLocalProv, $cleanRoProv)) {
                $roProvinceId = $roProv['id'] ?? $roProv['province_id'];
                break;
            }
        }

        if (!$roProvinceId) return null;

        $rajaOngkirCities = $rajaOngkir->getCities($roProvinceId);
        $localName = strtolower(str_replace(['kota ', 'kabupaten ', 'kab. '], '', strtolower($city->name)));
        $cleanLocalName = trim($localName);
        
        foreach ($rajaOngkirCities as $roCity) {
            $roCityName = strtolower($roCity['name'] ?? $roCity['city_name'] ?? '');
            $cleanRoCity = trim(str_replace(['kota ', 'kabupaten ', 'kab. '], '', $roCityName));
            
            if ($cleanRoCity === $cleanLocalName || str_contains($cleanRoCity, $cleanLocalName) || str_contains($cleanLocalName, $cleanRoCity)) {
                return $roCity['id'] ?? $roCity['city_id'];
            }
        }

        return null;
    }
}

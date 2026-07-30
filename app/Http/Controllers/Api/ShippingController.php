<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Setting;

class ShippingController extends Controller
{
    protected RajaOngkirService $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    public function getProvinces(Request $request): JsonResponse
    {
        if ($request->has('api_key')) {
            $this->rajaOngkir->setCredentials($request->api_key, $request->type ?? 'starter');
        }
        $provinces = $this->rajaOngkir->getProvinces();
        return response()->json($provinces);
    }

    public function getCities(Request $request): JsonResponse
    {
        if ($request->has('api_key')) {
            $this->rajaOngkir->setCredentials($request->api_key, $request->type ?? 'starter');
        }
        $provinceId = $request->query('province');
        $cities = $this->rajaOngkir->getCities($provinceId);
        return response()->json($cities);
    }

    public function calculateCost(Request $request): JsonResponse
    {
        $request->validate([
            'destination' => 'required|numeric',
            'weight' => 'required|numeric|min:1',
            'courier' => 'required|string|in:jne,pos,tiki',
        ]);

        $origin = Setting::where('key', 'store_origin_city_id')->value('value');
        
        if (!$origin) {
            return response()->json(['error' => 'Kota asal pengiriman belum diatur oleh admin.'], 400);
        }

        $results = $this->rajaOngkir->getCost(
            $origin,
            $request->destination,
            (int) $request->weight,
            $request->courier
        );

        if (empty($results)) {
            return response()->json(['error' => 'Gagal menghitung ongkos kirim atau API belum terkonfigurasi.'], 500);
        }

        return response()->json($results[0]);
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RajaOngkirService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = Setting::where('key', 'rajaongkir_api_key')->value('value');
        $this->baseUrl = 'https://rajaongkir.komerce.id/api/v1';
    }

    public function setCredentials(?string $apiKey, ?string $type = null): void
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = 'https://rajaongkir.komerce.id/api/v1';
    }

    public function isConfigured(): bool
    {
        $enabled = Setting::where('key', 'rajaongkir_enabled')->value('value');
        if ($enabled === '0' || $enabled === 'false' || empty($enabled)) {
            return false;
        }
        return !empty($this->apiKey);
    }

    public function getProvinces(): array
    {
        if (!$this->isConfigured()) return [];

        if (Cache::has('rajaongkir_provinces')) {
            return Cache::get('rajaongkir_provinces');
        }

        $response = Http::withHeaders(['key' => $this->apiKey])
            ->get($this->baseUrl . '/destination/province');
        
        if ($response->successful()) {
            $json = $response->json();
            // Support both V2 (data) and V1 (rajaongkir.results) just in case
            $results = $json['data'] ?? $json['rajaongkir']['results'] ?? [];
            if (!empty($results)) {
                Cache::put('rajaongkir_provinces', $results, 86400);
                return $results;
            }
        }

        return [];
    }

    public function getCities($provinceId = null): array
    {
        if (!$this->isConfigured()) return [];

        $cacheKey = 'rajaongkir_cities' . ($provinceId ? '_' . $provinceId : '');
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $url = $this->baseUrl . '/destination/city';
        if ($provinceId) {
            $url .= '/' . $provinceId;
        }
        
        $response = Http::withHeaders(['key' => $this->apiKey])
            ->get($url);
        
        if ($response->successful()) {
            $json = $response->json();
            $results = $json['data'] ?? $json['rajaongkir']['results'] ?? [];
            if (!empty($results)) {
                Cache::put($cacheKey, $results, 86400);
                return $results;
            }
        }

        return [];
    }

    public function getCost(int|string $origin, int|string $destination, int $weight, string $courier): array
    {
        if (!$this->isConfigured()) return [];

        // Try V2 endpoint first
        $response = Http::asForm()->withHeaders(['key' => $this->apiKey])
            ->post($this->baseUrl . '/calculate/domestic-cost', [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weight,
                'courier' => $courier
            ]);

        if ($response->successful()) {
            $json = $response->json();
            if (isset($json['data'])) {
                $v2Data = $json['data'];
                if (empty($v2Data)) return [];

                $costs = [];
                $courierCode = $v2Data[0]['code'] ?? $courier;
                $courierName = $v2Data[0]['name'] ?? strtoupper($courier);

                foreach ($v2Data as $item) {
                    $costs[] = [
                        'service' => $item['service'] ?? '',
                        'description' => $item['description'] ?? '',
                        'cost' => [
                            [
                                'value' => $item['cost'] ?? 0,
                                'etd' => $item['etd'] ?? '',
                                'note' => ''
                            ]
                        ]
                    ];
                }

                return [
                    [
                        'code' => $courierCode,
                        'name' => $courierName,
                        'costs' => $costs
                    ]
                ];
            }
            if (isset($json['rajaongkir']['results'])) {
                return $json['rajaongkir']['results'];
            }
        }

        // Fallback to V1 endpoint if V2 fails (404)
        if ($response->status() === 404) {
            $responseV1 = Http::withHeaders(['key' => $this->apiKey])
                ->post($this->baseUrl . '/cost', [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier
                ]);
            if ($responseV1->successful() && isset($responseV1->json()['rajaongkir']['results'])) {
                return $responseV1->json()['rajaongkir']['results'];
            }
        }
        
        return [];
    }
}

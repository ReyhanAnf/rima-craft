<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\ShippingRate;
use Illuminate\Database\Seeder;

class ShippingRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeder Tarif Ongkos Kirim untuk masing-masing kota/kabupaten
        $rates = [
            'Kota Jakarta Pusat' => 15000,
            'Kota Jakarta Utara' => 15000,
            'Kota Jakarta Barat' => 15000,
            'Kota Jakarta Selatan' => 15000,
            'Kota Jakarta Timur' => 15000,
            'Kota Bandung' => 20000,
            'Kab. Bandung' => 22000,
            'Kota Bogor' => 18000,
            'Kota Depok' => 16000,
            'Kota Bekasi' => 16000,
            'Kota Semarang' => 25000,
            'Kota Surakarta (Solo)' => 25000,
            'Kab. Banyumas' => 28000,
            'Kota Surabaya' => 30000,
            'Kota Malang' => 32000,
            'Kab. Sidoarjo' => 30000,
            'Kota Yogyakarta' => 25000,
            'Kab. Sleman' => 26000,
            'Kab. Bantul' => 26000,
            'Kota Denpasar' => 35000,
            'Kab. Badung' => 38000,
        ];

        foreach ($rates as $cityName => $cost) {
            $city = Region::where('type', 'city')->where('name', $cityName)->first();
            if ($city) {
                ShippingRate::updateOrCreate(
                    ['region_id' => $city->id],
                    ['shipping_cost' => $cost]
                );
            }
        }
    }
}

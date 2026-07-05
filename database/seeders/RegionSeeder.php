<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to truncate/re-seed cleanly
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \Illuminate\Support\Facades\DB::table('regions')->truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 1. Seed Provinces
        $provincesPath = resource_path('data/json/provinces.json');
        if (!file_exists($provincesPath)) {
            $this->command->error("Provinces file not found at: {$provincesPath}");
            return;
        }
        $provinces = json_decode(file_get_contents($provincesPath), true);
        
        $provinceIds = [];
        foreach ($provinces as $prov) {
            $inserted = Region::create([
                'code'      => $prov['id'],
                'name'      => $prov['name'],
                'type'      => 'province',
                'parent_id' => null,
            ]);
            $provinceIds[$prov['id']] = $inserted->id;
        }

        // 2. Seed Regencies (Cities)
        $regenciesPath = resource_path('data/json/regencies.json');
        if (!file_exists($regenciesPath)) {
            $this->command->error("Regencies file not found at: {$regenciesPath}");
            return;
        }
        $regencies = json_decode(file_get_contents($regenciesPath), true);

        $regencyIds = [];
        foreach ($regencies as $reg) {
            $parentProvinceId = $provinceIds[$reg['province_id']] ?? null;
            $inserted = Region::create([
                'code'      => $reg['id'],
                'name'      => $reg['name'],
                'type'      => 'city',
                'parent_id' => $parentProvinceId,
            ]);
            $regencyIds[$reg['id']] = $inserted->id;
        }

        // 3. Seed Villages
        $villagesPath = resource_path('data/json/villages.json');
        if (!file_exists($villagesPath)) {
            $this->command->error("Villages file not found at: {$villagesPath}");
            return;
        }
        $villages = json_decode(file_get_contents($villagesPath), true);

        $villagesData = [];
        $now = now();
        foreach ($villages as $vil) {
            // First 4 characters of district_id correspond to the regency (city) code
            $regencyCode = substr($vil['district_id'], 0, 4);
            $parentRegencyId = $regencyIds[$regencyCode] ?? null;

            $villagesData[] = [
                'code'       => $vil['id'],
                'name'       => $vil['name'],
                'type'       => 'village',
                'parent_id'  => $parentRegencyId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($villagesData) >= 1000) {
                \Illuminate\Support\Facades\DB::table('regions')->insert($villagesData);
                $villagesData = [];
            }
        }

        if (!empty($villagesData)) {
            \Illuminate\Support\Facades\DB::table('regions')->insert($villagesData);
        }
    }
}

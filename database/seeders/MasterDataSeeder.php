<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Material;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Contacts
        $contacts = [
            ['type' => 'supplier', 'name' => 'PT Makmur Kayu', 'phone' => '08111222333', 'address' => 'Jl. Kayu Manis 1'],
            ['type' => 'supplier', 'name' => 'Toko Kain Abadi', 'phone' => '08222333444', 'address' => 'Ps. Tanah Abang Blok A'],
            ['type' => 'crafter', 'name' => 'Bapak Budi (Tukang Anyam)', 'phone' => '08333444555', 'address' => 'Desa Pandanrejo'],
            ['type' => 'customer', 'name' => 'Ibu Siti (Reseller)', 'phone' => '08444555666', 'address' => 'Bandung'],
        ];

        foreach ($contacts as $contact) {
            Contact::firstOrCreate(['name' => $contact['name']], $contact);
        }

        // Materials
        $materials = [
            ['name' => 'Pandan Laut Kering', 'unit' => 'kg', 'min_stock' => 10, 'current_stock' => 25, 'last_buy_price' => 15000],
            ['name' => 'Kayu Mahoni 2x2', 'unit' => 'meter', 'min_stock' => 20, 'current_stock' => 50, 'last_buy_price' => 12000],
            ['name' => 'Kain Furing', 'unit' => 'roll', 'min_stock' => 2, 'current_stock' => 5, 'last_buy_price' => 250000],
        ];

        foreach ($materials as $material) {
            Material::firstOrCreate(['name' => $material['name']], $material);
        }

        // Products
        $products = [
            ['name' => 'Tas Anyaman Pandan Klasik', 'description' => 'Tas tangan berbahan dasar pandan laut.', 'base_price' => 85000, 'current_stock' => 15],
            ['name' => 'Kotak Tisu Kayu Aesthetic', 'description' => 'Kotak tisu kayu mahoni dengan tutup anyaman.', 'base_price' => 45000, 'current_stock' => 30],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['name' => $product['name']], $product);
        }
    }
}

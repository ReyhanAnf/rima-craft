<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rajaOngkir = app()->make(\App\Services\RajaOngkirService::class);
$city = \App\Models\Region::find(35);
$province = \App\Models\Region::find($city->parent_id);
$localProvName = strtolower($province->name);
$cleanLocalProv = trim(str_replace(['daerah istimewa ', 'kepulauan ', 'nanggroe ', ' darussalam', 'di '], '', $localProvName));
echo "Clean Local Prov: " . $cleanLocalProv . "\n";
$roProvinces = $rajaOngkir->getProvinces();
foreach ($roProvinces as $roProv) {
    $roProvName = strtolower($roProv['name'] ?? $roProv['province'] ?? '');
    $cleanRoProv = trim(str_replace(['daerah istimewa ', 'kepulauan ', 'nanggroe ', ' darussalam', ' (nad)', ' (ntb)', ' (ntt)', 'di '], '', $roProvName));
    if (str_contains($roProvName, 'aceh')) echo "API: " . $roProvName . " -> Clean: " . $cleanRoProv . "\n";
}

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rajaOngkir = app()->make(\App\Services\RajaOngkirService::class);
$city = \App\Models\Region::find(35);
echo "Local City: " . $city->name . "\n";
$localName = strtolower(str_replace(['Kota ', 'Kabupaten ', 'Kab. '], '', $city->name));
$province = \App\Models\Region::find($city->parent_id);
$localProvName = strtolower($province->name);
echo "Local Prov: " . $localProvName . "\n";
$roProvinces = $rajaOngkir->getProvinces();
$roProvinceId = null;
foreach ($roProvinces as $roProv) {
if (strtolower($roProv['name'] ?? $roProv['province'] ?? '') === $localProvName) {
$roProvinceId = $roProv['id'] ?? $roProv['province_id'];
break;
}
}
echo "RO Prov ID: " . ($roProvinceId ?: 'NOT FOUND') . "\n";
if ($roProvinceId) {
$rajaOngkirCities = $rajaOngkir->getCities($roProvinceId);
$match = null;
foreach ($rajaOngkirCities as $roCity) {
$roCityName = strtolower($roCity['name'] ?? $roCity['city_name'] ?? '');
if ($roCityName === trim($localName)) {
$match = $roCity;
break;
}
}
var_dump($match);
}

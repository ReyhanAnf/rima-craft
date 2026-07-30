<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rajaOngkir = app()->make(\App\Services\RajaOngkirService::class);
$roCities = $rajaOngkir->getCities(9);
foreach ($roCities as $roCity) {
    $roCityName = strtolower($roCity['name'] ?? $roCity['city_name'] ?? '');
    if (str_contains($roCityName, 'simeulue')) echo "City: " . $roCityName . "\n";
}

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$controller = app()->make(\App\Http\Controllers\RegionController::class);
$rajaOngkir = app()->make(\App\Services\RajaOngkirService::class);
$method = new ReflectionMethod($controller, 'getRajaOngkirCityId');
$method->setAccessible(true);
$origin = \App\Models\Setting::where('key', 'store_origin_city_id')->value('value');
echo "Origin Local ID: " . $origin . "\n";
$originId = $method->invoke($controller, $origin, $rajaOngkir);
echo "Origin Komerce ID: " . $originId . "\n";
$destId = $method->invoke($controller, 35, $rajaOngkir);
echo "Dest Komerce ID (Simeulue): " . $destId . "\n";
$results = $rajaOngkir->getCost($originId, $destId, 1000, 'jne');
var_dump($results);

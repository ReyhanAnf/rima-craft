<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rajaOngkir = app()->make(\App\Services\RajaOngkirService::class);
$roProvinces = $rajaOngkir->getProvinces();
foreach ($roProvinces as $roProv) {
echo $roProv['id'] . ": " . $roProv['name'] . "\n";
}

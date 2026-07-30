<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$controller = app()->make(\App\Http\Controllers\RegionController::class);
$req = Illuminate\Http\Request::create('/api/regions/calculate', 'POST', [
'city_id' => 1,
'courier' => 'jne',
'items' => [['id' => 1, 'qty' => 1]]
]);
$res = $controller->calculateTotals($req, app()->make(\App\Services\ProductPriceService::class), app()->make(\App\Services\RajaOngkirService::class));
echo substr($res->getContent(), 0, 500) . "\n";

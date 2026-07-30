<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$controller = app()->make(\App\Http\Controllers\Api\ShippingController::class);
$req = Illuminate\Http\Request::create('/api/shipping/cities', 'GET', ['api_key' => \App\Models\Setting::where('key', 'rajaongkir_api_key')->value('value'), 'province' => 5]);
$res = $controller->getCities($req);
echo substr($res->getContent(), 0, 500) . "\n";

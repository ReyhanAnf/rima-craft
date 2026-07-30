<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$controller = app()->make(\App\Http\Controllers\Api\ShippingController::class);
$req = Illuminate\Http\Request::create('/api/shipping/provinces', 'GET', ['api_key' => \App\Models\Setting::where('key', 'rajaongkir_api_key')->value('value')]);
$res = $controller->getProvinces($req);
echo "Done\n";

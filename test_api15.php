<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$controller = app()->make(\App\Http\Controllers\Api\ShippingController::class);
$req = Illuminate\Http\Request::create('/api/shipping/cities?province=1', 'GET');
$res = $controller->getCities($req);
var_dump($res->getContent());

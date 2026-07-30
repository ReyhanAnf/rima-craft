<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$prov = \App\Models\Setting::where('key', 'store_origin_province_id')->first();
$city = \App\Models\Setting::where('key', 'store_origin_city_id')->first();
echo "Prov: " . ($prov->value ?? 'null') . "\n";
echo "City: " . ($city->value ?? 'null') . "\n";

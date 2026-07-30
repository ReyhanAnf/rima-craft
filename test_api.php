<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$key = \App\Models\Setting::where('key', 'rajaongkir_api_key')->value('value');
echo "API KEY: " . substr($key, 0, 5) . "...\n";
$res = Illuminate\Support\Facades\Http::withHeaders(['key' => $key])->get('https://rajaongkir.komerce.id/api/v1/destination/province');
echo "Status: " . $res->status() . "\n";
echo "Body: " . $res->body() . "\n";

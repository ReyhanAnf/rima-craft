<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$key = \App\Models\Setting::where('key', 'rajaongkir_api_key')->value('value');
$res = Illuminate\Support\Facades\Http::asForm()->withHeaders(['key' => $key])->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
'origin' => 55,
'destination' => 114,
'weight' => 1000,
'courier' => 'jne'
]);
echo "Status: " . $res->status() . "\n";
echo "Body: " . substr($res->body(), 0, 500) . "\n";

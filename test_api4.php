<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$key = \App\Models\Setting::where('key', 'rajaongkir_api_key')->value('value');
$endpoints = [
'destination/city?province=5',
'destination/city/5',
'destination/regency?province=5',
'destination/regency?id_province=5',
'destination/regency/5',
'destination/city?id_province=5'
];
foreach($endpoints as $ep) {
$res = Illuminate\Support\Facades\Http::withHeaders(['key' => $key])->get('https://rajaongkir.komerce.id/api/v1/' . $ep);
echo $ep . " -> " . $res->status() . "\n";
}

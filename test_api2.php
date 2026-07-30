<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
$req = Illuminate\Http\Request::create('/api/shipping/provinces', 'GET', ['api_key' => \App\Models\Setting::where('key', 'rajaongkir_api_key')->value('value')]);
$res = app()->handle($req);
echo "Status: " . $res->status() . "\n";
echo "Body: " . $res->getContent() . "\n";
} catch (\Exception $e) {
echo $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile();
}

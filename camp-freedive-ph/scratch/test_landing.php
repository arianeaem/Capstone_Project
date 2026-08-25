<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$res = app()->handle(Illuminate\Http\Request::create('/', 'GET'));
echo "Status: " . $res->getStatusCode() . "\n";
echo "Length: " . strlen($res->getContent()) . "\n";
echo "Contains Image: " . (str_contains($res->getContent(), 'AdobeStock_272067459.jpeg') ? 'YES' : 'NO') . "\n";

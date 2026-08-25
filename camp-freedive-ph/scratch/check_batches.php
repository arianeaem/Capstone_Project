<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(App\Models\Batch::all() as $b) {
    echo "ID: {$b->id} | batch_number: {$b->batch_number} | name: {$b->name} | code: {$b->batch_code}\n";
}

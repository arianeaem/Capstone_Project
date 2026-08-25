<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'owner')->first();
Illuminate\Support\Facades\Auth::login($user);

try {
    $c = app(App\Http\Controllers\Admin\RefundController::class);
    $view = $c->index(request());
    $html = $view->render();
    echo "Render SUCCESS (" . strlen($html) . " bytes)\n";
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

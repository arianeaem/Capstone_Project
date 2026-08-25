<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$owner = User::where('role', 'owner')->first();
Auth::login($owner);

$testRoutes = [
    '/admin/coaches/matching',
    '/admin/settings/users',
    '/admin/users',
    '/admin/payments',
];

foreach ($testRoutes as $path) {
    try {
        $req = Illuminate\Http\Request::create($path, 'GET');
        $req->setUserResolver(fn() => $owner);
        $res = app()->handle($req);
        echo "Route: {$path} => Status: " . $res->getStatusCode() . "\n";
        if ($res->getStatusCode() >= 400 || $res->getStatusCode() === 500) {
            echo "   Error: " . substr($res->getContent(), 0, 500) . "\n";
        }
    } catch (\Throwable $e) {
        echo "Route: {$path} => Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

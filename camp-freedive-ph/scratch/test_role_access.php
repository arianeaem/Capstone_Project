<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING ACCESS FOR ADMIN AND OWNER ROLES ===\n\n";

$roles = ['owner', 'admin'];

$routes = [
    '/admin/coaches/matching',
    '/admin/settings/users',
    '/admin/users',
    '/admin/settings/audit-logs',
    '/admin/audit-logs',
    '/admin/payments',
    '/admin/payments/refunds',
    '/admin/bookings/requests',
    '/admin/batches/create',
];

foreach ($roles as $role) {
    $user = User::where('role', $role)->first();
    if (!$user) {
        $user = User::factory()->create([
            'role' => $role,
            'name' => ucfirst($role) . ' User',
            'email' => "{$role}@test.com",
            'status' => 'active',
            'must_change_password' => false,
        ]);
    } else {
        $user->status = 'active';
        $user->must_change_password = false;
        $user->save();
    }
    
    Auth::login($user);
    echo "--- Testing as Role: {$role} ({$user->email}) ---\n";

    foreach ($routes as $path) {
        $req = Illuminate\Http\Request::create($path, 'GET');
        $req->setUserResolver(fn() => $user);
        $res = app()->handle($req);
        
        $status = $res->getStatusCode();
        echo sprintf("  %-30s => Status: %d %s\n", $path, $status, $status === 200 ? 'SUCCESS' : ($res->isRedirection() ? 'REDIRECT to ' . $res->headers->get('Location') : 'ERROR'));
        if ($status >= 400 && $status !== 404) {
            echo "     Error snippet: " . substr(strip_tags($res->getContent()), 0, 300) . "\n";
        }
    }
    echo "\n";
}

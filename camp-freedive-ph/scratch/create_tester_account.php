<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$tester = User::updateOrCreate(
    ['email' => 'group8@campfreedive.ph'],
    [
        'name' => 'Group 8 Peer Tester',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
        'status' => 'active',
        'must_change_password' => false,
        'phone' => '09170000008',
    ]
);

echo "Tester account created/updated: {$tester->email} (ID: {$tester->id}, Role: {$tester->role})\n";

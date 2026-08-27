<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PricingRule;
use App\Models\User;

$owner = User::where('role', 'owner')->first();
$admin = User::where('role', 'admin')->first();

$pricingRules = [
    [
        'name' => 'Peak Season Discovery Bump',
        'description' => 'Standard seasonal surcharge during peak Amihan diving months (Nov - Apr).',
        'rule_type' => 'seasonality',
        'condition_operator' => null,
        'condition_value' => 'peak',
        'applies_to' => 'all',
        'adjustment_type' => 'increase',
        'adjustment_method' => 'percentage',
        'adjustment_value' => 10.00,
        'priority' => 1,
        'status' => 'active',
        'created_by' => $owner ? $owner->id : null,
    ],
    [
        'name' => 'Off-Peak Seasonal Incentive',
        'description' => 'Discounts to stimulate bookings and raise camp occupancy during off-peak rainy season (Jun - Sep).',
        'rule_type' => 'seasonality',
        'condition_operator' => null,
        'condition_value' => 'off_peak',
        'applies_to' => 'all',
        'adjustment_type' => 'decrease',
        'adjustment_method' => 'percentage',
        'adjustment_value' => 15.00,
        'priority' => 2,
        'status' => 'active',
        'created_by' => $owner ? $owner->id : null,
    ],
    [
        'name' => 'High Demand Surge',
        'description' => 'Applies when batch occupancy exceeds 60% capacity.',
        'rule_type' => 'demand',
        'condition_operator' => null,
        'condition_value' => 'high',
        'applies_to' => 'all',
        'adjustment_type' => 'increase',
        'adjustment_method' => 'percentage',
        'adjustment_value' => 10.00,
        'priority' => 3,
        'status' => 'active',
        'created_by' => $owner ? $owner->id : null,
    ],
    [
        'name' => 'Early Bird Booking Reward',
        'description' => 'Fixed discount incentive for divers booking at least 30 days in advance.',
        'rule_type' => 'lead_time',
        'condition_operator' => '>=',
        'condition_value' => '30',
        'applies_to' => 'all',
        'adjustment_type' => 'decrease',
        'adjustment_method' => 'fixed',
        'adjustment_value' => 300.00,
        'priority' => 4,
        'status' => 'active',
        'created_by' => $admin ? $admin->id : null,
    ],
    [
        'name' => 'Last-Minute Rush Adjustment',
        'description' => 'Surcharge for reservations made within 3 days of departure.',
        'rule_type' => 'lead_time',
        'condition_operator' => '<=',
        'condition_value' => '3',
        'applies_to' => 'all',
        'adjustment_type' => 'increase',
        'adjustment_method' => 'fixed',
        'adjustment_value' => 400.00,
        'priority' => 5,
        'status' => 'active',
        'created_by' => $admin ? $admin->id : null,
    ],
];

foreach ($pricingRules as $r) {
    PricingRule::updateOrCreate(['name' => $r['name']], $r);
}

echo "Seeded " . PricingRule::count() . " pricing rules successfully!\n";

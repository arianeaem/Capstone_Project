<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Mail\BatchWeatherCancellationMail;
use App\Mail\BookingConfirmedMail;
use App\Mail\CancellationApprovedMail;
use App\Mail\CancellationRejectedMail;
use App\Mail\CancellationRequestedMail;
use App\Mail\PasswordResetMail;
use App\Mail\RescheduleApprovedMail;
use App\Mail\RescheduleRejectedMail;
use App\Mail\RescheduleRequestedMail;
use App\Models\Batch;
use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\BookingPriceAdjustment;
use App\Models\CancellationRequest;
use App\Models\RescheduleRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

$targetEmail = 'arianemaebgusto@gmail.com';
echo "Starting dispatch of ALL 9 email samples to: {$targetEmail}...\n\n";

// 1. Create Base Booking Model
$booking = new Booking([
    'booking_number' => 'CFP-2026-1002',
    'pin' => '9824',
    'contact_name' => 'Ariane Mae Gusto',
    'contact_email' => $targetEmail,
    'contact_phone' => '+63 917 123 4567',
    'class_type' => 'discovery',
    'start_date' => Carbon::parse('2026-10-03'),
    'end_date' => Carbon::parse('2026-10-04'),
    'pickup_option' => 'carpool',
    'pickup_location' => "McDonald's El Pueblo (Ortigas) - 2:00 AM",
    'boat_dive' => true,
    'lgu_pass_included' => true,
    'subtotal' => 7000.00,
    'downpayment_amount' => 3000.00,
    'balance_amount' => 4000.00,
    'total_amount' => 7000.00,
    'paid_amount' => 3000.00,
    'status' => 'confirmed',
]);
$booking->id = 1002;
$booking->exists = true;

// Participants
$p1 = new BookingParticipant(['name' => 'Ariane Mae Gusto', 'age' => 24, 'gender' => 'Female', 'shoe_size' => '38 EU']);
$p2 = new BookingParticipant(['name' => 'Bryan Gusto', 'age' => 26, 'gender' => 'Male', 'shoe_size' => '42 EU']);
$booking->setRelation('participants', collect([$p1, $p2]));

// Pricing adjustments
$adj1 = new BookingPriceAdjustment(['rule_name' => 'Early Bird Diver Reward', 'adjustment_amount' => -500.00]);
$adj2 = new BookingPriceAdjustment(['rule_name' => 'Habagat Monsoon Incentive', 'adjustment_amount' => -250.00]);
$booking->setRelation('priceAdjustments', collect([$adj1, $adj2]));

// 2. Cancellation Request Model
$cancellationRequest = new CancellationRequest([
    'booking_id' => $booking->id,
    'calculated_refund_amount' => 3000.00,
    'reason' => 'Emergency schedule conflict with work travel.',
    'force_majeure_flag' => false,
    'status' => 'pending',
]);
$cancellationRequest->setRelation('booking', $booking);

// 3. Reschedule Request Model
$rescheduleRequest = new RescheduleRequest([
    'booking_id' => $booking->id,
    'current_start_date' => Carbon::parse('2026-10-03'),
    'current_end_date' => Carbon::parse('2026-10-04'),
    'requested_start_date' => Carbon::parse('2026-10-17'),
    'requested_end_date' => Carbon::parse('2026-10-18'),
    'reason' => 'Family emergency on original scheduled weekend.',
    'status' => 'pending',
]);
$rescheduleRequest->setRelation('booking', $booking);

// 4. Batch Model
$batch = new Batch([
    'name' => 'Batch 5',
    'batch_code' => 'Batch 5',
    'start_date' => Carbon::parse('2026-10-03'),
    'end_date' => Carbon::parse('2026-10-04'),
    'status' => 'cancelled_by_camp',
]);
$batch->id = 5;

// 5. User Model for Password Reset
$user = new User([
    'name' => 'Ariane Mae Gusto',
    'email' => $targetEmail,
    'role' => 'admin',
]);
$user->id = 1;

// List of all 9 Mailable instances
$mailables = [
    '1. Booking Confirmed' => new BookingConfirmedMail($booking),
    '2. Cancellation Request Received' => new CancellationRequestedMail($booking, $cancellationRequest),
    '3. Cancellation Approved (Refund Queued)' => new CancellationApprovedMail($booking, $cancellationRequest, 3000.00, false),
    '4. Cancellation Rejected' => new CancellationRejectedMail($booking, $cancellationRequest, 'Cancellation request submitted less than 48 hours prior to trip without valid medical certificate.'),
    '5. Reschedule Request Received' => new RescheduleRequestedMail($booking, $rescheduleRequest),
    '6. Reschedule Approved' => new RescheduleApprovedMail($booking, $rescheduleRequest),
    '7. Reschedule Rejected' => new RescheduleRejectedMail($booking, $rescheduleRequest, 'Requested new weekend batch is currently at full coach and boat capacity.'),
    '8. Batch Weather Safety Cancellation' => new BatchWeatherCancellationMail($booking, $batch, 'PAGASA Heavy Rainfall Warning & PCG Sea Travel Gale Advisory.'),
    '9. Password Reset' => new PasswordResetMail('https://campfreedive.ph/password/reset/sample-token-12345', $user),
];

foreach ($mailables as $name => $mailable) {
    try {
        echo "Sending [{$name}]... ";
        Mail::to($targetEmail)->send($mailable);
        echo "SUCCESS\n";
    } catch (\Throwable $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}

echo "\nAll email dispatches finished!\n";

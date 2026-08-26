<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Mail\BookingConfirmedMail;
use Illuminate\Support\Facades\Mail;

$booking = Booking::with('participants', 'payments')->latest()->first();

if (!$booking) {
    echo "No booking found to test.\n";
    exit(0);
}

echo "Testing email build for Booking #{$booking->booking_number} ({$booking->contact_email})...\n";

$mailable = new BookingConfirmedMail($booking);
$rendered = $mailable->render();

echo "Rendered Email HTML Length: " . strlen($rendered) . " bytes\n";
echo "Contains Booking Number: " . (str_contains($rendered, $booking->booking_number) ? 'YES ✓' : 'NO ✗') . "\n";
echo "Contains PIN: " . (str_contains($rendered, $booking->pin) ? 'YES ✓' : 'NO ✗') . "\n";
echo "Contains Things to Bring: " . (str_contains($rendered, 'Things to Bring') ? 'YES ✓' : 'NO ✗') . "\n";

// Test Mail::to send
Mail::to($booking->contact_email)->send($mailable);
echo "Mail::send executed successfully! ✓\n";

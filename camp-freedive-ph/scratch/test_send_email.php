<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Mail\BookingConfirmedMail;
use Illuminate\Support\Facades\Mail;

$recipient = $argv[1] ?? 'aodkfldksmae@gmail.com';

echo "=== CAMP FREEDIVEPH EMAIL DISPATCH TEST ===\n";
echo "Mailer: " . config('mail.default') . "\n";
echo "Host: " . config('mail.mailers.smtp.host') . ":" . config('mail.mailers.smtp.port') . "\n";
echo "Sender: " . config('mail.from.address') . " (" . config('mail.from.name') . ")\n";
echo "Recipient: {$recipient}\n\n";

$booking = Booking::with(['participants', 'payments'])->latest()->first();

if (!$booking) {
    echo "Error: No bookings found in database to test.\n";
    exit(1);
}

echo "Testing dispatch with Booking #{$booking->booking_number}...\n";

try {
    Mail::to($recipient)->send(new BookingConfirmedMail($booking));
    echo "SUCCESS! Confirmation email successfully sent to {$recipient}.\n";
} catch (\Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    if (str_contains($e->getMessage(), '535') || str_contains($e->getMessage(), 'BadCredentials')) {
        echo "\nNOTE: Google SMTP requires a 16-character App Password.\n";
        echo "Please generate one at https://myaccount.google.com/apppasswords and set MAIL_PASSWORD in .env.\n";
    }
}

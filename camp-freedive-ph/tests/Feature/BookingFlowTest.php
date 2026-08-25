<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Camp FreedivePH');
        $response->assertSee('Discovery');
        $response->assertSee('Fundive');
        $response->assertSee('Refinement');
        $response->assertSee('Manage Booking');
    }

    public function test_booking_wizard_page_loads(): void
    {
        $response = $this->get('/book?class=discovery');
        $response->assertStatus(200);
        $response->assertSee('Select Class');
        $response->assertSee('Discovery');
        $response->assertSee('2:30 AM');
    }

    public function test_weather_check_endpoint(): void
    {
        $response = $this->postJson('/api/weather/check', [
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(8)->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'risk_level',
            'title',
            'badge_color',
            'description',
            'is_bookable',
            'day1' => ['date', 'classification', 'recommended_action'],
            'day2' => ['date', 'classification', 'recommended_action'],
        ]);
    }

    public function test_complete_booking_submission_creates_booking_and_payment(): void
    {
        $startDate = now()->addDays(25)->format('Y-m-d');
        $endDate = now()->addDays(26)->format('Y-m-d');

        $payload = [
            'class_type' => 'discovery',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'participants' => [
                [
                    'name' => 'Ariane Mae',
                    'age' => 25,
                    'health_condition' => 'None',
                    'swimmer_status' => 'non_swimmer',
                ],
                [
                    'name' => 'Bryan Santos',
                    'age' => 26,
                    'health_condition' => 'Mild dust allergy',
                    'swimmer_status' => 'swimmer',
                ],
            ],
            'contact_name' => 'Ariane Mae',
            'contact_email' => 'ariane@example.com',
            'contact_phone' => '09171234567',
            'contact_facebook' => 'https://www.facebook.com/arianemae',
            'pickup_option' => 'carpool',
            'pickup_location' => 'Shell Tiendesitas (Pasig) - 3:00 AM',
            'boat_dive' => true,
            'confirmation_ack' => true,
            'payment_method' => 'gcash',
        ];

        $response = $this->postJson('/book', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'booking_number',
            'pin',
            'booking_id',
            'downpayment_paid',
            'balance_due',
            'manage_url',
        ]);

        // 3,000 per head * 2 = 6,000.00
        $this->assertDatabaseHas('bookings', [
            'contact_email' => 'ariane@example.com',
            'class_type' => 'discovery',
            'pickup_option' => 'carpool',
            'downpayment_amount' => 6000.00,
        ]);

        $this->assertDatabaseCount('booking_participants', 2);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_manage_booking_lookup_and_policy_evaluation(): void
    {
        $startDate = now()->addDays(20)->format('Y-m-d');
        $endDate = now()->addDays(21)->format('Y-m-d');

        $booking = Booking::create([
            'booking_number' => 'CFP-2026-9999',
            'pin' => '1234',
            'class_type' => 'discovery',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'pickup_option' => 'carpool',
            'pickup_location' => 'Shell Tiendesitas (Pasig) - 3:00 AM',
            'carpool_fee' => 2000.00,
            'boat_dive' => false,
            'boat_dive_fee' => 0.00,
            'lgu_fee' => 300.00,
            'environmental_fee' => 50.00,
            'subtotal' => 4250.00,
            'total_amount' => 6600.00,
            'downpayment_amount' => 3000.00,
            'balance_amount' => 3600.00,
            'contact_name' => 'Juan Test',
            'contact_email' => 'juan@example.com',
            'contact_phone' => '09170000000',
            'status' => 'confirmed',
        ]);

        // Search with wrong PIN
        $searchFail = $this->post('/manage-booking/search', [
            'booking_number' => 'CFP-2026-9999',
            'pin' => '9999',
        ]);
        $searchFail->assertSessionHas('error', 'Booking not found - please check your details.');

        // Search with correct PIN
        $searchSuccess = $this->post('/manage-booking/search', [
            'booking_number' => 'CFP-2026-9999',
            'pin' => '1234',
        ]);
        $searchSuccess->assertRedirect(route('manage.show', ['booking_number' => 'CFP-2026-9999', 'pin' => '1234']));

        // Show detail page
        $showResponse = $this->get('/manage-booking/CFP-2026-9999?pin=1234');
        $showResponse->assertStatus(200);
        $showResponse->assertSee('CFP-2026-9999');
        $showResponse->assertSee('Juan Test');
        $showResponse->assertSee('Allowed');

        // Submit reschedule request
        $newStart = now()->addDays(30)->format('Y-m-d');
        $newEnd = now()->addDays(31)->format('Y-m-d');
        $reschedResponse = $this->post('/manage-booking/CFP-2026-9999/reschedule', [
            'pin' => '1234',
            'requested_start_date' => $newStart,
            'requested_end_date' => $newEnd,
            'reason' => 'Schedule adjustment',
        ]);
        $reschedResponse->assertRedirect();
        $this->assertDatabaseHas('reschedule_requests', [
            'booking_id' => $booking->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'reschedule_requested',
        ]);
    }

    public function test_booking_fails_when_exceeding_batch_capacity_of_45_pax(): void
    {
        $startDate = now()->addDays(28)->format('Y-m-d');
        $endDate = now()->addDays(29)->format('Y-m-d');

        // Create an existing booking with 44 participants on that date
        $existingBooking = Booking::create([
            'booking_number' => 'CFP-2026-8888',
            'pin' => '1234',
            'class_type' => 'discovery',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'pickup_option' => 'own',
            'carpool_fee' => 0,
            'boat_dive' => false,
            'boat_dive_fee' => 0,
            'lgu_fee' => 300 * 44,
            'environmental_fee' => 50 * 44,
            'subtotal' => 4250 * 44,
            'total_amount' => 4600 * 44,
            'downpayment_amount' => 2000 * 44,
            'balance_amount' => 2600 * 44,
            'contact_name' => 'Existing Group Leader',
            'contact_email' => 'group@example.com',
            'contact_phone' => '09170000000',
            'status' => 'confirmed',
        ]);

        for ($i = 1; $i <= 44; $i++) {
            $existingBooking->participants()->create([
                'name' => "Participant {$i}",
                'age' => 25,
                'price_per_person' => 4250.00,
            ]);
        }

        // Try booking 2 more participants (44 + 2 = 46 > 45 max)
        $response = $this->postJson('/book', [
            'class_type' => 'discovery',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'participants' => [
                ['name' => 'User A', 'age' => 25],
                ['name' => 'User B', 'age' => 26],
            ],
            'contact_name' => 'Overflow Guest',
            'contact_email' => 'overflow@example.com',
            'contact_phone' => '09171234567',
            'pickup_option' => 'own',
            'boat_dive' => false,
            'confirmation_ack' => true,
            'payment_method' => 'gcash',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'success' => false,
        ]);
    }

    public function test_booking_validation_rejects_invalid_phone_number(): void
    {
        $response = $this->postJson('/book', [
            'class_type' => 'discovery',
            'start_date' => now()->addDays(20)->format('Y-m-d'),
            'end_date' => now()->addDays(21)->format('Y-m-d'),
            'participants' => [
                ['name' => 'Valid User', 'age' => 25],
            ],
            'contact_name' => 'Valid Contact',
            'contact_email' => 'valid@example.com',
            'contact_phone' => 'randomletters123',
            'pickup_option' => 'own',
            'confirmation_ack' => true,
            'payment_method' => 'gcash',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contact_phone']);
    }

    public function test_booking_validation_rejects_invalid_email_format(): void
    {
        $response = $this->postJson('/book', [
            'class_type' => 'discovery',
            'start_date' => now()->addDays(20)->format('Y-m-d'),
            'end_date' => now()->addDays(21)->format('Y-m-d'),
            'participants' => [
                ['name' => 'Valid User', 'age' => 25],
            ],
            'contact_name' => 'Valid Contact',
            'contact_email' => 'notanemailaddress',
            'contact_phone' => '09171234567',
            'pickup_option' => 'own',
            'confirmation_ack' => true,
            'payment_method' => 'gcash',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contact_email']);
    }

    public function test_booking_validation_rejects_out_of_range_age(): void
    {
        $response = $this->postJson('/book', [
            'class_type' => 'discovery',
            'start_date' => now()->addDays(20)->format('Y-m-d'),
            'end_date' => now()->addDays(21)->format('Y-m-d'),
            'participants' => [
                ['name' => 'Valid User', 'age' => 999],
            ],
            'contact_name' => 'Valid Contact',
            'contact_email' => 'valid@example.com',
            'contact_phone' => '09171234567',
            'pickup_option' => 'own',
            'confirmation_ack' => true,
            'payment_method' => 'gcash',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['participants.0.age']);
    }
}

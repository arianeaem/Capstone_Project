<?php

namespace Database\Seeders;

use App\Models\AssignmentLog;
use App\Models\Batch;
use App\Models\BatchStatusHistory;
use App\Models\BatchStatusLog;
use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\BookingStatusLog;
use App\Models\CancellationRequest;
use App\Models\CoachAvailability;
use App\Models\CoachOpening;
use App\Models\CoachRequest;
use App\Models\ParticipantAssignment;
use App\Models\Payment;
use App\Models\PaymentStatusLog;
use App\Models\RefundRequest;
use App\Models\RescheduleRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // =========================================================================
        // 1. SEED INTERNAL STAFF USERS
        // =========================================================================
        
        // 1. Camp Owner (Superadmin)
        $owner = User::create([
            'name' => 'Camp Owner',
            'email' => 'owner@campfreedive.ph',
            'phone' => '0927 887 9894',
            'password' => Hash::make('Password123!'),
            'role' => 'owner',
            'status' => 'active',
            'must_change_password' => false,
            'email_verified_at' => now(),
        ]);

        // 2. Camp Admin (Operations Staff)
        $admin = User::create([
            'name' => 'Camp Admin Coordinator',
            'email' => 'admin@campfreedive.ph',
            'phone' => '0917 888 1234',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
            'must_change_password' => false,
            'email_verified_at' => now(),
        ]);

        // 3. Freediving Coach 1 (Active)
        $coachMiko = User::create([
            'name' => 'Coach Miko Reyes',
            'email' => 'coach.miko@campfreedive.ph',
            'phone' => '0919 456 7890',
            'password' => Hash::make('Password123!'),
            'role' => 'coach',
            'status' => 'active',
            'must_change_password' => false,
            'email_verified_at' => now(),
        ]);

        // 4. Freediving Coach 2 (Active, First-time Login)
        $coachElena = User::create([
            'name' => 'Coach Elena Santos',
            'email' => 'coach.elena@campfreedive.ph',
            'phone' => '0920 111 2233',
            'password' => Hash::make('TempPass123!'),
            'role' => 'coach',
            'status' => 'active',
            'must_change_password' => true,
            'email_verified_at' => now(),
        ]);

        // 5. Freediving Coach 3 (Active)
        $coachRyan = User::create([
            'name' => 'Coach Ryan Gomez',
            'email' => 'coach.ryan@campfreedive.ph',
            'phone' => '0917 555 4321',
            'password' => Hash::make('Password123!'),
            'role' => 'coach',
            'status' => 'active',
            'must_change_password' => false,
            'email_verified_at' => now(),
        ]);

        // 6. Freediving Coach 4 (Deactivated / Inactive)
        $coachInactive = User::create([
            'name' => 'Coach Inactive Test',
            'email' => 'coach.inactive@campfreedive.ph',
            'phone' => '0999 000 1111',
            'password' => Hash::make('Password123!'),
            'role' => 'coach',
            'status' => 'inactive',
            'must_change_password' => false,
            'email_verified_at' => now(),
        ]);

        // Seed Initial Audit Logs
        AuditLogger::log('USER_CREATED', 'Owner account initialized: owner@campfreedive.ph', $owner, 'System Seeder');
        AuditLogger::log('USER_CREATED', 'Admin coordinator provisioned: admin@campfreedive.ph', $admin, 'Camp Owner');
        AuditLogger::log('USER_CREATED', 'Coach provisioned: coach.miko@campfreedive.ph', $coachMiko, 'Camp Owner');
        AuditLogger::log('USER_CREATED', 'Coach provisioned: coach.elena@campfreedive.ph', $coachElena, 'Camp Admin Coordinator');
        AuditLogger::log('USER_CREATED', 'Coach provisioned: coach.ryan@campfreedive.ph', $coachRyan, 'Camp Admin Coordinator');
        AuditLogger::log('USER_STATUS_TOGGLED', 'Account deactivated by Owner: coach.inactive@campfreedive.ph', $coachInactive, 'Camp Owner');

        // =========================================================================
        // 2. SEED 2D1N DIVE BATCHES (BATCH MODULE)
        // =========================================================================

        // Batch 1: Confirmed (7 days out), 1 Coach Assigned (Miko)
        $batch1Start = Carbon::now()->addDays(7)->startOfDay();
        $batch1 = Batch::create([
            'name' => 'Aug 30–31 Discovery Batch',
            'batch_code' => 'BATCH-2026-AUG29',
            'start_date' => $batch1Start,
            'end_date' => $batch1Start->copy()->addDay(),
            'status' => 'confirmed',
            'lifecycle_status' => 'confirmed',
            'risk_classification' => 'very_safe',
            'capacity_note' => 'Discovery & Fundive Weekend',
            'notes' => 'Anilao Marine Sanctuary Discovery & Fundive Batch',
            'created_by' => $admin->id,
        ]);

        BatchStatusLog::create([
            'batch_id' => $batch1->id,
            'old_status' => null,
            'new_status' => 'confirmed',
            'changed_by' => $admin->id,
            'note' => 'Batch created and confirmed for August dive dates.',
            'created_at' => now()->subDays(5),
        ]);

        // Batch 2: Confirmed (14 days out), 0 Coaches Assigned -> "Instructor Pending"
        $batch2Start = Carbon::now()->addDays(14)->startOfDay();
        $batch2 = Batch::create([
            'name' => 'Sep 05–06 Beginner Batch',
            'batch_code' => 'BATCH-2026-SEP05',
            'start_date' => $batch2Start,
            'end_date' => $batch2Start->copy()->addDay(),
            'status' => 'confirmed',
            'lifecycle_status' => 'confirmed',
            'risk_classification' => 'safe',
            'capacity_note' => 'Beginner Friendly Weekend',
            'notes' => 'Beginner Friendly Weekend Batch',
            'created_by' => $admin->id,
        ]);

        BatchStatusLog::create([
            'batch_id' => $batch2->id,
            'old_status' => null,
            'new_status' => 'confirmed',
            'changed_by' => $admin->id,
            'note' => 'Batch created and confirmed for September slots.',
            'created_at' => now()->subDays(3),
        ]);

        // Batch 3: Rescheduled Batch (21 days out)
        $batch3Start = Carbon::now()->addDays(21)->startOfDay();
        $batch3 = Batch::create([
            'name' => 'Sep 12–13 Rescheduled Batch',
            'batch_code' => 'BATCH-2026-SEP12',
            'start_date' => $batch3Start,
            'end_date' => $batch3Start->copy()->addDay(),
            'status' => 'rescheduled',
            'lifecycle_status' => 'rescheduled',
            'risk_classification' => 'moderate',
            'capacity_note' => 'Shifted due to resort maintenance',
            'notes' => 'Rescheduled from original weekend',
            'created_by' => $owner->id,
        ]);

        BatchStatusLog::create([
            'batch_id' => $batch3->id,
            'old_status' => 'confirmed',
            'new_status' => 'rescheduled',
            'changed_by' => $owner->id,
            'note' => 'Camp-wide reschedule due to resort maintenance.',
            'created_at' => now()->subDay(),
        ]);

        // Batch 4: Completed Batch (in past)
        $batch4Start = Carbon::now()->subDays(10)->startOfDay();
        $batch4 = Batch::create([
            'name' => 'Aug 14–15 Concluded Batch',
            'batch_code' => 'BATCH-2026-AUG14',
            'start_date' => $batch4Start,
            'end_date' => $batch4Start->copy()->addDay(),
            'status' => 'completed',
            'lifecycle_status' => 'completed',
            'risk_classification' => 'very_safe',
            'completed_at' => $batch4Start->copy()->addDays(2),
            'notes' => 'Concluded dive weekend in Anilao',
            'created_by' => $admin->id,
        ]);

        BatchStatusLog::create([
            'batch_id' => $batch4->id,
            'old_status' => 'confirmed',
            'new_status' => 'completed',
            'changed_by' => $admin->id,
            'note' => '2D1N dive schedule completed successfully.',
            'created_at' => $batch4Start->copy()->addDays(2),
        ]);

        // =========================================================================
        // 3. SEED COACH AVAILABILITIES (COACH PORTAL CALENDAR)
        // =========================================================================

        // Coach Miko: Assigned on Batch 1 date, Available on Batch 2 date
        CoachAvailability::create([
            'coach_id' => $coachMiko->id,
            'date' => $batch1Start->format('Y-m-d'),
            'status' => 'assigned',
            'notes' => 'Assigned to BATCH-2026-AUG29',
        ]);

        CoachAvailability::create([
            'coach_id' => $coachMiko->id,
            'date' => $batch2Start->format('Y-m-d'),
            'status' => 'available',
            'notes' => 'Marked available in Coach Portal',
        ]);

        // Coach Elena: Available on Batch 1 & 2 dates
        CoachAvailability::create([
            'coach_id' => $coachElena->id,
            'date' => $batch1Start->format('Y-m-d'),
            'status' => 'available',
            'notes' => 'Available for weekend sessions',
        ]);

        CoachAvailability::create([
            'coach_id' => $coachElena->id,
            'date' => $batch2Start->format('Y-m-d'),
            'status' => 'available',
            'notes' => 'Available for beginner batch',
        ]);

        // Coach Ryan: Unavailable on Batch 1 date
        CoachAvailability::create([
            'coach_id' => $coachRyan->id,
            'date' => $batch1Start->format('Y-m-d'),
            'status' => 'unavailable',
            'notes' => 'Out of town / Personal leave',
        ]);

        // =========================================================================
        // 4. SEED CUSTOMER BOOKINGS, PARTICIPANTS & COACH ASSIGNMENTS
        // =========================================================================

        // Booking 1: Discovery Class (2 pax) -> Attached to Batch 1
        $start1 = $batch1Start;
        $b1 = Booking::create([
            'batch_id' => $batch1->id,
            'booking_number' => 'CFP-2026-1001',
            'pin' => '1111',
            'class_type' => 'discovery',
            'is_certified_diver' => false,
            'start_date' => $start1,
            'end_date' => $start1->copy()->addDay(),
            'pickup_option' => 'carpool',
            'pickup_location' => 'Shell Tiendesitas (Pasig) - 3:00 AM',
            'carpool_fee' => 2000.00,
            'boat_dive' => true,
            'boat_dive_fee' => 1600.00,
            'lgu_fee' => 600.00,
            'environmental_fee' => 100.00,
            'subtotal' => 8500.00,
            'total_amount' => 12800.00,
            'downpayment_amount' => 6000.00,
            'balance_amount' => 6800.00,
            'contact_name' => 'Ariane Mae Ramos',
            'contact_email' => 'ariane@example.com',
            'contact_phone' => '0917 123 4567',
            'contact_facebook' => 'https://www.facebook.com/arianemae',
            'status' => 'confirmed',
        ]);

        $p1 = BookingParticipant::create([
            'booking_id' => $b1->id,
            'name' => 'Ariane Mae Ramos',
            'age' => 25,
            'health_condition' => 'None declared',
            'swimmer_status' => 'non_swimmer',
            'price_per_person' => 4250.00,
        ]);

        $p2 = BookingParticipant::create([
            'booking_id' => $b1->id,
            'name' => 'Bryan Santos',
            'age' => 26,
            'health_condition' => 'Mild ear pressure sensitivity',
            'swimmer_status' => 'swimmer',
            'price_per_person' => 4250.00,
        ]);

        // Assign Booking 1 students to Coach Miko
        ParticipantAssignment::create([
            'participant_id' => $p1->id,
            'booking_id' => $b1->id,
            'coach_id' => $coachMiko->id,
            'batch_id' => $batch1->id,
            'dive_date' => $start1,
            'assigned_by' => $admin->id,
            'assigned_at' => now()->subDay(),
            'status' => 'assigned',
            'is_ratio_override' => false,
        ]);

        ParticipantAssignment::create([
            'participant_id' => $p2->id,
            'booking_id' => $b1->id,
            'coach_id' => $coachMiko->id,
            'batch_id' => $batch1->id,
            'dive_date' => $start1,
            'assigned_by' => $admin->id,
            'assigned_at' => now()->subDay(),
            'status' => 'assigned',
            'is_ratio_override' => false,
        ]);

        Payment::create([
            'booking_id' => $b1->id,
            'payment_method' => 'gcash',
            'transaction_id' => 'PAYM-TXN-1001A',
            'paymongo_payment_id' => 'pay_test_w3J3Z8pL5xK9aB1m',
            'paymongo_resource_id' => 'src_test_9kL2pQ8x',
            'amount' => 6000.00,
            'fee_amount' => 150.00,
            'net_amount' => 5850.00,
            'payment_type' => 'downpayment',
            'status' => 'completed',
            'paid_at' => now()->subDay(),
        ]);

        BookingStatusLog::create([
            'booking_id' => $b1->id,
            'old_status' => 'new',
            'new_status' => 'confirmed',
            'changed_by' => null,
            'note' => 'Online customer booking completed with GCash downpayment of ₱6,000.',
            'created_at' => now()->subDay(),
        ]);

        // Booking 2: Fundive Class (1 pax) -> Attached to Batch 2 (UNASSIGNED -> In Matching Queue)
        $start2 = $batch2Start;
        $b2 = Booking::create([
            'batch_id' => $batch2->id,
            'booking_number' => 'CFP-2026-1002',
            'pin' => '2222',
            'class_type' => 'fundive',
            'is_certified_diver' => true,
            'start_date' => $start2,
            'end_date' => $start2->copy()->addDay(),
            'pickup_option' => 'own',
            'pickup_location' => null,
            'carpool_fee' => 0.00,
            'boat_dive' => false,
            'boat_dive_fee' => 0.00,
            'lgu_fee' => 300.00,
            'environmental_fee' => 50.00,
            'subtotal' => 2500.00,
            'total_amount' => 2850.00,
            'downpayment_amount' => 2000.00,
            'balance_amount' => 850.00,
            'contact_name' => 'Carlos Mendoza',
            'contact_email' => 'carlos@example.com',
            'contact_phone' => '0918 555 9876',
            'contact_facebook' => 'https://www.facebook.com/carlos.mendoza',
            'status' => 'confirmed',
        ]);

        BookingParticipant::create([
            'booking_id' => $b2->id,
            'name' => 'Carlos Mendoza',
            'age' => 29,
            'health_condition' => 'Certified diver',
            'swimmer_status' => 'confident_swimmer',
            'price_per_person' => 2500.00,
        ]);

        Payment::create([
            'booking_id' => $b2->id,
            'payment_method' => 'bpi_bank_transfer',
            'transaction_id' => 'PAYM-TXN-1002B',
            'paymongo_payment_id' => 'pay_test_7hK4mQ2xL8wP1z',
            'amount' => 2000.00,
            'fee_amount' => 50.00,
            'net_amount' => 1950.00,
            'payment_type' => 'downpayment',
            'status' => 'completed',
            'paid_at' => now()->subDays(2),
        ]);

        BookingStatusLog::create([
            'booking_id' => $b2->id,
            'old_status' => 'new',
            'new_status' => 'confirmed',
            'changed_by' => null,
            'note' => 'Booking confirmed. Unassigned in Batch 2.',
            'created_at' => now()->subHours(5),
        ]);

        // Booking 4: Reschedule Request
        $start4 = Carbon::now()->addDays(10)->startOfDay();
        $b4 = Booking::create([
            'batch_id' => null,
            'booking_number' => 'CFP-2026-1004',
            'pin' => '4444',
            'class_type' => 'discovery',
            'is_certified_diver' => false,
            'start_date' => $start4,
            'end_date' => $start4->copy()->addDay(),
            'pickup_option' => 'own',
            'pickup_location' => null,
            'carpool_fee' => 0.00,
            'boat_dive' => false,
            'boat_dive_fee' => 0.00,
            'lgu_fee' => 300.00,
            'environmental_fee' => 50.00,
            'subtotal' => 4250.00,
            'total_amount' => 4600.00,
            'downpayment_amount' => 3000.00,
            'balance_amount' => 1600.00,
            'contact_name' => 'Danilo Ramos',
            'contact_email' => 'danilo@example.com',
            'contact_phone' => '0917 111 2233',
            'status' => 'reschedule_requested',
        ]);

        BookingParticipant::create([
            'booking_id' => $b4->id,
            'name' => 'Danilo Ramos',
            'age' => 30,
            'health_condition' => 'None',
            'swimmer_status' => 'swimmer',
            'price_per_person' => 4250.00,
        ]);

        Payment::create([
            'booking_id' => $b4->id,
            'payment_method' => 'gcash',
            'transaction_id' => 'PAYM-TXN-1004D',
            'paymongo_payment_id' => 'pay_test_9kL2pQ8x',
            'amount' => 3000.00,
            'fee_amount' => 75.00,
            'net_amount' => 2925.00,
            'payment_type' => 'downpayment',
            'status' => 'completed',
            'paid_at' => now()->subDays(3),
        ]);

        RescheduleRequest::create([
            'booking_id' => $b4->id,
            'current_start_date' => $start4,
            'current_end_date' => $start4->copy()->addDay(),
            'requested_start_date' => $start4->copy()->addDays(7),
            'requested_end_date' => $start4->copy()->addDays(8),
            'reason' => 'Work schedule conflict on the original weekend.',
            'status' => 'pending',
        ]);

        BookingStatusLog::create([
            'booking_id' => $b4->id,
            'old_status' => 'confirmed',
            'new_status' => 'reschedule_requested',
            'changed_by' => null,
            'note' => 'Customer submitted a reschedule request to ' . $start4->copy()->addDays(7)->format('M d, Y') . '.',
            'created_at' => now()->subHours(5),
        ]);

        // Booking 3: Refinement (1 pax) -> Cancellation & Refund Request
        $start3 = Carbon::now()->addDays(3)->startOfDay();
        $b3 = Booking::create([
            'batch_id' => null,
            'booking_number' => 'CFP-2026-1003',
            'pin' => '3333',
            'class_type' => 'refinement',
            'is_certified_diver' => false,
            'start_date' => $start3,
            'end_date' => $start3->copy()->addDay(),
            'pickup_option' => 'carpool',
            'pickup_location' => 'Market! Market! (BGC, Taguig) - 3:40 AM',
            'carpool_fee' => 1000.00,
            'boat_dive' => true,
            'boat_dive_fee' => 800.00,
            'lgu_fee' => 300.00,
            'environmental_fee' => 50.00,
            'subtotal' => 4100.00,
            'total_amount' => 6250.00,
            'downpayment_amount' => 3000.00,
            'balance_amount' => 3250.00,
            'contact_name' => 'Elena Cruz',
            'contact_email' => 'elena@example.com',
            'contact_phone' => '0922 888 1234',
            'status' => 'cancellation_requested',
        ]);

        BookingParticipant::create([
            'booking_id' => $b3->id,
            'name' => 'Elena Cruz',
            'age' => 27,
            'health_condition' => 'Valsalva equalizing practice',
            'swimmer_status' => 'swimmer',
            'price_per_person' => 4100.00,
        ]);

        $p3 = Payment::create([
            'booking_id' => $b3->id,
            'payment_method' => 'gcash',
            'transaction_id' => 'PAYM-TXN-1003C',
            'paymongo_payment_id' => 'pay_test_5mN8vC1xQ4kL7w',
            'amount' => 3000.00,
            'fee_amount' => 75.00,
            'net_amount' => 2925.00,
            'payment_type' => 'downpayment',
            'status' => 'refund_requested',
            'paid_at' => now()->subDays(5),
        ]);

        CancellationRequest::create([
            'booking_id' => $b3->id,
            'calculated_refund_amount' => 0.00,
            'reason' => 'Emergency family commitment.',
            'force_majeure_flag' => false,
            'status' => 'pending',
        ]);

        RefundRequest::create([
            'payment_id' => $p3->id,
            'booking_id' => $b3->id,
            'requested_by' => 'customer',
            'requested_at' => now()->subHours(2),
            'eligibility_calculated' => [
                'days_until_dive' => 3,
                'eligible_for_refund' => false,
                'refund_percentage' => 0,
                'window_label' => 'Within 1 Week (< 7 Days)',
                'policy_action_text' => 'Non-refundable lockdown window. Forfeited per camp cancellation policy.',
            ],
            'status' => 'pending',
            'notes' => 'Customer requested cancellation from self-service portal.',
        ]);

        // =========================================================================
        // 5. SEED COACH OPENING BROADCAST & COACH REQUEST APPLICATION
        // =========================================================================

        $opening = CoachOpening::create([
            'batch_id' => $batch3->id,
            'dive_date' => $batch3Start,
            'needed_students_count' => 4,
            'status' => 'open',
            'posted_by' => $admin->id,
            'notes' => 'Open slot for Discovery & Beginner Dive Batch in Mabini.',
        ]);

        CoachRequest::create([
            'opening_id' => $opening->id,
            'batch_id' => $batch3->id,
            'coach_id' => $coachElena->id,
            'status' => 'pending',
            'notes' => 'I am available for this weekend schedule.',
            'created_at' => now()->subHours(3),
        ]);

        CoachRequest::create([
            'opening_id' => $opening->id,
            'batch_id' => $batch3->id,
            'coach_id' => $coachRyan->id,
            'status' => 'pending',
            'notes' => 'Can accommodate the discovery session.',
            'created_at' => now()->subHours(2),
        ]);

        // =========================================================================
        // 6. SEED LIVE WEATHER RISK ASSESSMENTS FOR BATCHES
        // =========================================================================
        $forecastService = app(\App\Services\WeatherForecastService::class);
        
        // Assess Batch 1 (7 days out)
        $forecastService->assessBatch($batch1, null, $admin);

        // Assess Batch 2 (14 days out)
        $forecastService->assessBatch($batch2, null, $admin);
    }
}

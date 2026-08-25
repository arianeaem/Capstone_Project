<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\BookingParticipant;
use App\Models\CoachOpening;
use App\Models\CoachRequest;
use App\Models\ParticipantAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCoachMatchingModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_admin_can_access_coach_roster(): void
    {
        $admin = User::where('email', 'admin@campfreedive.ph')->first();
        $this->actingAs($admin);

        $response = $this->get('/admin/coaches');
        $response->assertStatus(200);
        $response->assertSee('Coach Roster &amp; Schedules', false);
        $response->assertSee('Coach Miko Reyes');
        $response->assertSee('Coach Elena Santos');
    }

    public function test_coach_role_is_forbidden_from_admin_coaches_module(): void
    {
        $coach = User::where('email', 'coach.miko@campfreedive.ph')->first();
        $this->actingAs($coach);

        $response = $this->get('/admin/coaches');
        $response->assertStatus(403);

        $responseMatching = $this->get('/admin/coaches/matching');
        $responseMatching->assertStatus(403);
    }

    public function test_admin_can_view_coach_detail(): void
    {
        $admin = User::where('email', 'admin@campfreedive.ph')->first();
        $this->actingAs($admin);

        $coachMiko = User::where('email', 'coach.miko@campfreedive.ph')->first();

        $response = $this->get("/admin/coaches/{$coachMiko->id}");
        $response->assertStatus(200);
        $response->assertSee('Coach Miko Reyes');
        $response->assertSee('Ariane Mae Ramos');
        $response->assertSee('Bryan Santos');
    }

    public function test_admin_can_view_students_needing_coach_matching_queue(): void
    {
        $admin = User::where('email', 'admin@campfreedive.ph')->first();
        $this->actingAs($admin);

        $response = $this->get('/admin/coaches/matching');
        $response->assertStatus(200);
        $response->assertSee('Students Needing a Coach');
        $response->assertSee('Carlos Mendoza');
    }

    public function test_admin_can_assign_students_to_available_coach(): void
    {
        $admin = User::where('email', 'admin@campfreedive.ph')->first();
        $this->actingAs($admin);

        $unassignedParticipant = BookingParticipant::where('name', 'Carlos Mendoza')->first();
        $coachElena = User::where('email', 'coach.elena@campfreedive.ph')->first();
        $batch2 = Batch::where('batch_code', 'BATCH-2026-SEP05')->first();

        $response = $this->post('/admin/coaches/matching/assign', [
            'participant_ids' => [$unassignedParticipant->id],
            'coach_id' => $coachElena->id,
            'batch_id' => $batch2->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('participant_assignments', [
            'participant_id' => $unassignedParticipant->id,
            'coach_id' => $coachElena->id,
            'batch_id' => $batch2->id,
            'status' => 'assigned',
        ]);
    }

    public function test_assigning_over_four_students_records_ratio_override_exception(): void
    {
        $admin = User::where('email', 'admin@campfreedive.ph')->first();
        $this->actingAs($admin);

        $coachMiko = User::where('email', 'coach.miko@campfreedive.ph')->first();
        $batch1 = Batch::where('batch_code', 'BATCH-2026-AUG29')->first();

        // Coach Miko already has 2 students (Ariane and Bryan) on batch1
        // Create 3 additional mock participants
        $booking = $batch1->bookings->first();
        $p1 = BookingParticipant::create(['booking_id' => $booking->id, 'name' => 'Extra Student 1', 'age' => 20, 'price_per_person' => 2500.00]);
        $p2 = BookingParticipant::create(['booking_id' => $booking->id, 'name' => 'Extra Student 2', 'age' => 21, 'price_per_person' => 2500.00]);
        $p3 = BookingParticipant::create(['booking_id' => $booking->id, 'name' => 'Extra Student 3', 'age' => 22, 'price_per_person' => 2500.00]);

        $response = $this->post('/admin/coaches/matching/assign', [
            'participant_ids' => [$p1->id, $p2->id, $p3->id],
            'coach_id' => $coachMiko->id,
            'batch_id' => $batch1->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('participant_assignments', [
            'participant_id' => $p3->id,
            'coach_id' => $coachMiko->id,
            'is_ratio_override' => true,
        ]);
    }

    public function test_admin_can_reassign_student_with_audit_log(): void
    {
        $admin = User::where('email', 'admin@campfreedive.ph')->first();
        $this->actingAs($admin);

        $coachMiko = User::where('email', 'coach.miko@campfreedive.ph')->first();
        $coachElena = User::where('email', 'coach.elena@campfreedive.ph')->first();
        $participant = BookingParticipant::where('name', 'Ariane Mae Ramos')->first();

        $response = $this->post("/admin/coaches/{$coachMiko->id}/reassign-student", [
            'participant_id' => $participant->id,
            'new_coach_id' => $coachElena->id,
            'reason' => 'Coach Miko requested schedule load reduction.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('participant_assignments', [
            'participant_id' => $participant->id,
            'coach_id' => $coachElena->id,
            'status' => 'assigned',
        ]);

        $this->assertDatabaseHas('assignment_logs', [
            'participant_id' => $participant->id,
            'old_coach_id' => $coachMiko->id,
            'new_coach_id' => $coachElena->id,
            'reason' => 'Coach Miko requested schedule load reduction.',
        ]);
    }

    public function test_admin_can_broadcast_open_slot_to_coach_portal(): void
    {
        $admin = User::where('email', 'admin@campfreedive.ph')->first();
        $this->actingAs($admin);

        $batch = Batch::where('batch_code', 'BATCH-2026-SEP05')->first();

        $response = $this->post('/admin/coaches/matching/broadcast', [
            'batch_id' => $batch->id,
            'notes' => 'Open spot for Fundive session.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('coach_openings', [
            'batch_id' => $batch->id,
            'status' => 'open',
            'notes' => 'Open spot for Fundive session.',
        ]);
    }

    public function test_admin_can_review_and_approve_coach_request(): void
    {
        $admin = User::where('email', 'admin@campfreedive.ph')->first();
        $this->actingAs($admin);

        $coachElena = User::where('email', 'coach.elena@campfreedive.ph')->first();
        $coachRyan = User::where('email', 'coach.ryan@campfreedive.ph')->first();
        $requestElena = CoachRequest::where('coach_id', $coachElena->id)->first();
        $requestRyan = CoachRequest::where('coach_id', $coachRyan->id)->first();

        $response = $this->post("/admin/coaches/requests/{$requestElena->id}/approve");
        $response->assertRedirect();

        $this->assertEquals('approved', $requestElena->fresh()->status);
        $this->assertEquals('not_selected', $requestRyan->fresh()->status);
    }

    public function test_admin_can_perform_balanced_batch_assignment_across_multiple_coaches(): void
    {
        $admin = User::where('email', 'admin@campfreedive.ph')->first();
        $this->actingAs($admin);

        $batch = Batch::where('batch_code', 'BATCH-2026-SEP05')->first();
        $coachMiko = User::where('email', 'coach.miko@campfreedive.ph')->first();
        $coachElena = User::where('email', 'coach.elena@campfreedive.ph')->first();

        // Create 4 mock participants on this batch
        $booking = $batch->bookings->first();
        $p1 = BookingParticipant::create(['booking_id' => $booking->id, 'name' => 'Balance Test 1', 'age' => 20, 'price_per_person' => 2500]);
        $p2 = BookingParticipant::create(['booking_id' => $booking->id, 'name' => 'Balance Test 2', 'age' => 21, 'price_per_person' => 2500]);
        $p3 = BookingParticipant::create(['booking_id' => $booking->id, 'name' => 'Balance Test 3', 'age' => 22, 'price_per_person' => 2500]);
        $p4 = BookingParticipant::create(['booking_id' => $booking->id, 'name' => 'Balance Test 4', 'age' => 23, 'price_per_person' => 2500]);

        $response = $this->post('/admin/coaches/matching/batch-assign', [
            'batch_id' => $batch->id,
            'assignments' => [
                $coachMiko->id => [$p1->id, $p2->id],
                $coachElena->id => [$p3->id, $p4->id],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('participant_assignments', [
            'participant_id' => $p1->id,
            'coach_id' => $coachMiko->id,
            'batch_id' => $batch->id,
            'status' => 'assigned',
        ]);
        $this->assertDatabaseHas('participant_assignments', [
            'participant_id' => $p3->id,
            'coach_id' => $coachElena->id,
            'batch_id' => $batch->id,
            'status' => 'assigned',
        ]);
    }

    public function test_batch_assignment_logs_intentional_exception_when_imbalanced_split_is_saved(): void
    {
        $admin = User::where('email', 'admin@campfreedive.ph')->first();
        $this->actingAs($admin);

        $batch = Batch::where('batch_code', 'BATCH-2026-SEP05')->first();
        $coachMiko = User::where('email', 'coach.miko@campfreedive.ph')->first();
        $coachElena = User::where('email', 'coach.elena@campfreedive.ph')->first();

        // 3 on Miko, 0 on Elena (imbalanced by 3)
        $booking = $batch->bookings->first();
        $p1 = BookingParticipant::create(['booking_id' => $booking->id, 'name' => 'Imbalance 1', 'age' => 20, 'price_per_person' => 2500]);
        $p2 = BookingParticipant::create(['booking_id' => $booking->id, 'name' => 'Imbalance 2', 'age' => 21, 'price_per_person' => 2500]);
        $p3 = BookingParticipant::create(['booking_id' => $booking->id, 'name' => 'Imbalance 3', 'age' => 22, 'price_per_person' => 2500]);

        $response = $this->post('/admin/coaches/matching/batch-assign', [
            'batch_id' => $batch->id,
            'assignments' => [
                $coachMiko->id => [$p1->id, $p2->id, $p3->id],
                $coachElena->id => [],
            ],
            'exception_note' => 'Coach Elena requested zero load for this weekend due to exam.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'BATCH_COACH_ASSIGNMENT_EXCEPTION',
        ]);
    }
}

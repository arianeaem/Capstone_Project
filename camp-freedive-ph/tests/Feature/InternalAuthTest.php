<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Staff Portal Login');
        $response->assertSee('Staff Email Address');
    }

    public function test_owner_login_redirects_to_admin_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'owner@campfreedive.ph',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
        $this->assertEquals('owner', auth()->user()->role);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'LOGIN_SUCCESS',
            'user_id' => auth()->id(),
        ]);
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@campfreedive.ph',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->role);
    }

    public function test_coach_login_redirects_to_coach_portal(): void
    {
        $response = $this->post('/login', [
            'email' => 'coach.miko@campfreedive.ph',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/coach');
        $this->assertAuthenticated();
        $this->assertEquals('coach', auth()->user()->role);
    }

    public function test_deactivated_account_is_blocked_with_custom_message(): void
    {
        $response = $this->post('/login', [
            'email' => 'coach.inactive@campfreedive.ph',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHas('error', 'This account has been deactivated. Please contact the camp owner.');
        $this->assertGuest();

        // Verify audit log for deactivated block
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'LOGIN_DEACTIVATED_BLOCKED',
        ]);
    }

    public function test_user_with_temporary_password_is_forced_to_change_password(): void
    {
        // Coach Elena has must_change_password = true
        $response = $this->post('/login', [
            'email' => 'coach.elena@campfreedive.ph',
            'password' => 'TempPass123!',
        ]);

        $response->assertRedirect(route('password.force_change'));
        $this->assertAuthenticated();

        // Attempting to access coach dashboard while must_change_password is true redirects back to force change
        $dashResponse = $this->get('/coach');
        $dashResponse->assertRedirect(route('password.force_change'));

        // Update password
        $updateResponse = $this->post('/force-password-change', [
            'current_password' => 'TempPass123!',
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!',
        ]);

        $updateResponse->assertRedirect(route('coach.dashboard'));
        $this->assertFalse(auth()->user()->fresh()->must_change_password);
        $this->assertTrue(Hash::check('NewSecurePass123!', auth()->user()->fresh()->password));
    }

    public function test_role_authorization_middleware_restricts_access(): void
    {
        $coach = User::where('email', 'coach.miko@campfreedive.ph')->first();
        $admin = User::where('email', 'admin@campfreedive.ph')->first();
        $owner = User::where('email', 'owner@campfreedive.ph')->first();

        // Coach cannot access admin dashboard
        $this->actingAs($coach);
        $this->get('/admin')->assertStatus(403);

        // Admin can access admin dashboard, but NOT audit logs (Owner exclusive)
        $this->actingAs($admin);
        $this->get('/admin')->assertStatus(200);
        $this->get('/admin/settings/audit-logs')->assertStatus(403);

        // Owner can access both
        $this->actingAs($owner);
        $this->get('/admin')->assertStatus(200);
        $this->get('/admin/settings/audit-logs')->assertStatus(200);
        $this->get('/admin/settings/users')->assertStatus(200);
    }

    public function test_owner_can_provision_new_coach_and_toggle_status(): void
    {
        $owner = User::where('email', 'owner@campfreedive.ph')->first();
        $this->actingAs($owner);

        // Provision a new Coach
        $createResponse = $this->post('/admin/settings/users', [
            'name' => 'Coach New Recruit',
            'email' => 'coach.new@campfreedive.ph',
            'phone' => '0912 345 6789',
            'role' => 'coach',
            'temp_password' => 'TempPass999!',
        ]);

        $createResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'coach.new@campfreedive.ph',
            'role' => 'coach',
            'status' => 'active',
            'must_change_password' => true,
        ]);

        $newCoach = User::where('email', 'coach.new@campfreedive.ph')->first();

        // Toggle status to inactive
        $toggleResponse = $this->patch("/admin/settings/users/{$newCoach->id}/toggle-status");
        $toggleResponse->assertRedirect();
        $this->assertEquals('inactive', $newCoach->fresh()->status);

        // Edit profile
        $editResponse = $this->put("/admin/settings/users/{$newCoach->id}", [
            'name' => 'Coach Updated Name',
            'email' => 'coach.new@campfreedive.ph',
            'phone' => '0999 888 7777',
            'role' => 'coach',
            'status' => 'active',
        ]);
        $editResponse->assertRedirect(route('admin.users.index'));
        $this->assertEquals('Coach Updated Name', $newCoach->fresh()->name);
        $this->assertEquals('active', $newCoach->fresh()->status);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected User $coach;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->coach = User::factory()->create([
            'role' => 'coach',
            'status' => 'active',
        ]);
    }

    public function test_owner_can_provision_new_coach_account_with_credentials_in_session()
    {
        $response = $this->actingAs($this->owner)->post(route('admin.users.store'), [
            'name' => 'Maria Santos',
            'email' => 'maria.santos@test.ph',
            'phone' => '09171112233',
            'role' => 'coach',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('new_user_credentials');

        $creds = session('new_user_credentials');
        $this->assertEquals('Maria Santos', $creds['name']);
        $this->assertEquals('maria.santos@test.ph', $creds['email']);
        $this->assertEquals('coach', $creds['role']);
        $this->assertNotEmpty($creds['temp_password']);

        $newUser = User::where('email', 'maria.santos@test.ph')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->must_change_password);
    }

    public function test_admin_can_provision_new_coach_account()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan.delacruz@test.ph',
            'role' => 'coach',
            'temp_password' => 'CustomTemp123!',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $creds = session('new_user_credentials');
        $this->assertEquals('CustomTemp123!', $creds['temp_password']);
    }

    public function test_admin_cannot_provision_admin_or_owner_account()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Fake Admin',
            'email' => 'fake.admin@test.ph',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors(['role']);
    }

    public function test_admin_can_delete_coach_account()
    {
        $coachToDelete = User::factory()->create([
            'role' => 'coach',
            'status' => 'active',
        ]);
        Coach::create([
            'user_id' => $coachToDelete->id,
            'full_name' => $coachToDelete->name,
            'email' => $coachToDelete->email,
            'phone' => '09170001122',
            'certification_level' => 'AIDA 4 Master',
            'certification_number' => 'AIDA-12345',
            'certification_expiry' => now()->addYear(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $coachToDelete));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $coachToDelete->id]);
        $this->assertDatabaseMissing('coaches', ['user_id' => $coachToDelete->id]);
    }

    public function test_admin_cannot_delete_owner_or_admin()
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->owner));
        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_themselves()
    {
        $response = $this->actingAs($this->owner)->delete(route('admin.users.destroy', $this->owner));
        $response->assertSessionHas('error', 'You cannot delete your own account.');
    }

    public function test_updating_password_forces_must_change_password_and_flashes_credentials()
    {
        $response = $this->actingAs($this->owner)->put(route('admin.users.update', $this->coach), [
            'name' => $this->coach->name,
            'email' => $this->coach->email,
            'role' => 'coach',
            'status' => 'active',
            'new_password' => 'NewTempResetPass123!',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('new_user_credentials');

        $creds = session('new_user_credentials');
        $this->assertEquals('NewTempResetPass123!', $creds['temp_password']);
        $this->assertTrue($creds['is_reset']);

        $this->coach->refresh();
        $this->assertTrue($this->coach->must_change_password);
    }
}

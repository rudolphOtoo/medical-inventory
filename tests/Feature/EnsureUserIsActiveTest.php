<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureUserIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_access_authenticated_routes(): void
    {
        $user = User::factory()->departmentStaff()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_inactive_user_is_redirected_to_login_and_logged_out(): void
    {
        $user = User::factory()->departmentStaff()->create(['is_active' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_authenticate_via_login_screen(): void
    {
        $user = User::factory()->departmentStaff()->create(['is_active' => false]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_settings_routes_are_protected_for_inactive_users(): void
    {
        $user = User::factory()->departmentStaff()->create(['is_active' => false]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }
}

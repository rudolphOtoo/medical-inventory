<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StarterShellRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_for_all_core_routes(): void
    {
        $routes = [
            'dashboard',
            'equipment.index',
            'departments.index',
            'issues.index',
            'activity.index',
            'health',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertRedirect(route('login'));
        }
    }

    public function test_admin_can_access_all_starter_routes(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $routes = [
            'dashboard',
            'equipment.index',
            'departments.index',
            'issues.index',
            'activity.index',
            'health',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertOk();
        }
    }

    public function test_department_staff_can_access_staff_routes_and_is_forbidden_from_admin_routes(): void
    {
        $staff = User::factory()->departmentStaff()->create();
        $this->actingAs($staff);

        // Staff routes
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('equipment.index'))->assertOk();
        $this->get(route('issues.index'))->assertOk();

        // Admin-only routes
        $this->get(route('departments.index'))->assertForbidden();
        $this->get(route('activity.index'))->assertForbidden();
        $this->get(route('health'))->assertForbidden();
    }

    public function test_health_check_endpoint_returns_json_when_requested(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('health'));

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'timestamp',
                'environment',
                'checks' => [
                    'database' => ['status', 'latency_ms', 'driver'],
                    'storage' => ['status'],
                    'cache' => ['status', 'driver'],
                ],
                'server' => [
                    'php_version',
                    'laravel_version',
                ],
            ]);
    }
}

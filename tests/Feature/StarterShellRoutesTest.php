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

    public function test_authenticated_users_can_access_all_starter_routes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

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

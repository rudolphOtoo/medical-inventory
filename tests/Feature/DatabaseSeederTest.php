<?php

namespace Tests\Feature;

use App\Models\Department;
use Database\Seeders\BaselineSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_baseline_departments_are_seeded_in_any_environment(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        app(DatabaseSeeder::class)->run();

        $this->assertDatabaseHas('departments', ['code' => 'ED']);
        $this->assertDatabaseHas('departments', ['code' => 'ICU']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_demo_data_is_seeded_in_local_environment(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@medtrack.test']);
    }

    public function test_demo_data_is_seeded_when_flag_is_enabled(): void
    {
        config(['medtrack.seed_demo_users' => true]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@medtrack.test']);
    }

    public function test_demo_data_is_skipped_by_default_in_non_local_environments(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseHas('departments', ['code' => 'ED']);
    }

    public function test_demo_seeder_is_blocked_in_production_without_flag(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(RuntimeException::class);

        app(DemoSeeder::class)->run();
    }

    public function test_demo_seeder_can_be_run_directly_outside_production(): void
    {
        $this->seed(DemoSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@medtrack.test']);
    }

    public function test_baseline_seeder_is_idempotent(): void
    {
        app(BaselineSeeder::class)->run();
        app(BaselineSeeder::class)->run();

        $this->assertSame(6, $this->freshDepartmentsCount());
    }

    protected function freshDepartmentsCount(): int
    {
        return Department::count();
    }
}

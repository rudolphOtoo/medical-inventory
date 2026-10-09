<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProvisionAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_administrator_when_missing(): void
    {
        config([
            'medtrack.admin.name' => 'Root Admin',
            'medtrack.admin.email' => 'root@medtrack.test',
            'medtrack.admin.password' => 'supersecretpass123',
        ]);

        $this->artisan('medtrack:provision-admin')->assertExitCode(0);

        $admin = User::where('email', 'root@medtrack.test')->firstOrFail();

        $this->assertSame('Root Admin', $admin->name);
        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertNull($admin->department_id);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('supersecretpass123', $admin->password));
    }

    public function test_it_does_not_overwrite_an_existing_administrator(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@medtrack.test',
            'password' => 'originalpassword123',
        ]);

        config([
            'medtrack.admin.email' => 'admin@medtrack.test',
            'medtrack.admin.password' => 'rotatedsecretpass12',
        ]);

        $this->artisan('medtrack:provision-admin')->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);

        $admin = User::where('email', 'admin@medtrack.test')->firstOrFail();
        $this->assertTrue(Hash::check('originalpassword123', $admin->password));
    }

    public function test_force_resets_an_existing_administrator_password(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@medtrack.test',
            'password' => 'originalpassword123',
        ]);

        config([
            'medtrack.admin.email' => 'admin@medtrack.test',
            'medtrack.admin.password' => 'rotatedsecretpass12',
        ]);

        $this->artisan('medtrack:provision-admin', ['--force' => true])->assertExitCode(0);

        $admin = User::where('email', 'admin@medtrack.test')->firstOrFail();
        $this->assertTrue(Hash::check('rotatedsecretpass12', $admin->password));
    }

    public function test_it_fails_when_credentials_are_missing(): void
    {
        config([
            'medtrack.admin.email' => null,
            'medtrack.admin.password' => null,
        ]);

        $this->artisan('medtrack:provision-admin')->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_it_rejects_a_password_shorter_than_the_minimum(): void
    {
        config([
            'medtrack.admin.email' => 'admin@medtrack.test',
            'medtrack.admin.password' => 'short',
        ]);

        $this->artisan('medtrack:provision-admin')->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_it_defaults_the_name_when_blank(): void
    {
        config([
            'medtrack.admin.name' => '',
            'medtrack.admin.email' => 'admin@medtrack.test',
            'medtrack.admin.password' => 'supersecretpass123',
        ]);

        $this->artisan('medtrack:provision-admin')->assertExitCode(0);

        $admin = User::where('email', 'admin@medtrack.test')->firstOrFail();
        $this->assertSame('Administrator', $admin->name);
    }
}

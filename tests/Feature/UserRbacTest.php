<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class UserRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_admin_role_and_helpers(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertEquals(UserRole::Admin, $admin->role);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isDepartmentUser());
        $this->assertTrue(Gate::forUser($admin)->allows('admin'));
    }

    public function test_user_can_have_department_staff_role_and_helpers(): void
    {
        $staff = User::factory()->departmentStaff(1)->create();

        $this->assertEquals(UserRole::DepartmentUser, $staff->role);
        $this->assertEquals(1, $staff->department_id);
        $this->assertFalse($staff->isAdmin());
        $this->assertTrue($staff->isDepartmentUser());
        $this->assertFalse(Gate::forUser($staff)->allows('admin'));
    }

    public function test_welcome_page_renders_successfully(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('MedTrack')
            ->assertSee('Hospital Equipment');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_create_department(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);
        $response = $this->post(route('departments.store'), [
            'name' => 'Cardiology Wing',
            'code' => 'CARD',
            'floor' => '3rd Floor - East Wing',
            'contact_number' => 'Ext. 3301',
            'head_of_department' => 'Dr. Harrison Wells',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', [
            'name' => 'Cardiology Wing',
            'code' => 'CARD',
        ]);

        $indexResponse = $this->get(route('departments.index'));
        $indexResponse->assertOk()->assertSee('Cardiology Wing');
    }

    public function test_non_admin_cannot_create_department(): void
    {
        $staff = User::factory()->departmentStaff()->create();

        $this->actingAs($staff);
        $response = $this->post(route('departments.store'), [
            'name' => 'Unauthorized Dept',
            'code' => 'UNAUTH',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('departments', ['code' => 'UNAUTH']);
    }
}

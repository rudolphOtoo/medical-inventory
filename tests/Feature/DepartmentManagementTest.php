<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Equipment;
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

    public function test_admin_can_update_department(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Old Ward', 'code' => 'OLD']);

        $this->actingAs($admin);
        $response = $this->put(route('departments.update', $dept), [
            'name' => 'Renovated Ward',
            'code' => 'REN',
            'floor' => 'Level 2',
            'head_of_department' => 'Dr. Jane Doe',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', [
            'id' => $dept->id,
            'name' => 'Renovated Ward',
            'code' => 'REN',
        ]);
    }

    public function test_admin_can_delete_empty_department(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Temporary Overflow Ward', 'code' => 'TEMP']);

        $this->actingAs($admin);
        $response = $this->delete(route('departments.destroy', $dept));

        $response->assertRedirect();
        $this->assertDatabaseMissing('departments', ['id' => $dept->id]);
    }

    public function test_admin_cannot_delete_department_with_assigned_equipment(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Maternity Ward', 'code' => 'MAT']);
        Equipment::factory()->create(['department_id' => $dept->id]);

        $this->actingAs($admin);
        $response = $this->delete(route('departments.destroy', $dept));

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', ['id' => $dept->id]);
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

    public function test_non_admin_cannot_access_departments_index(): void
    {
        $staff = User::factory()->departmentStaff()->create();

        $this->actingAs($staff);
        $response = $this->get(route('departments.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_search_departments_with_fuzzy_matching(): void
    {
        $admin = User::factory()->admin()->create();
        Department::create(['name' => 'Cardiovascular Surgery Unit', 'code' => 'CVSU']);
        Department::create(['name' => 'Pediatric Intensive Care', 'code' => 'PICU']);

        $this->actingAs($admin);

        $response = $this->get(route('departments.index', ['search' => 'cardio surg']));
        $response->assertOk()
            ->assertSee('Cardiovascular Surgery Unit')
            ->assertDontSee('Pediatric Intensive Care');
    }
}

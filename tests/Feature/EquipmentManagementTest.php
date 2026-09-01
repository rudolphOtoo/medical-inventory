<?php

namespace Tests\Feature;

use App\Enums\EquipmentStatus;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_equipment_and_search(): void
    {
        $admin = User::factory()->admin()->create();
        $deptA = Department::create(['name' => 'Emergency', 'code' => 'ED']);
        $deptB = Department::create(['name' => 'ICU', 'code' => 'ICU']);

        $eqA = Equipment::create([
            'name' => 'Defibrillator R-Series',
            'asset_tag' => 'MED-ED-101',
            'department_id' => $deptA->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $eqB = Equipment::create([
            'name' => 'Ventilator EV-500',
            'asset_tag' => 'MED-ICU-202',
            'department_id' => $deptB->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('equipment.index'));

        $response->assertOk()
            ->assertSee('MED-ED-101')
            ->assertSee('MED-ICU-202');

        // Search query filter
        $searchResponse = $this->get(route('equipment.index', ['search' => 'Ventilator']));
        $searchResponse->assertOk()
            ->assertSee('MED-ICU-202')
            ->assertDontSee('MED-ED-101');
    }

    public function test_department_user_is_scoped_to_own_department_equipment(): void
    {
        $deptA = Department::create(['name' => 'Emergency', 'code' => 'ED']);
        $deptB = Department::create(['name' => 'ICU', 'code' => 'ICU']);

        $staffA = User::factory()->departmentStaff($deptA->id)->create();

        $eqA = Equipment::create([
            'name' => 'Emergency Ultrasound',
            'asset_tag' => 'MED-ED-001',
            'department_id' => $deptA->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $eqB = Equipment::create([
            'name' => 'ICU Dialysis Unit',
            'asset_tag' => 'MED-ICU-001',
            'department_id' => $deptB->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $this->actingAs($staffA);
        $response = $this->get(route('equipment.index'));

        $response->assertOk()
            ->assertSee('MED-ED-001')
            ->assertDontSee('MED-ICU-001');
    }

    public function test_user_can_register_new_equipment(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Radiology', 'code' => 'RAD']);

        $this->actingAs($admin);
        $response = $this->post(route('equipment.store'), [
            'name' => 'Mobile X-Ray Unit',
            'asset_tag' => 'MED-RAD-999',
            'serial_number' => 'SN-RAD-999',
            'manufacturer' => 'Siemens Healthineers',
            'department_id' => $dept->id,
            'location' => 'Room 4',
            'status' => 'in_use',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('equipment', [
            'asset_tag' => 'MED-RAD-999',
            'name' => 'Mobile X-Ray Unit',
        ]);
    }

    public function test_status_update_and_archive_toggle(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Surgery', 'code' => 'SURG']);

        $equipment = Equipment::create([
            'name' => 'Cautery Machine',
            'asset_tag' => 'MED-SURG-111',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $this->actingAs($admin);

        // Update status
        $statusResponse = $this->patch(route('equipment.status', $equipment), [
            'status' => 'under_review',
        ]);
        $statusResponse->assertRedirect();
        $this->assertEquals(EquipmentStatus::UnderReview, $equipment->fresh()->status);

        // Toggle archive
        $archiveResponse = $this->post(route('equipment.archive', $equipment));
        $archiveResponse->assertRedirect();
        $this->assertTrue($equipment->fresh()->is_archived);
    }
}

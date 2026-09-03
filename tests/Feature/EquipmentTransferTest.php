<?php

namespace Tests\Feature;

use App\Enums\EquipmentStatus;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_transfer_equipment_between_departments(): void
    {
        $admin = User::factory()->admin()->create();
        $deptA = Department::create(['name' => 'Emergency', 'code' => 'ED']);
        $deptB = Department::create(['name' => 'Intensive Care Unit', 'code' => 'ICU']);

        $equipment = Equipment::create([
            'name' => 'Transport Monitor',
            'asset_tag' => 'MED-ED-333',
            'department_id' => $deptA->id,
            'location' => 'ED Bay 1',
            'status' => EquipmentStatus::InUse,
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('equipment.transfer', $equipment), [
            'department_id' => $deptB->id,
            'location' => 'ICU Bed 5',
        ]);

        $response->assertRedirect();
        $equipment->refresh();

        $this->assertEquals($deptB->id, $equipment->department_id);
        $this->assertEquals('ICU Bed 5', $equipment->location);

        $this->assertDatabaseHas('activity_logs', [
            'event_type' => 'equipment.transferred',
            'subject_id' => $equipment->id,
        ]);
    }

    public function test_unauthorized_user_cannot_transfer_other_department_equipment(): void
    {
        $deptA = Department::create(['name' => 'Emergency', 'code' => 'ED']);
        $deptB = Department::create(['name' => 'Radiology', 'code' => 'RAD']);

        $staffB = User::factory()->departmentStaff($deptB->id)->create();

        $equipment = Equipment::create([
            'name' => 'Crash Cart Defibrillator',
            'asset_tag' => 'MED-ED-444',
            'department_id' => $deptA->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $this->actingAs($staffB);
        $response = $this->post(route('equipment.transfer', $equipment), [
            'department_id' => $deptB->id,
        ]);

        $response->assertForbidden();
    }

    public function test_printable_asset_tag_renders_with_svg_qr_code(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Biomed', 'code' => 'BIOMED']);

        $equipment = Equipment::create([
            'name' => 'Dialysis Machine',
            'asset_tag' => 'MED-BIO-TAG-01',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
            'next_calibration_due' => now()->addMonths(6),
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('equipment.tag', $equipment));

        $response->assertOk()
            ->assertSee('MED-BIO-TAG-01')
            ->assertSee('Dialysis Machine')
            ->assertSee('<svg', false)
            ->assertSee('Print Clinical Label');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\EquipmentStatus;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentCalibrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_calibration_status_calculations(): void
    {
        $dept = Department::create(['name' => 'Biomed', 'code' => 'BIOMED']);

        // Overdue device
        $overdue = Equipment::create([
            'name' => 'Infusion Pump',
            'asset_tag' => 'MED-CAL-001',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
            'last_calibrated_at' => now()->subMonths(14),
            'next_calibration_due' => now()->subDays(5),
        ]);
        $this->assertEquals('overdue', $overdue->calibrationStatus()['key']);
        $this->assertTrue($overdue->isCalibrationOverdue());

        // Due soon device (<= 30 days)
        $dueSoon = Equipment::create([
            'name' => 'Vital Signs Monitor',
            'asset_tag' => 'MED-CAL-002',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
            'last_calibrated_at' => now()->subMonths(11),
            'next_calibration_due' => now()->addDays(15),
        ]);
        $this->assertEquals('due_soon', $dueSoon->calibrationStatus()['key']);

        // Certified device (> 30 days)
        $certified = Equipment::create([
            'name' => 'Ultrasound Probe',
            'asset_tag' => 'MED-CAL-003',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
            'last_calibrated_at' => now()->subMonths(2),
            'next_calibration_due' => now()->addMonths(10),
        ]);
        $this->assertEquals('certified', $certified->calibrationStatus()['key']);
    }

    public function test_user_can_update_calibration_certificate(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'ICU', 'code' => 'ICU']);

        $equipment = Equipment::create([
            'name' => 'Ventilator EV-800',
            'asset_tag' => 'MED-ICU-700',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('equipment.calibration', $equipment), [
            'last_calibrated_at' => now()->toDateString(),
            'next_calibration_due' => now()->addYear()->toDateString(),
        ]);

        $response->assertRedirect();
        $equipment->refresh();

        $this->assertEquals(now()->toDateString(), $equipment->last_calibrated_at->toDateString());
        $this->assertEquals(now()->addYear()->toDateString(), $equipment->next_calibration_due->toDateString());

        $this->assertDatabaseHas('activity_logs', [
            'event_type' => 'equipment.calibrated',
            'subject_id' => $equipment->id,
        ]);
    }

    public function test_equipment_directory_filters_by_calibration_status(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Emergency', 'code' => 'ED']);

        $overdue = Equipment::create([
            'name' => 'Defibrillator Unit',
            'asset_tag' => 'MED-ED-OVERDUE',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
            'next_calibration_due' => now()->subDays(10),
        ]);

        $certified = Equipment::create([
            'name' => 'Pulse Oximeter',
            'asset_tag' => 'MED-ED-CERTIFIED',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
            'next_calibration_due' => now()->addMonths(6),
        ]);

        $this->actingAs($admin);

        // Filter overdue
        $responseOverdue = $this->get(route('equipment.index', ['calibration_status' => 'overdue']));
        $responseOverdue->assertOk()
            ->assertSee('MED-ED-OVERDUE')
            ->assertDontSee('MED-ED-CERTIFIED');

        // Filter certified
        $responseCertified = $this->get(route('equipment.index', ['calibration_status' => 'certified']));
        $responseCertified->assertOk()
            ->assertSee('MED-ED-CERTIFIED')
            ->assertDontSee('MED-ED-OVERDUE');
    }
}

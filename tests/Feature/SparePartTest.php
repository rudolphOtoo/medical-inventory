<?php

namespace Tests\Feature;

use App\Enums\EquipmentStatus;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\IssueReport;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparePartTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_create_spare_part_in_catalog(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);
        $response = $this->post(route('spare-parts.store'), [
            'name' => 'Oxygen Fuel Cell',
            'part_number' => 'O2-CELL-99',
            'manufacturer' => 'EnviteC',
            'stock_quantity' => 15,
            'unit_cost' => 125.00,
            'description' => 'Medical O2 sensor cell.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('spare_parts', [
            'part_number' => 'O2-CELL-99',
            'stock_quantity' => 15,
        ]);

        $indexResponse = $this->get(route('spare-parts.index'));
        $indexResponse->assertOk()
            ->assertSee('Oxygen Fuel Cell')
            ->assertSee('O2-CELL-99');
    }

    public function test_admin_can_update_spare_part_stock_and_cost(): void
    {
        $admin = User::factory()->admin()->create();
        $part = SparePart::create([
            'name' => 'Pressure Transducer',
            'part_number' => 'PT-500',
            'stock_quantity' => 5,
            'unit_cost' => 80.00,
        ]);

        $this->actingAs($admin);
        $response = $this->put(route('spare-parts.update', $part), [
            'name' => 'Pressure Transducer Pro',
            'part_number' => 'PT-500',
            'stock_quantity' => 25,
            'unit_cost' => 95.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('spare_parts', [
            'id' => $part->id,
            'name' => 'Pressure Transducer Pro',
            'stock_quantity' => 25,
            'unit_cost' => 95.00,
        ]);
    }

    public function test_admin_can_delete_unused_spare_part(): void
    {
        $admin = User::factory()->admin()->create();
        $part = SparePart::create([
            'name' => 'Obsolete Filter',
            'part_number' => 'FLT-OBS-1',
            'stock_quantity' => 0,
        ]);

        $this->actingAs($admin);
        $response = $this->delete(route('spare-parts.destroy', $part));

        $response->assertRedirect();
        $this->assertDatabaseMissing('spare_parts', ['id' => $part->id]);
    }

    public function test_spare_parts_are_attached_to_issue_and_stock_decrements(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Biomed', 'code' => 'BIOMED']);

        $equipment = Equipment::create([
            'name' => 'Infusion Pump',
            'asset_tag' => 'MED-BIO-300',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::OutForRepair,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $admin->id,
            'department_id' => $dept->id,
            'title' => 'Peristaltic rotor worn',
            'description' => 'Flow rate drift detected.',
            'priority' => IssuePriority::High,
            'progress_status' => IssueProgress::InProgress,
        ]);

        $part = SparePart::create([
            'name' => 'Peristaltic Rotor Assembly',
            'part_number' => 'RTR-200',
            'stock_quantity' => 10,
            'unit_cost' => 85.50,
        ]);

        $this->actingAs($admin);
        $response = $this->patch(route('issues.status', $issue), [
            'progress_status' => 'resolved',
            'resolution_notes' => 'Replaced rotor assembly.',
            'spare_part_ids' => [$part->id],
            'spare_part_quantities' => [2],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('issue_spare_parts', [
            'issue_report_id' => $issue->id,
            'spare_part_id' => $part->id,
            'quantity_used' => 2,
        ]);

        $this->assertEquals(8, $part->fresh()->stock_quantity);
    }

    public function test_spare_part_is_not_attached_when_quantity_exceeds_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Radiology', 'code' => 'RAD']);

        $equipment = Equipment::create([
            'name' => 'X-Ray Tube',
            'asset_tag' => 'MED-RAD-400',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::UnderReview,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $admin->id,
            'department_id' => $dept->id,
            'title' => 'Anode wear',
            'description' => 'Reduced image quality.',
            'priority' => IssuePriority::Medium,
            'progress_status' => IssueProgress::InProgress,
        ]);

        $part = SparePart::create([
            'name' => 'X-Ray Anode',
            'part_number' => 'XRA-100',
            'stock_quantity' => 1,
            'unit_cost' => 1200,
        ]);

        $this->actingAs($admin);
        $this->patch(route('issues.status', $issue), [
            'progress_status' => 'awaiting_parts',
            'spare_part_ids' => [$part->id],
            'spare_part_quantities' => [5],
        ]);

        $this->assertEquals(1, $part->fresh()->stock_quantity);
        $this->assertDatabaseMissing('issue_spare_parts', [
            'issue_report_id' => $issue->id,
            'spare_part_id' => $part->id,
        ]);
    }

    public function test_repeated_submission_does_not_double_deduct_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Biomed', 'code' => 'BIOMED']);

        $equipment = Equipment::create([
            'name' => 'Infusion Pump',
            'asset_tag' => 'MED-IDEM-500',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::OutForRepair,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $admin->id,
            'department_id' => $dept->id,
            'title' => 'Rotor worn',
            'description' => 'Flow drift detected.',
            'priority' => IssuePriority::High,
            'progress_status' => IssueProgress::InProgress,
        ]);

        $part = SparePart::create([
            'name' => 'Rotor Assembly',
            'part_number' => 'RTR-300',
            'stock_quantity' => 10,
            'unit_cost' => 80,
        ]);

        $this->actingAs($admin);

        $payload = [
            'progress_status' => 'resolved',
            'spare_part_ids' => [$part->id],
            'spare_part_quantities' => [2],
        ];

        $this->patch(route('issues.status', $issue), $payload)->assertRedirect();
        $this->patch(route('issues.status', $issue), $payload)->assertRedirect();

        $this->assertEquals(8, $part->fresh()->stock_quantity);
        $this->assertSame(1, $issue->spareParts()->count());
    }

    public function test_low_stock_part_is_detected(): void
    {
        $low = SparePart::create([
            'name' => 'Sensor Cable',
            'part_number' => 'SNS-50',
            'stock_quantity' => 3,
            'unit_cost' => 15,
        ]);

        $healthy = SparePart::create([
            'name' => 'Battery Pack',
            'part_number' => 'BAT-100',
            'stock_quantity' => 20,
            'unit_cost' => 60,
        ]);

        $this->assertTrue($low->isLowStock());
        $this->assertFalse($healthy->isLowStock());
    }
}

<?php

namespace Tests\Feature;

use App\Enums\EquipmentStatus;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\IssueReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_report_issue_and_auto_updates_equipment_status_if_high(): void
    {
        $dept = Department::create(['name' => 'Emergency', 'code' => 'ED']);
        $staff = User::factory()->departmentStaff($dept->id)->create();

        $equipment = Equipment::create([
            'name' => 'Defibrillator Unit',
            'asset_tag' => 'MED-ED-500',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $this->actingAs($staff);
        $response = $this->post(route('issues.store'), [
            'equipment_id' => $equipment->id,
            'title' => 'Pads connector faulty',
            'description' => 'Pads connector pin bent during resuscitation drill.',
            'priority' => 'high',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('issue_reports', [
            'equipment_id' => $equipment->id,
            'title' => 'Pads connector faulty',
            'priority' => IssuePriority::High,
            'progress_status' => IssueProgress::Reported,
        ]);

        // Auto-transitioned equipment to Under Review
        $this->assertEquals(EquipmentStatus::UnderReview, $equipment->fresh()->status);
    }

    public function test_issue_triage_progress_transitions_and_return_to_service_gate(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Biomed', 'code' => 'BIOMED']);

        $equipment = Equipment::create([
            'name' => 'Infusion Pump',
            'asset_tag' => 'MED-BIO-200',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::OutForRepair,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $admin->id,
            'department_id' => $dept->id,
            'title' => 'Motor occlusion error',
            'description' => 'Alarm triggers at 50ml/h',
            'priority' => IssuePriority::Medium,
            'progress_status' => IssueProgress::InProgress,
        ]);

        $this->actingAs($admin);

        // Resolve ticket and certify return to service
        $response = $this->patch(route('issues.status', $issue), [
            'progress_status' => 'resolved',
            'resolution_notes' => 'Replaced pump peristaltic rotor assembly and passed flow rate validation.',
            'equipment_status' => 'in_use',
        ]);

        $response->assertRedirect();
        $this->assertEquals(IssueProgress::Resolved, $issue->fresh()->progress_status);
        $this->assertNotNull($issue->fresh()->resolved_at);
        $this->assertEquals(EquipmentStatus::InUse, $equipment->fresh()->status);
    }
}

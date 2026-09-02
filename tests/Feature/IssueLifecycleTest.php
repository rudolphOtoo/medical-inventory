<?php

namespace Tests\Feature;

use App\Enums\EquipmentStatus;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\IssueReport;
use App\Models\User;
use Carbon\Carbon;
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

    public function test_resolved_issue_shows_downtime_on_show_page(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Lab', 'code' => 'LAB']);

        $equipment = Equipment::create([
            'name' => 'Centrifuge',
            'asset_tag' => 'MED-LAB-100',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::UnderReview,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $admin->id,
            'department_id' => $dept->id,
            'title' => 'Rotor imbalance',
            'description' => 'Excessive vibration at high speed.',
            'priority' => IssuePriority::High,
            'progress_status' => IssueProgress::Resolved,
        ]);

        // Backdate the reported & resolution timestamps (bypasses auto-timestamps)
        IssueReport::withoutTimestamps(function () use ($issue) {
            $issue->forceFill([
                'created_at' => Carbon::now()->subHours(6),
                'resolved_at' => Carbon::now(),
            ])->save();
        });

        $this->actingAs($admin);
        $response = $this->get(route('issues.show', $issue));

        $response->assertOk();
        $response->assertSee('Downtime');
        $response->assertSee('360 min');
    }

    public function test_dashboard_shows_mttr_and_overdue_metrics(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'OR', 'code' => 'OR']);

        $equipment = Equipment::create([
            'name' => 'Anesthesia Machine',
            'asset_tag' => 'MED-OR-200',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
        ]);

        // Resolved issue: 2-hour MTTR
        $resolved = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $admin->id,
            'department_id' => $dept->id,
            'title' => 'Gas leak alarm',
            'description' => 'False positive on sevoflurane sensor.',
            'priority' => IssuePriority::Medium,
            'progress_status' => IssueProgress::Resolved,
        ]);
        IssueReport::withoutTimestamps(function () use ($resolved) {
            $resolved->forceFill([
                'created_at' => Carbon::now()->subHours(4),
                'resolved_at' => Carbon::now()->subHours(2),
            ])->save();
        });

        // Overdue high-priority issue (opened > 24h ago, still unresolved)
        $overdue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $admin->id,
            'department_id' => $dept->id,
            'title' => 'Ventilator circuit failure',
            'description' => 'Complete circuit break.',
            'priority' => IssuePriority::Critical,
            'progress_status' => IssueProgress::InProgress,
        ]);
        IssueReport::withoutTimestamps(function () use ($overdue) {
            $overdue->forceFill([
                'created_at' => Carbon::now()->subHours(36),
            ])->save();
        });

        $this->actingAs($admin);
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Avg MTTR');
        $response->assertSee('120');
        $response->assertSee('Overdue');
        $response->assertSee('1');
    }

    public function test_cannot_assign_user_from_another_department(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Biomed', 'code' => 'BIOMED']);
        $otherDept = Department::create(['name' => 'Radiology', 'code' => 'RAD']);

        $engineer = User::factory()->departmentStaff($otherDept->id)->create();

        $equipment = Equipment::create([
            'name' => 'Infusion Pump',
            'asset_tag' => 'MED-BIO-900',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::OutForRepair,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $admin->id,
            'department_id' => $dept->id,
            'title' => 'Flow drift',
            'description' => 'Flow rate unstable.',
            'priority' => IssuePriority::High,
            'progress_status' => IssueProgress::InProgress,
        ]);

        $this->actingAs($admin);
        $response = $this->patch(route('issues.status', $issue), [
            'progress_status' => IssueProgress::Assigned->value,
            'assigned_to_id' => $engineer->id,
        ]);

        $response->assertSessionHasErrors('assigned_to_id');
        $this->assertDatabaseHas('issue_reports', [
            'id' => $issue->id,
            'assigned_to_id' => null,
        ]);
    }

    public function test_can_assign_admin_or_same_department_user(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Surgery', 'code' => 'SURG']);

        $deptEngineer = User::factory()->departmentStaff($dept->id)->create();
        $otherAdmin = User::factory()->admin()->create();

        $equipment = Equipment::create([
            'name' => 'Electrosurgical Unit',
            'asset_tag' => 'MED-SURG-777',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::OutForRepair,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $admin->id,
            'department_id' => $dept->id,
            'title' => 'Grounding fault',
            'description' => 'Intermittent grounding fault.',
            'priority' => IssuePriority::Medium,
            'progress_status' => IssueProgress::InProgress,
        ]);

        $this->actingAs($admin);

        // Same-department engineer is assignable
        $this->patch(route('issues.status', $issue), [
            'progress_status' => IssueProgress::Assigned->value,
            'assigned_to_id' => $deptEngineer->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame($deptEngineer->id, $issue->fresh()->assigned_to_id);

        // Admin (regardless of department) is assignable
        $this->patch(route('issues.status', $issue), [
            'progress_status' => IssueProgress::Assigned->value,
            'assigned_to_id' => $otherAdmin->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame($otherAdmin->id, $issue->fresh()->assigned_to_id);
    }
}

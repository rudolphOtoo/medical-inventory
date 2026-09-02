<?php

namespace Tests\Feature;

use App\Enums\EquipmentStatus;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\IssueComment;
use App\Models\IssueReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_append_comment_to_issue(): void
    {
        $dept = Department::create(['name' => 'Radiology', 'code' => 'RAD']);
        $staff = User::factory()->departmentStaff($dept->id)->create();

        $equipment = Equipment::create([
            'name' => 'CT Scanner',
            'asset_tag' => 'MED-RAD-100',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::UnderReview,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $staff->id,
            'department_id' => $dept->id,
            'title' => 'Gantry vibration anomaly',
            'description' => 'Intermittent vibration during axial sweep.',
            'priority' => IssuePriority::High,
            'progress_status' => IssueProgress::InProgress,
        ]);

        $this->actingAs($staff);
        $response = $this->post(route('issues.comments.store', $issue), [
            'body' => 'Checked bearing assembly — within tolerance. Re-ran calibration sweep.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('issue_comments', [
            'issue_report_id' => $issue->id,
            'user_id' => $staff->id,
            'body' => 'Checked bearing assembly — within tolerance. Re-ran calibration sweep.',
            'is_internal_only' => false,
        ]);
    }

    public function test_comment_can_be_marked_internal_only(): void
    {
        $dept = Department::create(['name' => 'ICU', 'code' => 'ICU']);
        $staff = User::factory()->departmentStaff($dept->id)->create();

        $equipment = Equipment::create([
            'name' => 'Ventilator',
            'asset_tag' => 'MED-ICU-300',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $staff->id,
            'department_id' => $dept->id,
            'title' => 'Alarm threshold drift',
            'description' => 'SpO2 alarm triggers at 94% instead of 90%.',
            'priority' => IssuePriority::Medium,
            'progress_status' => IssueProgress::Acknowledged,
        ]);

        $this->actingAs($staff);
        $this->post(route('issues.comments.store', $issue), [
            'body' => 'Internal note: suspect sensor degradation, need replacement under warranty.',
            'is_internal_only' => true,
        ]);

        $this->assertDatabaseHas('issue_comments', [
            'issue_report_id' => $issue->id,
            'is_internal_only' => true,
        ]);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $dept = Department::create(['name' => 'Surgery', 'code' => 'SURG']);
        $staff = User::factory()->departmentStaff($dept->id)->create();

        $equipment = Equipment::create([
            'name' => 'Electrosurgical Unit',
            'asset_tag' => 'MED-SURG-400',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $staff->id,
            'department_id' => $dept->id,
            'title' => 'Grounding pad fault',
            'description' => 'Intermittent grounding fault during procedures.',
            'priority' => IssuePriority::Low,
            'progress_status' => IssueProgress::Reported,
        ]);

        $comment = IssueComment::create([
            'issue_report_id' => $issue->id,
            'user_id' => $staff->id,
            'body' => 'Test comment to be deleted.',
        ]);

        $this->actingAs($staff);
        $response = $this->delete(route('issues.comments.destroy', $comment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('issue_comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_other_users_comment(): void
    {
        $dept = Department::create(['name' => 'Pharmacy', 'code' => 'PHARM']);
        $author = User::factory()->departmentStaff($dept->id)->create();
        $other = User::factory()->departmentStaff($dept->id)->create();

        $equipment = Equipment::create([
            'name' => 'Automated Dispenser',
            'asset_tag' => 'MED-PHARM-500',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $author->id,
            'department_id' => $dept->id,
            'title' => 'Dispensing error',
            'description' => 'Incorrect dosage dispensed.',
            'priority' => IssuePriority::Critical,
            'progress_status' => IssueProgress::Assigned,
        ]);

        $comment = IssueComment::create([
            'issue_report_id' => $issue->id,
            'user_id' => $author->id,
            'body' => 'Author-only comment.',
        ]);

        $this->actingAs($other);
        $response = $this->delete(route('issues.comments.destroy', $comment));

        $response->assertForbidden();
        $this->assertDatabaseHas('issue_comments', ['id' => $comment->id]);
    }

    public function test_admin_can_delete_any_comment(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Emergency', 'code' => 'ED']);
        $staff = User::factory()->departmentStaff($dept->id)->create();

        $equipment = Equipment::create([
            'name' => 'Defibrillator',
            'asset_tag' => 'MED-ED-600',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $staff->id,
            'department_id' => $dept->id,
            'title' => 'Battery failure',
            'description' => 'Device shuts down during charge cycle.',
            'priority' => IssuePriority::High,
            'progress_status' => IssueProgress::InProgress,
        ]);

        $comment = IssueComment::create([
            'issue_report_id' => $issue->id,
            'user_id' => $staff->id,
            'body' => 'Staff comment that admin can remove.',
        ]);

        $this->actingAs($admin);
        $response = $this->delete(route('issues.comments.destroy', $comment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('issue_comments', ['id' => $comment->id]);
    }

    public function test_cannot_comment_on_unauthorized_issue(): void
    {
        $deptA = Department::create(['name' => 'Cardiology', 'code' => 'CARD']);
        $deptB = Department::create(['name' => 'Neurology', 'code' => 'NEUR']);
        $staffB = User::factory()->departmentStaff($deptB->id)->create();

        $equipment = Equipment::create([
            'name' => 'ECG Monitor',
            'asset_tag' => 'MED-CARD-700',
            'department_id' => $deptA->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $staffB->id,
            'department_id' => $deptA->id,
            'title' => 'Lead wire fault',
            'description' => 'Intermittent lead II signal loss.',
            'priority' => IssuePriority::Medium,
            'progress_status' => IssueProgress::Reported,
        ]);

        $this->actingAs($staffB);
        $response = $this->post(route('issues.comments.store', $issue), [
            'body' => 'This should be forbidden.',
        ]);

        $response->assertForbidden();
    }
}

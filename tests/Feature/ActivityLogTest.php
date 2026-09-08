<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_activity_ledger(): void
    {
        $admin = User::factory()->admin()->create();
        ActivityLog::record($admin, 'test.event', 'Test activity logged');

        $this->actingAs($admin);
        $response = $this->get(route('activity.index'));

        $response->assertOk()
            ->assertSee('Test activity logged')
            ->assertSee('Export Excel');
    }

    public function test_admin_can_export_activity_ledger_to_excel(): void
    {
        $admin = User::factory()->admin()->create();
        ActivityLog::record($admin, 'test.event', 'Sample event for export');

        $this->actingAs($admin);
        $response = $this->get(route('activity.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_prune_activity_logs(): void
    {
        $admin = User::factory()->admin()->create();

        // Log an old activity
        ActivityLog::create([
            'causer_id' => $admin->id,
            'event_type' => 'old.event',
            'description' => 'Old record from past',
            'created_at' => now()->subDays(45),
        ]);

        // Log a fresh activity
        ActivityLog::create([
            'causer_id' => $admin->id,
            'event_type' => 'fresh.event',
            'description' => 'Fresh recent record',
            'created_at' => now(),
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('activity.prune'), ['mode' => 'older_than_30']);

        $response->assertRedirect();
        $this->assertDatabaseMissing('activity_logs', ['event_type' => 'old.event']);
        $this->assertDatabaseHas('activity_logs', ['event_type' => 'fresh.event']);
    }

    public function test_admin_can_clear_all_activity_logs(): void
    {
        $admin = User::factory()->admin()->create();
        ActivityLog::record($admin, 'some.event', 'Will be cleared');

        $this->actingAs($admin);
        $response = $this->post(route('activity.prune'), ['mode' => 'all']);

        $response->assertRedirect();
        $this->assertDatabaseMissing('activity_logs', ['event_type' => 'some.event']);
        $this->assertDatabaseHas('activity_logs', ['event_type' => 'audit.cleared']);
    }

    public function test_non_admin_cannot_access_or_prune_activity_logs(): void
    {
        $staff = User::factory()->departmentStaff()->create();

        $this->actingAs($staff);
        $this->get(route('activity.index'))->assertForbidden();
        $this->get(route('activity.export'))->assertForbidden();
        $this->post(route('activity.prune'), ['mode' => 'all'])->assertForbidden();
    }
}

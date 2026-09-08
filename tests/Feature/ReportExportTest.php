<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_weekly_operations_report(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Intensive Care Unit', 'code' => 'ICU']);
        Equipment::factory()->create(['department_id' => $dept->id]);

        $this->actingAs($admin);
        $response = $this->get(route('reports.weekly'));

        $response->assertOk()
            ->assertSee('Weekly Executive Report')
            ->assertSee('Intensive Care Unit')
            ->assertSee('Export Excel (.xlsx)');
    }

    public function test_admin_can_view_printable_pdf_report(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);
        $response = $this->get(route('reports.print'));

        $response->assertOk()
            ->assertSee('MedTrack Clinical Operations Report')
            ->assertSee('OFFICIAL REPORT');
    }

    public function test_admin_can_download_weekly_report_excel(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Radiology Department', 'code' => 'RAD']);
        Equipment::factory()->create(['department_id' => $dept->id]);

        $this->actingAs($admin);
        $response = $this->get(route('reports.download', ['type' => 'weekly']));

        $response->assertOk();
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );
        $this->assertStringContainsString('MedTrack_Weekly_Report_', $response->headers->get('Content-Disposition'));
    }

    public function test_admin_can_download_equipment_inventory_excel(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Surgery Center', 'code' => 'SURG']);
        Equipment::factory()->create(['department_id' => $dept->id, 'name' => 'Electrosurgical Generator', 'asset_tag' => 'MED-SURG-101']);

        $this->actingAs($admin);
        $response = $this->get(route('equipment.export'));

        $response->assertOk();
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );
        $this->assertStringContainsString('MedTrack_Equipment_Inventory_', $response->headers->get('Content-Disposition'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\ClinicalNote;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_user_cannot_create_or_update_spare_parts(): void
    {
        $user = User::factory()->departmentStaff()->create();
        $part = SparePart::factory()->create();

        $this->actingAs($user);

        $this->post(route('spare-parts.store'), [
            'name' => 'Unauthorized Part',
            'part_number' => 'SP-NOT-ALLOWED',
            'stock_quantity' => 1,
        ])->assertForbidden();

        $this->put(route('spare-parts.update', $part), [
            'name' => 'Unauthorized Edit',
            'part_number' => $part->part_number,
            'stock_quantity' => 99,
        ])->assertForbidden();
    }

    public function test_department_user_can_browse_spare_parts_catalogue(): void
    {
        SparePart::factory()->create(['name' => 'Ventilator Valve', 'part_number' => 'SP-VALVE-01']);

        $user = User::factory()->departmentStaff()->create();

        $this->actingAs($user)
            ->get(route('spare-parts.index'))
            ->assertOk()
            ->assertSee('SP-VALVE-01');
    }

    public function test_department_user_cannot_access_facility_wide_reports(): void
    {
        $user = User::factory()->departmentStaff()->create();

        $this->actingAs($user);

        $this->get(route('reports.weekly'))->assertForbidden();
        $this->get(route('reports.print'))->assertForbidden();
        $this->get(route('reports.download'))->assertForbidden();
        $this->get(route('equipment.export'))->assertForbidden();
    }

    public function test_note_department_is_forced_to_own_department_for_staff(): void
    {
        $ownDept = Department::create(['name' => 'Emergency', 'code' => 'ED']);
        $otherDept = Department::create(['name' => 'Radiology', 'code' => 'RAD']);

        Equipment::create(['name' => 'Own X-Ray', 'asset_tag' => 'MED-OWN-1', 'department_id' => $ownDept->id]);
        $otherEquipment = Equipment::create(['name' => 'Other X-Ray', 'asset_tag' => 'MED-OTHER-1', 'department_id' => $otherDept->id]);

        $user = User::factory()->departmentStaff($ownDept->id)->create();

        $this->actingAs($user)
            ->post(route('notes.store'), [
                'title' => 'Cross-dept attempt',
                'body' => 'Should be forced back to own department.',
                'color' => 'azure',
                'department_id' => $otherDept->id,
                'equipment_id' => $otherEquipment->id,
            ])
            ->assertRedirect();

        $note = ClinicalNote::where('title', 'Cross-dept attempt')->first();

        $this->assertNotNull($note);
        $this->assertSame($ownDept->id, $note->department_id);
        $this->assertNull($note->equipment_id);
    }
}

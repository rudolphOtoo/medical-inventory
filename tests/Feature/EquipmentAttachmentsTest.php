<?php

namespace Tests\Feature;

use App\Enums\EquipmentStatus;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EquipmentAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_photo_and_manual_on_registration(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Cardiology', 'code' => 'CARD']);

        $photo = UploadedFile::fake()->image('ecg_machine.jpg', 600, 600);
        $manual = UploadedFile::fake()->create('manual.pdf', 500, 'application/pdf');

        $this->actingAs($admin);
        $response = $this->post(route('equipment.store'), [
            'name' => '12-Lead ECG Machine',
            'asset_tag' => 'MED-CARD-001',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse->value,
            'photo' => $photo,
            'manual' => $manual,
        ]);

        $response->assertRedirect();
        $equipment = Equipment::where('asset_tag', 'MED-CARD-001')->firstOrFail();

        $this->assertNotNull($equipment->photo_path);
        $this->assertNotNull($equipment->manual_path);

        Storage::disk('public')->assertExists($equipment->photo_path);
        Storage::disk('public')->assertExists($equipment->manual_path);
    }

    public function test_user_can_upload_attachments_from_spec_sheet(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'ICU', 'code' => 'ICU']);

        $equipment = Equipment::create([
            'name' => 'Syringe Pump',
            'asset_tag' => 'MED-ICU-888',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $photo = UploadedFile::fake()->image('pump.png', 400, 400);

        $this->actingAs($admin);
        $response = $this->post(route('equipment.attachments.store', $equipment), [
            'photo' => $photo,
        ]);

        $response->assertRedirect();
        $equipment->refresh();

        $this->assertNotNull($equipment->photo_path);
        Storage::disk('public')->assertExists($equipment->photo_path);
    }

    public function test_attachment_upload_validates_mime_types(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Surgery', 'code' => 'SURG']);

        $equipment = Equipment::create([
            'name' => 'Cautery Machine',
            'asset_tag' => 'MED-SURG-777',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
        ]);

        $invalidFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        $this->actingAs($admin);
        $response = $this->post(route('equipment.attachments.store', $equipment), [
            'photo' => $invalidFile,
        ]);

        $response->assertSessionHasErrors(['photo']);
    }

    public function test_user_can_delete_attached_photo_or_manual(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $dept = Department::create(['name' => 'Biomed', 'code' => 'BIOMED']);

        $photoPath = 'equipment/photos/test_photo.jpg';
        Storage::disk('public')->put($photoPath, 'fake-image-content');

        $equipment = Equipment::create([
            'name' => 'Defibrillator Tester',
            'asset_tag' => 'MED-BIO-555',
            'department_id' => $dept->id,
            'status' => EquipmentStatus::InUse,
            'photo_path' => $photoPath,
        ]);

        $this->actingAs($admin);
        $response = $this->delete(route('equipment.attachments.destroy', [$equipment, 'photo']));

        $response->assertRedirect();
        $equipment->refresh();

        $this->assertNull($equipment->photo_path);
        Storage::disk('public')->assertMissing($photoPath);
    }
}

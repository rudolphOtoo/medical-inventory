<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class BackupCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        File::ensureDirectoryExists(storage_path('backups'));
    }

    protected function tearDown(): void
    {
        foreach (File::glob(storage_path('backups/medtrack_backup_*.zip')) as $file) {
            File::delete($file);
        }

        Cache::forget('latest_backup_file');

        parent::tearDown();
    }

    public function test_backup_command_creates_a_valid_zip_archive(): void
    {
        $this->artisan('medtrack:backup')->assertExitCode(0);

        $latest = cache('latest_backup_file');
        $this->assertNotNull($latest);

        $zipPath = storage_path('backups/'.$latest);
        $this->assertFileExists($zipPath);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($zipPath));

        $filenames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filenames[] = $zip->getNameIndex($i);
        }
        $zip->close();

        $this->assertContains('database/database.sqlite', $filenames);
    }

    public function test_health_page_shows_latest_backup_info(): void
    {
        $this->artisan('medtrack:backup');

        $user = User::factory()->admin()->create();
        $latest = cache('latest_backup_file');

        $this->actingAs($user);
        $response = $this->get(route('health'));

        $response->assertOk();
        $response->assertSee('LAN Backup Archive');
        $response->assertSee($latest);
    }

    public function test_download_route_returns_backup_stream_for_authenticated_user(): void
    {
        $this->artisan('medtrack:backup');

        $latest = cache('latest_backup_file');
        $zipPath = storage_path('backups/'.$latest);
        $expectedSize = File::size($zipPath);

        $user = User::factory()->admin()->create();

        $this->actingAs($user);
        $response = $this->get(route('health.backup.download'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/zip');
        $this->assertSame($expectedSize, strlen($response->streamedContent()));
    }

    public function test_guest_cannot_download_backup(): void
    {
        $this->get(route('health.backup.download'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_download_backup(): void
    {
        $this->artisan('medtrack:backup');

        $dept = Department::create(['name' => 'Ward', 'code' => 'WARD']);
        $staff = User::factory()->departmentStaff($dept->id)->create();

        $this->actingAs($staff);
        $this->get(route('health.backup.download'))
            ->assertForbidden();
    }

    public function test_download_returns_404_when_no_backup_exists(): void
    {
        Cache::forget('latest_backup_file');

        $user = User::factory()->admin()->create();

        $this->actingAs($user);
        $this->get(route('health.backup.download'))
            ->assertNotFound();
    }
}

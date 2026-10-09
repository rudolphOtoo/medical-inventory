<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseBackupCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $backupDir;

    private string $sourceDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($this->backupDir);

        $this->sourceDatabase = tempnam(sys_get_temp_dir(), 'medtrack_db').'.sqlite';
        File::put($this->sourceDatabase, 'fixture-sqlite-content');

        config(['database.connections.sqlite.database' => $this->sourceDatabase]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backupDir);
        File::delete($this->sourceDatabase);

        parent::tearDown();
    }

    public function test_sqlite_backup_creates_a_copy_of_the_database(): void
    {
        config(['database.default' => 'sqlite']);

        $this->artisan('db:backup')->assertExitCode(0);

        $backups = File::glob($this->backupDir.'/backup-*.sqlite');
        $this->assertCount(1, $backups);
        $this->assertSame(File::get($this->sourceDatabase), File::get($backups[0]));
    }

    public function test_sqlite_backup_preserves_wal_sidecar_files(): void
    {
        config(['database.default' => 'sqlite']);

        File::put($this->sourceDatabase.'-wal', 'wal-content');

        $this->artisan('db:backup')->assertExitCode(0);

        $backup = File::glob($this->backupDir.'/backup-*.sqlite')[0];
        $this->assertFileExists($backup.'-wal');
        $this->assertSame('wal-content', File::get($backup.'-wal'));
    }

    public function test_retention_prunes_stale_backups_but_keeps_fresh_ones(): void
    {
        config(['database.default' => 'sqlite']);

        $stale = $this->backupDir.'/backup-20200101_000000.sqlite';
        File::put($stale, 'stale');
        touch($stale, now()->subDays(40)->getTimestamp());

        $this->artisan('db:backup', ['--days' => 30])->assertExitCode(0);

        $this->assertFileDoesNotExist($stale);
        $this->assertCount(1, File::glob($this->backupDir.'/backup-*.sqlite'));
    }

    public function test_command_fails_gracefully_for_unsupported_drivers(): void
    {
        config(['database.default' => 'mysql']);

        try {
            $this->artisan('db:backup')->assertExitCode(1);
        } finally {
            config(['database.default' => 'sqlite']);
        }
    }
}

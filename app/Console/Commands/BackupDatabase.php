<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medtrack:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a timestamped LAN backup of the SQLite database and attached public assets.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $backupDir = storage_path('backups');
        File::ensureDirectoryExists($backupDir);

        $timestamp = now()->format('Ymd_His');
        $zipPath = $backupDir."/medtrack_backup_{$timestamp}.zip";

        $dbPath = database_path('database.sqlite');
        $publicPath = storage_path('app/public');

        if (! File::exists($dbPath)) {
            $this->error('SQLite database not found at '.$dbPath);

            return self::FAILURE;
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Unable to create backup archive.');

            return self::FAILURE;
        }

        $ok = true;

        // Add the SQLite database (with WAL/SHM flushed notation in filename)
        $ok = $ok && $zip->addFile($dbPath, 'database/database.sqlite');

        // Add any SQLite sidecar files (WAL/SHM) to preserve transaction integrity
        foreach (['-wal', '-shm'] as $suffix) {
            $sidecar = $dbPath.$suffix;
            if (File::exists($sidecar)) {
                $ok = $ok && $zip->addFile($sidecar, 'database/'.basename($sidecar));
            }
        }

        // Add attached public assets from storage/app/public
        if (File::isDirectory($publicPath)) {
            $files = File::allFiles($publicPath);
            foreach ($files as $file) {
                $relativePath = 'storage/app/public/'.$file->getRelativePathname();
                $ok = $ok && $zip->addFile($file->getPathname(), $relativePath);
            }
        }

        $closed = $zip->close() === true;

        if (! $ok || ! $closed) {
            $this->error('Backup archive creation failed mid-write.');

            if (File::exists($zipPath)) {
                File::delete($zipPath);
            }

            cache()->forget('latest_backup_file');

            return self::FAILURE;
        }

        // Store latest backup filename for the health page to reference
        cache()->forever('latest_backup_file', basename($zipPath));

        $this->info("Backup created successfully at {$zipPath}");

        return self::SUCCESS;
    }
}

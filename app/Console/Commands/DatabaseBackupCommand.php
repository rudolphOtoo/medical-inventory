<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Process as SymfonyProcess;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--days=30 : Number of days to retain backup archives}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a driver-aware database backup (PostgreSQL or SQLite).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $driver = config('database.default');

        $result = match ($driver) {
            'pgsql' => $this->backupPostgres($directory),
            'sqlite' => $this->backupSqlite($directory),
            default => $this->unsupportedDriver($driver),
        };

        if ($result !== self::SUCCESS) {
            return $result;
        }

        $pruned = $this->prune($directory, (int) $this->option('days'));

        if ($pruned > 0) {
            $this->info("Pruned {$pruned} backup archive(s) older than {$this->option('days')} day(s).");
        }

        return self::SUCCESS;
    }

    /**
     * Stream a `pg_dump` output into a gzip-compressed archive.
     *
     * @param  string  $directory  Destination directory.
     */
    protected function backupPostgres(string $directory): int
    {
        $connection = config('database.connections.pgsql');

        $command = array_filter([
            'pg_dump',
            '--host', $connection['host'] ?? '127.0.0.1',
            '--port', (string) ($connection['port'] ?? 5432),
            '--username', $connection['username'] ?? 'postgres',
            '--dbname', $connection['database'] ?? '',
        ], fn (string $value): bool => $value !== '');

        $timestamp = now()->format('Ymd_His');
        $destination = $directory."/backup-{$timestamp}.sql.gz";

        $outputHandle = gzopen($destination, 'wb6');

        if ($outputHandle === false) {
            $this->error('Unable to open backup destination for writing.');

            return self::FAILURE;
        }

        try {
            $result = Process::path($directory)
                ->env(['PGPASSWORD' => $connection['password'] ?? ''])
                ->timeout(600)
                ->run($command, function (string $type, string $buffer) use ($outputHandle): void {
                    if ($type === SymfonyProcess::OUT) {
                        gzwrite($outputHandle, $buffer);
                    }
                });
        } finally {
            gzclose($outputHandle);
        }

        if ($result->exitCode() !== 0) {
            File::delete($destination);

            $this->error('pg_dump failed: '.trim($result->errorOutput()));

            return self::FAILURE;
        }

        $this->info("PostgreSQL backup created at {$destination} ({$this->humanFileSize(File::size($destination))}).");

        return self::SUCCESS;
    }

    /**
     * Safely copy the SQLite database file (including WAL/SHM sidecars).
     *
     * @param  string  $directory  Destination directory.
     */
    protected function backupSqlite(string $directory): int
    {
        $source = config('database.connections.sqlite.database');

        if (! is_string($source) || $source === ':memory:') {
            $source = database_path('database.sqlite');
        }

        if (! File::exists($source)) {
            $this->error("SQLite database not found at {$source}.");

            return self::FAILURE;
        }

        $timestamp = now()->format('Ymd_His');
        $destinationBase = $directory."/backup-{$timestamp}.sqlite";

        File::copy($source, $destinationBase);

        foreach (['-wal', '-shm'] as $suffix) {
            if (File::exists($source.$suffix)) {
                File::copy($source.$suffix, $destinationBase.$suffix);
            }
        }

        $this->info("SQLite backup created at {$destinationBase} ({$this->humanFileSize(File::size($destinationBase))}).");

        return self::SUCCESS;
    }

    /**
     * Report an unsupported database driver.
     *
     * @param  string  $driver  The configured database driver name.
     */
    protected function unsupportedDriver(string $driver): int
    {
        $this->error("Database driver '{$driver}' is not supported by the db:backup command.");

        return self::FAILURE;
    }

    /**
     * Remove backup archives older than the retention window.
     *
     * @param  string  $directory  Backup directory.
     * @param  int  $days  Retention period in days.
     */
    protected function prune(string $directory, int $days): int
    {
        $cutoff = now()->subDays($days)->getTimestamp();
        $pruned = 0;

        foreach (File::files($directory) as $file) {
            if (! str_starts_with($file->getFilename(), 'backup-')) {
                continue;
            }

            if ($file->getMTime() < $cutoff) {
                File::delete($file->getPathname());
                $pruned++;
            }
        }

        return $pruned;
    }

    /**
     * Format a byte count into a human-readable string.
     */
    protected function humanFileSize(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $magnitude = 1024 ** min($power, count($units) - 1);

        return sprintf('%s %s', number_format($bytes / $magnitude, 2), $units[$power] ?? 'B');
    }
}

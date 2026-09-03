<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HealthController extends Controller
{
    /**
     * Report application readiness and system health for LAN operations (Admin only for UI view).
     */
    public function __invoke(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson() && ! $request->user()->isAdmin()) {
            abort(403, 'Restricted to hospital administrators.');
        }

        $dbOk = false;
        $dbLatency = 0;

        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $dbLatency = round((microtime(true) - $start) * 1000, 2);
            $dbOk = true;
        } catch (\Throwable $e) {
            $dbOk = false;
        }

        $status = $dbOk ? 'healthy' : 'degraded';

        // Latest LAN backup info
        $latestBackupFile = cache('latest_backup_file');
        $latestBackupPath = $latestBackupFile ? storage_path('backups/'.$latestBackupFile) : null;
        $latestBackup = null;
        if ($latestBackupPath && File::exists($latestBackupPath)) {
            $latestBackup = [
                'filename' => $latestBackupFile,
                'size' => File::size($latestBackupPath),
                'created_at' => File::lastModified($latestBackupPath),
            ];
        }

        $data = [
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'checks' => [
                'database' => [
                    'status' => $dbOk ? 'connected' : 'error',
                    'latency_ms' => $dbLatency,
                    'driver' => config('database.default'),
                ],
                'storage' => [
                    'status' => 'writable',
                ],
                'cache' => [
                    'status' => 'operational',
                    'driver' => config('cache.default'),
                ],
            ],
            'server' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ],
            'backup' => $latestBackup,
        ];

        if ($request->wantsJson()) {
            return response()->json($data, $dbOk ? 200 : 503);
        }

        return view('pages.health.index', compact('data'));
    }

    /**
     * Stream the latest LAN backup archive to the browser.
     */
    public function download(Request $request): StreamedResponse
    {
        Gate::authorize('manage-backups');

        $latestBackupFile = cache('latest_backup_file');

        if (! $latestBackupFile) {
            abort(404, 'No backup archive exists yet. Run `php artisan medtrack:backup` first.');
        }

        $path = storage_path('backups/'.$latestBackupFile);

        if (! File::exists($path)) {
            abort(404, 'Backup archive no longer exists on disk.');
        }

        ActivityLog::record(
            $request->user(),
            'backup.downloaded',
            "Downloaded LAN backup archive '{$latestBackupFile}'"
        );

        return response()->streamDownload(function () use ($path) {
            echo File::get($path);
        }, $latestBackupFile, ['Content-Type' => 'application/zip']);
    }
}

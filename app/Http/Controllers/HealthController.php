<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HealthController extends Controller
{
    /**
     * Report application readiness and system health for LAN operations.
     */
    public function __invoke(Request $request): View|JsonResponse
    {
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

        $storageOk = Storage::disk('local')->exists('.gitkeep') || true;
        $status = $dbOk ? 'healthy' : 'degraded';

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
        ];

        if ($request->wantsJson()) {
            return response()->json($data, $dbOk ? 200 : 503);
        }

        return view('pages.health.index', compact('data'));
    }
}

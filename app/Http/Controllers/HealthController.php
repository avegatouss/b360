<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Application health check endpoint.
     *
     * Returns status of critical services: database, cache, disk space.
     * Used by monitoring tools, load balancers, and deployment pipelines.
     */
    public function __invoke(): JsonResponse
    {
        $checks = [];

        // Database connectivity
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = 'error: '.$e->getMessage();
        }

        // Disk space (warn if < 100 MB free)
        $freeBytes = disk_free_space(storage_path());
        if ($freeBytes === false) {
            $checks['disk'] = 'error: unable to check';
        } else {
            $checks['disk'] = $freeBytes > 100 * 1024 * 1024
                ? 'ok'
                : 'low: '.round($freeBytes / 1024 / 1024).'MB';
        }

        // Cache read/write
        try {
            Cache::put('health_check', true, 10);
            $checks['cache'] = Cache::get('health_check') ? 'ok' : 'error';
        } catch (\Exception $e) {
            $checks['cache'] = 'error: '.$e->getMessage();
        }

        // Queue driver configured
        $checks['queue'] = config('queue.default', 'sync') !== 'null' ? 'ok' : 'not configured';

        $allOk = collect($checks)->every(fn ($v) => str_starts_with($v, 'ok'));

        return response()->json([
            'status' => $allOk ? 'healthy' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $allOk ? 200 : 503);
    }
}

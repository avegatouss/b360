<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Reporting\Models\ApiLog;
use Symfony\Component\HttpFoundation\Response;

/**
 * Log every API request: endpoint, method, response code, IP, duration.
 */
final class ApiLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $durationMs = (int) ((microtime(true) - $start) * 1000);

        try {
            ApiLog::create([
                'method' => $request->method(),
                'endpoint' => substr($request->path(), 0, 500),
                'response_code' => $response->getStatusCode(),
                'ip' => $request->ip(),
                'duration_ms' => $durationMs,
                'user_id' => auth()->id(),
                'instance_id' => CurrentInstance::get()?->id,
                'requested_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Silently fail — logging should never break the API response
            report($e);
        }

        return $response;
    }
}

<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;

final class EnsureInstanceResolved
{
    public function handle(Request $request, Closure $next)
    {
        // If InstanceMiddleware already bound currentInstance, we validate it here.
        $instance = CurrentInstance::get();

        if (!$instance) {
            $strategy = config('core.no_instance_strategy', 'block');

            if ($strategy === 'root') {
                // Explicit fallback requires a root instance query (system DB).
                // Only use if you've decided root fallback is acceptable.
                $root = \App\Instances\Instance::query()
                    ->on('system')
                    ->where('slug', 'root')
                    ->first();

                if ($root) {
                    CurrentInstance::set($root);
                    return $next($request);
                }
            }

            // Safe-by-default: block hard (prevents cross-instance data leak by accidental unscoped queries)
            abort(503, 'Instance context not resolved.');
        }

        return $next($request);
    }
}

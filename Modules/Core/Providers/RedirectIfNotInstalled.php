<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next)
    {
        $installed = (bool) config('app.installed', false);

        if ($installed) {
            return $next($request);
        }

        // Allow installer routes
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // Allow uptime endpoint
        if ($request->is('up')) {
            return $next($request);
        }

        return redirect('/install');
    }
}

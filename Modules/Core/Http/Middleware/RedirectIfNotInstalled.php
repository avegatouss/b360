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

        // Autoriser les routes de l'installateur
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // Autoriser le healthcheck
        if ($request->is('up')) {
            return $next($request);
        }

        return redirect('/install');
    }
}

<?php

namespace App\Http\Middleware;

use App\Installer\InstallLock;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Autoriser le healthcheck + installer
        if ($request->is('up') || $request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        $installed = (bool) config('app.installed', false) || InstallLock::isInstalled();

        if (!$installed) {
            return redirect('/install');
        }

        return $next($request);
    }
}

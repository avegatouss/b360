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
        // Autoriser le healthcheck (même avant installation)
        if ($request->is('up')) {
            return $next($request);
        }

        $installed = (bool) config('app.installed', false) || InstallLock::isInstalled();
        // Tant que non installé : autoriser l'installateur
        if (!$installed && ($request->is('install') || $request->is('install/*'))) {
            return $next($request);
        }

        if (!$installed) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Application not installed.',
                ], 503);
            }
            return redirect('/install');
        }

        return $next($request);
    }
}

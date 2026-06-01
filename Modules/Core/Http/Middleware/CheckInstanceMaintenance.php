<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Symfony\Component\HttpFoundation\Response;

final class CheckInstanceMaintenance
{
    /**
     * Routes that are always accessible during maintenance.
     */
    private const BYPASS_ROUTES = [
        'login',
        'logout',
        'instance.login',
        'instance.logout',
        'core.health',
        'maintenance.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $instance = CurrentInstance::get();

        // No instance resolved or maintenance is off — pass through
        if (!$instance || !$instance->is_maintenance) {
            return $next($request);
        }

        // Allow if user IP is in allowed IPs list
        $allowedIps = $instance->maintenance_allowed_ips ?? [];
        if (in_array($request->ip(), $allowedIps, true)) {
            return $next($request);
        }

        // Allow if user is super-admin or instance-admin
        $user = $request->user();
        if ($user) {
            if ($user->hasRole('super-admin') || $user->hasRole('instance-admin')) {
                return $next($request);
            }
        }

        // Allow bypass routes (login, logout, maintenance page)
        if ($this->isBypassRoute($request)) {
            return $next($request);
        }

        // Return maintenance view with 503 status
        $message = $instance->maintenance_message
            ?: 'Ce service est temporairement indisponible pour maintenance. Veuillez réessayer ultérieurement.';

        return response()
            ->view('core::maintenance', [
                'message' => $message,
                'instanceName' => $instance->name,
            ], 503);
    }

    private function isBypassRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return false;
        }

        foreach (self::BYPASS_ROUTES as $pattern) {
            if (str_contains($pattern, '*')) {
                $regex = str_replace('*', '.*', $pattern);
                if (preg_match("/^{$regex}$/", $routeName)) {
                    return true;
                }
            } elseif ($routeName === $pattern) {
                return true;
            }
        }

        return false;
    }
}

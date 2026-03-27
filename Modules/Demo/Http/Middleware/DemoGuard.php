<?php

namespace Modules\Demo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block destructive actions when the application is in demo mode.
 *
 * Demo mode is detected via:
 * 1. APP_DEMO=true in .env
 * 2. setting('general.demo_mode') = true
 */
class DemoGuard
{
    /**
     * Actions that are always blocked in demo mode.
     */
    private const BLOCKED_ROUTES = [
        'backups.restore',
        'backups.destroy',
        'backups.create',
    ];

    /**
     * Route patterns that are blocked in demo mode.
     */
    private const BLOCKED_PATTERNS = [
        'password*',
        '*.destroy',
    ];

    /**
     * Specific action keywords in route names to block.
     */
    private const BLOCKED_KEYWORDS = [
        'password.update',
        'settings.update',
        'settings.store',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->isDemoMode()) {
            return $next($request);
        }

        // Block all DELETE requests
        if ($request->isMethod('DELETE')) {
            return $this->denyAction($request);
        }

        // Block specific routes
        $routeName = $request->route()?->getName();

        if ($routeName) {
            // Check exact match
            if (in_array($routeName, self::BLOCKED_ROUTES, true)) {
                return $this->denyAction($request);
            }

            // Check keyword matches
            foreach (self::BLOCKED_KEYWORDS as $keyword) {
                if (str_contains($routeName, $keyword)) {
                    return $this->denyAction($request);
                }
            }

            // Check pattern matches
            foreach (self::BLOCKED_PATTERNS as $pattern) {
                if (fnmatch($pattern, $routeName)) {
                    return $this->denyAction($request);
                }
            }
        }

        // Block specific destructive POST actions
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            // Allow login/logout/CSRF
            $allowedPaths = ['login', 'logout', 'lockscreen', 'theme/switch'];
            $path = $request->path();

            foreach ($allowedPaths as $allowed) {
                if (str_contains($path, $allowed)) {
                    return $next($request);
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if application is in demo mode.
     */
    private function isDemoMode(): bool
    {
        // Check env variable
        if (config('app.demo', false) || env('APP_DEMO', false)) {
            return true;
        }

        // Check setting
        if (function_exists('setting')) {
            try {
                return (bool) setting('general.demo_mode', false);
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }

    /**
     * Deny the action with a flash message.
     */
    private function denyAction(Request $request): Response
    {
        $message = 'Cette action est desactivee en mode demonstration.';

        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 403);
        }

        return redirect()->back()->with('error', $message);
    }
}

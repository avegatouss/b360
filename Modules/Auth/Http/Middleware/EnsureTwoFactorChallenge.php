<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect authenticated users with 2FA enabled to the challenge page
 * if they haven't yet verified their TOTP code in this session.
 *
 * Register with alias: auth.2fa
 */
class EnsureTwoFactorChallenge
{
    /**
     * Routes that should be skipped (2FA flow pages).
     */
    private const EXCLUDED_PREFIXES = [
        'two-factor/challenge',
        'two-factor/recovery',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Global 2FA kill-switch from settings
        if (!setting('security.2fa_enabled', false)) {
            return $next($request);
        }

        $user = $request->user();

        // No user — pass through
        if (!$user) {
            return $next($request);
        }

        // If 2FA is forced for admins and user is admin, require 2FA even
        // if the user hasn't personally enabled it yet.
        $forceAdmins = (bool) setting('security.2fa_force_admins', false);
        $isAdmin = $user->hasRole(['super-admin', 'instance-admin']);

        $userHas2FA = $user->hasTwoFactorEnabled();

        // Skip if user doesn't have 2FA enabled AND admin forcing doesn't apply
        if (!$userHas2FA && !($forceAdmins && $isAdmin)) {
            return $next($request);
        }

        // Already verified in this session
        if (session('two_factor_verified') === true) {
            return $next($request);
        }

        // Don't redirect on excluded routes (prevent infinite loops)
        foreach (self::EXCLUDED_PREFIXES as $prefix) {
            if ($request->is($prefix, $prefix . '/*')) {
                return $next($request);
            }
        }

        return redirect()->route('two-factor.challenge');
    }
}

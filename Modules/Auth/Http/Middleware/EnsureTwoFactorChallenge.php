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
        $user = $request->user();

        // No user or 2FA not enabled — pass through
        if (!$user || !$user->hasTwoFactorEnabled()) {
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

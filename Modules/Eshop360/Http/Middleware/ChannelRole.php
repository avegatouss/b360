<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces a minimum channel role for the current user.
 *
 * Hierarchy: manager > operator > viewer
 *
 * Usage: ->middleware('eshop.channel.role:manager')
 */
final class ChannelRole
{
    private const HIERARCHY = [
        'viewer' => 0,
        'operator' => 1,
        'manager' => 2,
    ];

    public function handle(Request $request, Closure $next, string $requiredRole = 'viewer'): Response
    {
        $userRole = $request->channel_user_role ?? 'viewer';

        $requiredLevel = self::HIERARCHY[$requiredRole] ?? 0;
        $userLevel = self::HIERARCHY[$userRole] ?? 0;

        if ($userLevel < $requiredLevel) {
            abort(403, "This action requires the '{$requiredRole}' role.");
        }

        return $next($request);
    }
}

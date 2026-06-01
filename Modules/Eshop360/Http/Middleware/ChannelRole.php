<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces a minimum channel role for the current user.
 *
 * Hierarchy: admin > manager > operator > cashier > viewer > client
 *
 * Usage: ->middleware('eshop.channel.role:manager')
 */
final class ChannelRole
{
    private const LEGACY_ALIASES = [
        'member' => 'admin',
    ];

    public const HIERARCHY = [
        'client'   => 0,
        'viewer'   => 1,
        'cashier'  => 2,
        'operator' => 3,
        'manager'  => 4,
        'admin'    => 5,
    ];

    public const LABELS = [
        'admin'    => 'Administrateur canal',
        'manager'  => 'Manager canal',
        'operator' => 'Operateur',
        'cashier'  => 'Caissier(e)',
        'viewer'   => 'Observateur',
        'client'   => 'Client canal',
    ];

    public static function all(): array
    {
        return array_keys(self::HIERARCHY);
    }

    public static function label(string $role): string
    {
        $role = self::normalizeRole($role);

        return self::LABELS[$role] ?? ucfirst($role);
    }

    public function handle(Request $request, Closure $next, string $requiredRole = 'viewer'): Response
    {
        $userRole = self::normalizeRole((string) ($request->channel_user_role ?? 'viewer'));
        $requiredRole = self::normalizeRole($requiredRole);

        $requiredLevel = self::HIERARCHY[$requiredRole] ?? 0;
        $userLevel = self::HIERARCHY[$userRole] ?? 0;

        if ($userLevel < $requiredLevel) {
            abort(403, __('Cette action requiert le role :role.', ['role' => self::label($requiredRole)]));
        }

        return $next($request);
    }

    private static function normalizeRole(string $role): string
    {
        return self::LEGACY_ALIASES[$role] ?? $role;
    }
}

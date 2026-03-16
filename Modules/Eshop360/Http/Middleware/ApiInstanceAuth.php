<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Instances\Instance;
use Modules\Core\Support\CurrentInstance;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticate API requests scoped to the current instance.
 *
 * Accepts:
 *  - Laravel Sanctum Bearer token (if installed)
 *  - Session-based auth (for same-origin SPA calls)
 *
 * After authentication, verifies that the authenticated user
 * is a member of the instance resolved by InstanceMiddleware.
 */
final class ApiInstanceAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Attempt Sanctum token auth first, fall back to session
        $user = null;

        if (class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
            $user = auth('sanctum')->user();
        }

        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return response()->json([
                'error' => 'unauthenticated',
                'message' => 'A valid API token or session is required.',
            ], 401);
        }

        // Resolve instance: from route binding (web) or request body/header (API)
        $instance = CurrentInstance::get();

        if (!$instance) {
            $instanceId = $request->input('instance_id') ?? $request->header('X-Instance-Id');
            if ($instanceId) {
                $instance = Instance::find($instanceId);
                if ($instance) {
                    CurrentInstance::set($instance);
                }
            }
        }

        if (!$instance) {
            return response()->json([
                'error' => 'instance_not_found',
                'message' => 'No instance context resolved. Provide instance_id in request or X-Instance-Id header.',
            ], 404);
        }

        // Verify user belongs to this instance (via instance_user pivot on system connection)
        $isMember = \Illuminate\Support\Facades\DB::connection('system')
            ->table('instance_user')
            ->where('user_id', $user->id)
            ->where('instance_id', $instance->id)
            ->exists();

        // Super-admins (instance_id = 0 team) always pass
        $isSuperAdmin = $user->hasRole('super-admin', 0);

        if (!$isMember && !$isSuperAdmin) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'You do not have access to this instance.',
            ], 403);
        }

        return $next($request);
    }
}

<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;

final class EnsureInstanceMembershipActive
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('core.enforce_membership', true)) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user) {
            return $next($request); // auth middleware handles unauthenticated
        }

        // super-admin bypass via RBAC Gate::before, not here.

        $instance = CurrentInstance::get();
        if (!$instance) {
            abort(503, 'Instance context not resolved.');
        }

        $isActive = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if (!$isActive) {
            abort(403, 'User is not an active member of this instance.');
        }

        return $next($request);
    }
}

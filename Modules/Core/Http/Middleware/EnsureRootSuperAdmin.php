<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;

final class EnsureRootSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $instance = CurrentInstance::get();

        if (!$instance || !$instance->isRoot()) {
            abort(403, 'Accès réservé à l\'instance root.');
        }

        if (!$request->user() || !$request->user()->hasRole('super-admin')) {
            abort(403, 'Accès réservé aux super-administrateurs.');
        }

        return $next($request);
    }
}

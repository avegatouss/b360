<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;

final class EnsureRootSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $instance = CurrentInstance::get();

        if (!$instance || !$instance->isRoot()) {
            abort(403, 'Accès réservé à l\'instance root.');
        }

        if (!TeamContext::isSuperAdmin($request->user())) {
            abort(403, 'Accès réservé aux super-administrateurs.');
        }

        return $next($request);
    }
}

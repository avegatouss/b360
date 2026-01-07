<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;

final class SetSpatieTeamContextFromInstance
{
    public function handle(Request $request, Closure $next)
    {
        $instance = CurrentInstance::get();

        // Fail-closed: if no instance, set null team context.
        // Access remains denied unless Gate::before(super-admin) allows.
        TeamContext::set($instance?->id);

        return $next($request);
    }
}

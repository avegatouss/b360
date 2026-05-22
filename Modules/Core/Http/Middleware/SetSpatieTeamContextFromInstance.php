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

        // Si pas d'instance résolue → contexte global (0), pas null
        // (null viole la PK de model_has_roles)
        TeamContext::set($instance?->id ?? TeamContext::GLOBAL_TEAM_ID);

        return $next($request);
    }
}

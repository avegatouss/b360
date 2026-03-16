<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Modules\Core\Support\CurrentInstance;

class ApplyCurrentInstanceUrlDefaults
{
    public function handle(Request $request, Closure $next)
    {
        $instance = CurrentInstance::get();

        if ($instance?->slug) {
            URL::defaults(['slug' => $instance->slug]);
        }

        return $next($request);
    }
}

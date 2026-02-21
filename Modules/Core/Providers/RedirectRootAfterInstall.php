<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class RedirectRootAfterInstall
{
    public function handle(Request $request, Closure $next)
    {
        $installed = (bool) config('app.installed', false);

        if (!$installed) {
            return $next($request);
        }

        // Root is always public and always redirects to /login once installed
        if ($request->path() === '/') {
            return redirect('/login');
        }

        return $next($request);
    }
}

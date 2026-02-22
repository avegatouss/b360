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

        // La racine redirige vers /login une fois installé
        if ($request->path() === '/') {
            return redirect('/login');
        }

        return $next($request);
    }
}

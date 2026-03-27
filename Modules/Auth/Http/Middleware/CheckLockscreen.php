<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckLockscreen
{
    /**
     * Routes that should be accessible even when the screen is locked.
     */
    private const ALLOWED_ROUTES = [
        'lockscreen',
        'lockscreen.lock',
        'lockscreen.unlock',
        'logout',
        'instance.logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->session()->get('screen_locked')
            && $request->user()
            && ! in_array($request->route()?->getName(), self::ALLOWED_ROUTES, true)
        ) {
            return redirect()->route('lockscreen');
        }

        return $next($request);
    }
}

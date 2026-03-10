<?php

namespace Modules\Lang\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Lang\Services\LocaleManager;

final class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        try {
            app(LocaleManager::class)->apply();
        } catch (\Throwable) {
            // Settings table may not exist yet (e.g., during installation or tests)
        }

        return $next($request);
    }
}

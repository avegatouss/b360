<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Http\Middleware\EnsureInstanceResolved;
use Modules\Core\Http\Middleware\EnsureInstanceMembershipActive;
use Modules\Core\Http\Middleware\SetSpatieTeamContextFromInstance;

final class CoreHttpServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $router = $this->app['router'];

        // Aliases (usable in routes/middleware groups)
        $router->aliasMiddleware('core.instance.resolved', EnsureInstanceResolved::class);
        $router->aliasMiddleware('core.instance.member', EnsureInstanceMembershipActive::class);
        $router->aliasMiddleware('core.spatie.team', SetSpatieTeamContextFromInstance::class);
    }
}

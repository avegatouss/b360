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

        $router->aliasMiddleware('core.instance.resolved', EnsureInstanceResolved::class);
        $router->aliasMiddleware('core.instance.member', EnsureInstanceMembershipActive::class);
        $router->aliasMiddleware('core.spatie.team', SetSpatieTeamContextFromInstance::class);

        $router->aliasMiddleware('core.redirect.not_installed', RedirectIfNotInstalled::class);
        $router->aliasMiddleware('core.redirect.root_after_install', RedirectRootAfterInstall::class);
    }
}

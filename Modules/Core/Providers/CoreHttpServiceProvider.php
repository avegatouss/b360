<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Http\Middleware\BindInstanceFromRoute;
use Modules\Core\Http\Middleware\EnsureInstanceResolved;
use Modules\Core\Http\Middleware\EnsureInstanceMembershipActive;
use Modules\Core\Http\Middleware\SetSpatieTeamContextFromInstance;
use Modules\Core\Http\Middleware\EnsureRootSuperAdmin;
use Modules\Core\Http\Middleware\RedirectIfNotInstalled;
use Modules\Core\Http\Middleware\RedirectRootAfterInstall;

final class CoreHttpServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $router = $this->app['router'];

        // Résolution slug → Instance (doit précéder core.instance.resolved)
        $router->aliasMiddleware('core.instance.bind', BindInstanceFromRoute::class);

        $router->aliasMiddleware('core.instance.resolved', EnsureInstanceResolved::class);
        $router->aliasMiddleware('core.instance.member', EnsureInstanceMembershipActive::class);
        $router->aliasMiddleware('core.spatie.team', SetSpatieTeamContextFromInstance::class);

        $router->aliasMiddleware('core.redirect.not_installed', RedirectIfNotInstalled::class);
        $router->aliasMiddleware('core.redirect.root_after_install', RedirectRootAfterInstall::class);

        // Accès root + super-admin uniquement
        $router->aliasMiddleware('core.root.superadmin', EnsureRootSuperAdmin::class);
    }
}

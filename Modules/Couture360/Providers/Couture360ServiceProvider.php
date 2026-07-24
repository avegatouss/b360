<?php

declare(strict_types=1);

namespace Modules\Couture360\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\Couture360\Http\Middleware\CoutureApiAuth;

final class Couture360ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'couture360');
        $this->app->register(Couture360HooksProvider::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'couture360');

        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('couture.api.auth', CoutureApiAuth::class);
    }
}

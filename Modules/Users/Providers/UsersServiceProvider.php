<?php

namespace Modules\Users\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Users\Services\MembershipService;
use Modules\Users\Services\TeamRoleAssigner;

final class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MembershipService::class);
        $this->app->singleton(TeamRoleAssigner::class);

        $this->app->register(UsersAuthServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'users');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
        }
    }
}

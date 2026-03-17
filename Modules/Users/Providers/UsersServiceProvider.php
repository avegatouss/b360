<?php

namespace Modules\Users\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Support\CurrentInstance;
use Modules\Users\Services\MembershipService;
use Modules\Users\Services\TeamRoleAssigner;
use Modules\Users\Services\UserPreferenceService;

final class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MembershipService::class);
        $this->app->singleton(TeamRoleAssigner::class);
        $this->app->singleton(UserPreferenceService::class);

        $this->app->register(UsersAuthServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'users');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Share $instance with all users views automatically
        View::composer('users::*', function ($view) {
            if (!$view->offsetExists('instance')) {
                $view->with('instance', CurrentInstance::get());
            }
        });
    }
}

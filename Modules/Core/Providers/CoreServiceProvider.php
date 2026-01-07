<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Hooks\HookManager;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Modules\ModuleManager;

final class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/core.php', 'core');
        $this->mergeConfigFrom(__DIR__ . '/../config/hooks.php', 'hooks');

        $this->app->singleton(HookRegistry::class);
        $this->app->singleton(ModuleManager::class);
        $this->app->singleton(HookManager::class);

        // HTTP middleware aliases (router) + RBAC Gate::before
        $this->app->register(\Modules\Core\Providers\CoreHttpServiceProvider::class);
        $this->app->register(\Modules\Core\Providers\CoreAuthServiceProvider::class);

        // Artisan commands
        if ($this->app->runningInConsole()) {
            $this->app->register(\Modules\Core\Providers\CoreConsoleServiceProvider::class);
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Boot hooks once (providers listed in hooks config)
        $providers = (array) config('hooks.providers', []);
        app(HookManager::class)->boot($providers);
    }
}

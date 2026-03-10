<?php

namespace Modules\ModuleManager\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ModuleManager\Services\ModuleInstaller;

final class ModuleManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleInstaller::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'module-manager');
    }
}

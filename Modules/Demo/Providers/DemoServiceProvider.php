<?php

namespace Modules\Demo\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Demo\Services\DemoManager;

final class DemoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DemoManager::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'demo');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Demo\Console\DemoSeedCommand::class,
                \Modules\Demo\Console\DemoResetCommand::class,
            ]);
        }
    }
}

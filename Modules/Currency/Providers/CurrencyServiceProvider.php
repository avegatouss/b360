<?php

namespace Modules\Currency\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\Currency\Services\CurrencyManager;
use Modules\Currency\Services\ExchangeRateService;
use Modules\Currency\Services\SnapshotService;
use Modules\Currency\Services\TenantCurrencyManager;

class CurrencyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'currency');

        $this->app->singleton(CurrencyManager::class);
        $this->app->singleton(ExchangeRateService::class);
        $this->app->singleton(TenantCurrencyManager::class);
        $this->app->singleton(SnapshotService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'currency');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Currency\Console\Commands\UpdateExchangeRates::class,
            ]);
        }

        // Schedule daily exchange rate update
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('currency:update-rates')->dailyAt('06:00');
        });
    }
}

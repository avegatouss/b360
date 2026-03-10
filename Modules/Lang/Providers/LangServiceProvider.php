<?php

namespace Modules\Lang\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Lang\Services\LocaleManager;

class LangServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'lang');

        $this->app->singleton(LocaleManager::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'lang');

        // Load lang translation files
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'lang');
    }
}

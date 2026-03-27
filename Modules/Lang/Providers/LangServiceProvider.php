<?php

namespace Modules\Lang\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Lang\Services\LocaleManager;
use Modules\Lang\Services\TranslationRepository;

class LangServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'lang');

        $this->app->singleton(LocaleManager::class);
        $this->app->singleton(TranslationRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'lang');

        // Load lang translation files
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'lang');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Replace default translation loader with DB-enhanced loader
        $this->app->singleton('translation.loader', function ($app) {
            return new \Modules\Lang\Services\DatabaseTranslationLoader(
                $app['files'],
                $app->langPath()
            );
        });
    }
}

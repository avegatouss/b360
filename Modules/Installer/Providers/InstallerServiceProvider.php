<?php

namespace Modules\Installer\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\Installer\Http\Middleware\EnsureNotInstalled;

class InstallerServiceProvider extends ServiceProvider
{
    /**
     * Boot services.
     *
     * Le module Installer est chargé UNIQUEMENT
     * si l’application n’est pas encore installée.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Neutralisation définitive après installation
        |--------------------------------------------------------------------------
        */
        if (config('app.installed', false) === true) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Middleware Installer
        |--------------------------------------------------------------------------
        */
        /** @var Router $router */
        $router = $this->app['router'];

        $router->aliasMiddleware(
            'installer.not_installed',
            EnsureNotInstalled::class
        );

        /*
        |--------------------------------------------------------------------------
        | Routes Installer
        |--------------------------------------------------------------------------
        */
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        /*
        |--------------------------------------------------------------------------
        | Vues Installer
        |--------------------------------------------------------------------------
        */
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'installer'
        );

        /*
        |--------------------------------------------------------------------------
        | Configuration Installer
        |--------------------------------------------------------------------------
        */
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php',
            'installer'
        );
    }

    /**
     * Register services.
     *
     * Aucun binding global.
     */
    public function register(): void
    {
        //
    }
}

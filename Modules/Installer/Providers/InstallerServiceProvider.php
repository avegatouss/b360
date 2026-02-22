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
     */
    public function register(): void
    {
        // Force le driver de session en "file" tant que l'app n'est pas installée.
        //
        // Pourquoi : si SESSION_DRIVER=database est dans le .env (valeur par défaut
        // recommandée post-install) et que la base cible n'existe pas encore
        // (ou a été supprimée entre deux tentatives), le middleware StartSession
        // lève SQLSTATE[HY000][1049] Unknown database '...' avant même d'atteindre
        // les routes de l'installeur.
        //
        // register() s'exécute avant le binding du session store → c'est le seul
        // endroit garanti pour surcharger ce driver sans race condition.
        if (config('app.installed', false) !== true) {
            config([
                'session.driver' => 'file',
                'cache.default'  => 'file',
            ]);
        }
    }
}

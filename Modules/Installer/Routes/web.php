<?php

use Illuminate\Support\Facades\Route;
use Modules\Installer\Http\Controllers\InstallerController;


/*
|--------------------------------------------------------------------------
| Installer Routes
|--------------------------------------------------------------------------
|
| Ces routes sont accessibles UNIQUEMENT tant que l'application
| n'est pas installée (APP_INSTALLED=false).
| Toute tentative d'accès après installation sera bloquée.
|
*/

Route::middleware(['web', 'installer.not_installed', 'throttle:15,1'])
    ->prefix('install')
    ->name('installer.')
    ->group(function () {

        /*
        |----------------------------------------------------------------------
        | Écran principal de l’installateur
        |----------------------------------------------------------------------
        */
        Route::get('/', [InstallerController::class, 'index'])
            ->name('index');

        // Étape 1 Vérification prérequis
        Route::get('/requirements', [InstallerController::class, 'requirements'])->name('requirements');
        //  Étape 2 Test de connexion à la base de données

        Route::post('/database/test', [InstallerController::class, 'testDatabase'])->name('database.test');
        // Étape 3 Validation Configuration

        Route::post('/configuration/validate', [InstallerController::class, 'validateConfiguration'])
            ->name('configuration.validate');

        // Étape 4 Validation Super Admin
        Route::post('/admin/validate', [InstallerController::class, 'validateAdmin'])
            ->name('admin.validate');
        // État de l'installation (progression des tâches)
        Route::get('/state', [InstallerController::class, 'state'])->name('state');
        // Étape 5 (AJAX + progress)
        Route::post('/start', [InstallerController::class, 'startInstall'])
            ->name('start');

        Route::get('/stream', [InstallerController::class, 'streamInstall'])
            ->middleware('signed')
            ->name('stream');
            
    });

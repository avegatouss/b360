<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Dashboard Routes — Instance-scoped
|--------------------------------------------------------------------------
|
| /i/{slug}          → Accueil du dashboard de l'instance
| /i/{slug}/dashboard → Alias explicite (même vue)
|
*/
Route::middleware([
    'web',
    'core.redirect.not_installed',
    'core.instance.resolved',
    'core.spatie.team',
    'auth',
    'core.instance.member',
])->prefix('/i/{slug}')->group(function () {

    // Accueil instance = dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard.instance');

    // Alias explicite
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard.instance.explicit');
});

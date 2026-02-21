<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\LoginController;
use Modules\Auth\Http\Controllers\LogoutController;
use Modules\Auth\Http\Controllers\InstanceSelectionController;


/**
 * Public routes:
 * - non installé => rediriger vers /install (middleware)
 * - installed:
 *   - /login global (optional)
 *   - /i/{slug}/login canonical
 */
Route::middleware(['web', 'core.redirect.not_installed'])->group(function () {
    // Global login (optional entrypoint)
    Route::get('/login', [LoginController::class, 'showGlobal'])->name('login');
    Route::post('/login', [LoginController::class, 'loginGlobal'])
        ->middleware('throttle:login')
        ->name('login.post');

    // Instance-scoped login (canonical)
  /*  Route::get('/i/{slug}/login', [LoginController::class, 'showInstance'])
        ->name('instance.login');

    Route::post('/i/{slug}/login', [LoginController::class, 'loginInstance'])
        ->middleware(['throttle:login', 'core.instance.resolved', 'core.spatie.team'])
        ->name('instance.login.post');*/
});

// Authenticated routes
Route::middleware(['web', 'core.redirect.not_installed', 'auth'])->group(function () {
   /* Route::post('/i/{slug}/logout', LogoutController::class)
        ->middleware(['core.instance.resolved', 'core.spatie.team'])
        ->name('instance.logout');*/

    Route::get('/instances/select', [InstanceSelectionController::class, 'select'])
        ->name('instances.select');

    Route::post('/instances/select', [InstanceSelectionController::class, 'choose'])
        ->name('instances.choose');

    Route::get('/instances/no-active', [InstanceSelectionController::class, 'noActive'])
        ->name('instances.no_active');
});

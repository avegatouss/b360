<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\LoginController;
use Modules\Auth\Http\Controllers\LogoutController;
use Modules\Auth\Http\Controllers\InstanceSelectionController;
use Modules\Auth\Http\Controllers\ForgotPasswordController;
use Modules\Auth\Http\Controllers\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Auth Routes — B360
|--------------------------------------------------------------------------
|
| Routes publiques (avant auth) :
|   - Redirigées vers /install si non installé (core.redirect.not_installed)
|   - /login global + /i/{slug}/login instance
|   - /forgot-password + /reset-password/{token}
|
| Routes authentifiées :
|   - /i/{slug}/logout
|   - /instances/select (sélection d'instance multi)
|
*/

Route::middleware(['web', 'core.redirect.not_installed'])->group(function () {

    /*
    |----------------------------------------------------------------------
    | Login global (point d'entrée unique)
    |----------------------------------------------------------------------
    */
    Route::get('/login', [LoginController::class, 'showGlobal'])->name('login');
    Route::post('/login', [LoginController::class, 'loginGlobal'])
        ->middleware('throttle:login')
        ->name('login.post');

    /*
    |----------------------------------------------------------------------
    | Login scopé instance (canonique — résolution par path)
    |----------------------------------------------------------------------
    */
    Route::get('/i/{slug}/login', [LoginController::class, 'showInstance'])
        ->name('instance.login');

    Route::post('/i/{slug}/login', [LoginController::class, 'loginInstance'])
        ->middleware('throttle:login')
        ->name('instance.login.post');

    /*
    |----------------------------------------------------------------------
    | Mot de passe oublié
    |----------------------------------------------------------------------
    */
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])
        ->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])
        ->name('password.reset');

    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Routes authentifiées
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'core.redirect.not_installed', 'auth'])->group(function () {

    /*
    |----------------------------------------------------------------------
    | Logout scopé instance
    |----------------------------------------------------------------------
    */
    Route::match(['get', 'post'], '/i/{slug}/logout', LogoutController::class)
        ->name('instance.logout');

    /*
    |----------------------------------------------------------------------
    | Logout global (fallback)
    |----------------------------------------------------------------------
    */
    Route::match(['get', 'post'], '/logout', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    /*
    |----------------------------------------------------------------------
    | Sélection d'instance (mode multi avec plusieurs instances actives)
    |----------------------------------------------------------------------
    */
    Route::get('/instances/select', [InstanceSelectionController::class, 'select'])
        ->name('instances.select');

    Route::post('/instances/select', [InstanceSelectionController::class, 'choose'])
        ->name('instances.choose');

    Route::get('/instances/no-active', [InstanceSelectionController::class, 'noActive'])
        ->name('instances.no_active');
});

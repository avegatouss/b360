<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\LoginController;
use Modules\Auth\Http\Controllers\LogoutController;
use Modules\Auth\Http\Controllers\InstanceSelectionController;
use Modules\Auth\Http\Controllers\ForgotPasswordController;
use Modules\Auth\Http\Controllers\LockscreenController;
use Modules\Auth\Http\Controllers\ResetPasswordController;
use Modules\Auth\Http\Controllers\TwoFactorController;
use Modules\Auth\Http\Controllers\IpRuleController;
use Modules\Auth\Http\Controllers\LoginLogController;

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
    | Lockscreen
    |----------------------------------------------------------------------
    */
    Route::post('/lockscreen/lock', [LockscreenController::class, 'lock'])->name('lockscreen.lock');
    Route::get('/lockscreen', [LockscreenController::class, 'show'])->name('lockscreen');
    Route::post('/lockscreen/unlock', [LockscreenController::class, 'unlock'])->name('lockscreen.unlock');

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

/*
|--------------------------------------------------------------------------
| Two-Factor Authentication Routes
|--------------------------------------------------------------------------
*/

// 2FA setup (requires auth)
Route::middleware(['web', 'core.redirect.not_installed', 'auth'])->prefix('two-factor')->group(function () {
    Route::get('/enable', [TwoFactorController::class, 'showEnable'])->name('two-factor.enable');
    Route::post('/enable', [TwoFactorController::class, 'enable']);
    Route::post('/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
});

// 2FA challenge (after login, before full access)
Route::middleware(['web', 'core.redirect.not_installed', 'auth'])->prefix('two-factor')->group(function () {
    Route::get('/challenge', [TwoFactorController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('/challenge', [TwoFactorController::class, 'verifyChallenge']);
    Route::get('/recovery', [TwoFactorController::class, 'showRecovery'])->name('two-factor.recovery');
    Route::post('/recovery', [TwoFactorController::class, 'verifyRecovery']);
});

/*
|--------------------------------------------------------------------------
| IP Rules Management (instance-scoped, admin only)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'core.redirect.not_installed', 'auth'])
    ->prefix('/i/{slug}/admin/ip-rules')
    ->group(function () {
        Route::get('/', [IpRuleController::class, 'index'])->name('ip-rules.index');
        Route::post('/', [IpRuleController::class, 'store'])->name('ip-rules.store');
        Route::delete('/{ipRule}', [IpRuleController::class, 'destroy'])->name('ip-rules.destroy');
    });

/*
|--------------------------------------------------------------------------
| Login History (instance-scoped)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'core.redirect.not_installed', 'auth'])->group(function () {
    Route::get('/i/{slug}/admin/login-logs', [LoginLogController::class, 'index'])->name('login-logs.index');
    Route::get('/i/{slug}/profile/login-history', [LoginLogController::class, 'userHistory'])->name('login-logs.user');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\UserController;
use Modules\Users\Http\Controllers\UserMembershipController;

/*
|--------------------------------------------------------------------------
| Users Routes — Instance-scoped
|--------------------------------------------------------------------------
|
| Toutes les routes utilisateurs sont sous /i/{slug}/ pour permettre
| la résolution d'instance via le path (mode de résolution par défaut).
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

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('can:users.view')
        ->name('users.index');

    Route::get('/users/create', [UserController::class, 'create'])
        ->middleware('can:users.manage')
        ->name('users.create');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('can:users.manage')
        ->name('users.store');

    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('can:users.manage')
        ->name('users.edit');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('can:users.manage')
        ->name('users.update');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('can:users.manage')
        ->name('users.destroy');

    Route::put('/users/{user}/memberships', [UserMembershipController::class, 'sync'])
        ->middleware('can:users.manage')
        ->name('users.memberships.sync');
});

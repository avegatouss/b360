<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\RoleController;
use Modules\Users\Http\Controllers\UserController;
use Modules\Users\Http\Controllers\UserMembershipController;
use Modules\Users\Http\Controllers\UserPreferenceController;

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
    'core.instance.bind',      // résout slug → Instance (CurrentInstance::set)
    'core.instance.resolved',  // valide que l'instance est bien liée
    'core.spatie.team',        // setPermissionsTeamId(instance->id)
    'auth',
    'core.instance.member',    // vérifie membership actif
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

    // ─── Roles & Permissions ─────────────────────────────────
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('can:users.manage')
        ->name('roles.index');

    Route::get('/roles/create', [RoleController::class, 'create'])
        ->middleware('can:users.manage')
        ->name('roles.create');

    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('can:users.manage')
        ->name('roles.store');

    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('can:users.manage')
        ->name('roles.edit');

    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('can:users.manage')
        ->name('roles.update');

    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('can:users.manage')
        ->name('roles.destroy');

    // ─── User Preferences ──────────────────────────────────
    Route::get('/profile/preferences', [UserPreferenceController::class, 'edit'])
        ->name('users.preferences.edit');

    Route::put('/profile/preferences', [UserPreferenceController::class, 'update'])
        ->name('users.preferences.update');
});

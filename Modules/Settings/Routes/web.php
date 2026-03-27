<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;

Route::middleware([
    'web',
    'core.redirect.not_installed',
    'core.instance.bind',
    'core.instance.resolved',
    'core.spatie.team',
    'auth',
    'core.instance.member',
    'core.root.superadmin',
])->prefix('/i/{slug}')->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Specific action routes MUST come before the {group} wildcard
    Route::post('/settings/email/test', [SettingsController::class, 'testEmail'])->name('settings.test_email');

    Route::get('/settings/{group}', [SettingsController::class, 'group'])->name('settings.group');
    Route::put('/settings/{group}', [SettingsController::class, 'updateGroup'])->name('settings.group.update');
});

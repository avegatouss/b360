<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\LicenseApiController;

Route::prefix('api/license')->middleware(['throttle:30,1'])->group(function () {
    Route::post('/verify', [LicenseApiController::class, 'verify'])->name('api.license.verify');
    Route::get('/status/{instanceId}', [LicenseApiController::class, 'status'])
        ->middleware('auth')
        ->name('api.license.status');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Couture360\Http\Controllers\Api\AuthController;

$prefix = config('couture360.api.prefix', 'api/couture');
$throttle = 'throttle:'.config('couture360.api.throttle', '120,1');

// Public — health check (no auth). Auth routes are added in Task 3.
Route::middleware(['api', $throttle])->prefix($prefix)->name('api.couture.')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok', 'module' => 'couture360']))
        ->name('health');
});

// Public login (no token yet).
Route::middleware(['api', $throttle])->prefix($prefix)->name('api.couture.')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
});

// Authenticated device endpoints.
Route::middleware(['api', 'couture.api.auth', $throttle])->prefix($prefix)->name('api.couture.')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

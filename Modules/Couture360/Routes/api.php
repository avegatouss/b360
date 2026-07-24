<?php

use Illuminate\Support\Facades\Route;

$prefix = config('couture360.api.prefix', 'api/couture');
$throttle = 'throttle:'.config('couture360.api.throttle', '120,1');

// Public — health check (no auth). Auth routes are added in Task 3.
Route::middleware(['api', $throttle])->prefix($prefix)->name('api.couture.')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok', 'module' => 'couture360']))
        ->name('health');
});

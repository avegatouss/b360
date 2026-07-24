<?php

use Illuminate\Support\Facades\Route;

// Lot 10+: back-office web (dashboard, configuration, Kanban, conflict resolution).
// Registered via HookRegistry menu entries. Empty in Lot 0.
Route::middleware(['web'])->prefix('couture')->name('couture.')->group(function () {
    //
});

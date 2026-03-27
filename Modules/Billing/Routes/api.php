<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Billing API Routes
|--------------------------------------------------------------------------
| Webhook endpoints for payment gateways.
| These routes are public (no auth) since they receive callbacks from gateways.
*/

Route::middleware(['api'])->prefix('api/billing')->name('api.billing.')->group(function () {
    Route::post('/webhooks/{gateway}', [WebhookController::class, 'handle'])
        ->name('webhooks.handle');
});

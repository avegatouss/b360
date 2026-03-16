<?php

use Illuminate\Support\Facades\Route;
use Modules\Eshop360\Http\Controllers\Api\ApiController;

/*
|--------------------------------------------------------------------------
| Eshop360 API Routes
|--------------------------------------------------------------------------
|
| API v1 endpoints for programmatic access to Eshop360 data.
| All routes are prefixed with /api/eshop360/v1/
|
*/

Route::middleware(['api', 'eshop360.api.auth'])->prefix('api/eshop360/v1')->name('api.eshop360.')->group(function () {

    // ─── Products ────────────────────────────────────
    Route::get('/products', [ApiController::class, 'products'])->name('products.index');
    Route::get('/products/{id}', [ApiController::class, 'productShow'])->name('products.show');
    Route::post('/products', [ApiController::class, 'productStore'])->name('products.store');
    Route::put('/products/{id}', [ApiController::class, 'productUpdate'])->name('products.update');
    Route::delete('/products/{id}', [ApiController::class, 'productDestroy'])->name('products.destroy');

    // ─── Stock ───────────────────────────────────────
    Route::get('/stock', [ApiController::class, 'stock'])->name('stock.index');
    Route::post('/stock/movement', [ApiController::class, 'stockMovement'])->name('stock.movement');

    // ─── Clients ─────────────────────────────────────
    Route::get('/clients', [ApiController::class, 'clients'])->name('clients.index');
    Route::get('/clients/{id}', [ApiController::class, 'clientShow'])->name('clients.show');
    Route::post('/clients', [ApiController::class, 'clientStore'])->name('clients.store');

    // ─── Sales ───────────────────────────────────────
    Route::get('/sales', [ApiController::class, 'sales'])->name('sales.index');
    Route::get('/sales/{id}', [ApiController::class, 'saleShow'])->name('sales.show');
    Route::post('/sales', [ApiController::class, 'saleStore'])->name('sales.store');

    // ─── Purchases ───────────────────────────────────
    Route::get('/purchases', [ApiController::class, 'purchases'])->name('purchases.index');

    // ─── Reports ─────────────────────────────────────
    Route::get('/reports/overview', [ApiController::class, 'reportOverview'])->name('reports.overview');
    Route::get('/reports/profit-loss', [ApiController::class, 'reportProfitLoss'])->name('reports.profit-loss');
    Route::get('/reports/stock', [ApiController::class, 'reportStock'])->name('reports.stock');

    // ─── Online Orders ──────────────────────────────
    Route::post('/orders', [ApiController::class, 'onlineOrderStore'])->name('orders.store');
    Route::get('/orders', [ApiController::class, 'onlineOrders'])->name('orders.index');
    Route::get('/orders/{id}', [ApiController::class, 'onlineOrderShow'])->name('orders.show');
    Route::put('/orders/{id}/status', [ApiController::class, 'onlineOrderUpdateStatus'])->name('orders.status');

    // ─── v2 endpoints ────────────────────────────────
    // Accessible via /api/eshop360/v1/ (will create v2 prefix later)
    Route::get('/dashboard', [ApiController::class, 'dashboard'])->name('dashboard');
    Route::get('/channels/{channelId}/margins', [ApiController::class, 'channelMargins'])->name('channels.margins');
    Route::get('/charges/realtime', [ApiController::class, 'chargesRealtime'])->name('charges.realtime');
});

// CinetPay webhook callback — no instance-auth, signature verified internally
Route::middleware(['api'])->prefix('api/eshop360')->name('api.eshop360.')->group(function () {
    Route::post('/cinetpay/callback', [\Modules\Eshop360\Http\Controllers\Payment\CinetPayController::class, 'callback'])
        ->name('cinetpay.callback');

    // Legacy alias kept for compatibility
    Route::post('/inetpay/callback', [\Modules\Eshop360\Http\Controllers\Payment\InetPayController::class, 'callback'])
        ->name('inetpay.callback');
});

<?php

use Illuminate\Support\Facades\Route;

// Import ALL controllers
use Modules\Eshop360\Http\Controllers\Catalog\ProductController;
use Modules\Eshop360\Http\Controllers\Catalog\CategoryController;
use Modules\Eshop360\Http\Controllers\Catalog\BrandController;
use Modules\Eshop360\Http\Controllers\Catalog\BarcodeController;
use Modules\Eshop360\Http\Controllers\Inventory\StockController;
use Modules\Eshop360\Http\Controllers\Inventory\StockAdjustmentController;
use Modules\Eshop360\Http\Controllers\Inventory\StockTransferController;
use Modules\Eshop360\Http\Controllers\Inventory\WarehouseController;
use Modules\Eshop360\Http\Controllers\Pos\PosController;
use Modules\Eshop360\Http\Controllers\Sales\SaleController;
use Modules\Eshop360\Http\Controllers\Sales\OrderController;
use Modules\Eshop360\Http\Controllers\Sales\CartController;
use Modules\Eshop360\Http\Controllers\Sales\CheckoutController;
use Modules\Eshop360\Http\Controllers\Customer\CustomerController;
use Modules\Eshop360\Http\Controllers\Purchase\PurchaseController;
use Modules\Eshop360\Http\Controllers\Purchase\PurchaseReturnController;
use Modules\Eshop360\Http\Controllers\Invoice\InvoiceController;
use Modules\Eshop360\Http\Controllers\Promotion\CouponController;
use Modules\Eshop360\Http\Controllers\Promotion\DiscountController;
use Modules\Eshop360\Http\Controllers\Promotion\QuotationController;
use Modules\Eshop360\Http\Controllers\Report\ReportController;
use Modules\Eshop360\Http\Controllers\Report\AdvancedReportController;
use Modules\Eshop360\Http\Controllers\Settings\EshopSettingsController;
use Modules\Eshop360\Http\Controllers\Supplier\SupplierController;
use Modules\Eshop360\Http\Controllers\Import\ImportController;
use Modules\Eshop360\Http\Controllers\Finance\AccountController;
use Modules\Eshop360\Http\Controllers\Finance\ExpenseController;
use Modules\Eshop360\Http\Controllers\Finance\IncomeController;
use Modules\Eshop360\Http\Controllers\Finance\LoanController;
use Modules\Eshop360\Http\Controllers\Finance\GiftCardController;
use Modules\Eshop360\Http\Controllers\Finance\InstallmentController;
use Modules\Eshop360\Http\Controllers\HR\EmployeeController;
use Modules\Eshop360\Http\Controllers\HR\SalaryController;
use Modules\Eshop360\Http\Controllers\HR\AttendanceController;
use Modules\Eshop360\Http\Controllers\Charges\ChargesController;
use Modules\Eshop360\Http\Controllers\Channel\ChannelController;
use Modules\Eshop360\Http\Controllers\OnlineOrder\OnlineOrderController;
use Modules\Eshop360\Http\Controllers\Communication\MessageController;
use Modules\Eshop360\Http\Controllers\Communication\BulkMessageController;
use Modules\Eshop360\Http\Controllers\Communication\SmsGatewayController;
use Modules\Eshop360\Http\Controllers\Communication\SupportTicketController;
use Modules\Eshop360\Http\Controllers\Communication\EmailTemplateController;
use Modules\Eshop360\Http\Controllers\Payment\CinetPayController;
use Modules\Eshop360\Http\Controllers\Payment\InetPayController;
use Modules\Eshop360\Http\Controllers\Project\ProjectController;
use Modules\Eshop360\Http\Controllers\Project\TaskController;
use Modules\Eshop360\Http\Controllers\Project\EventController;
use Modules\Eshop360\Http\Controllers\Invoice\RecurringInvoiceController;
use Modules\Eshop360\Http\Controllers\Portal\CustomerPortalController;
use Modules\Eshop360\Http\Controllers\ChannelPortal\ChannelPortalDashboardController;
use Modules\Eshop360\Http\Controllers\ChannelPortal\ChannelPortalOrderController;
use Modules\Eshop360\Http\Controllers\ChannelPortal\ChannelPortalStockController;
use Modules\Eshop360\Http\Controllers\ChannelPortal\ChannelPortalSaleController;
use Modules\Eshop360\Http\Controllers\ChannelPortal\ChannelPortalCustomerController;
use Modules\Eshop360\Http\Controllers\ChannelPortal\ChannelPortalMarginController;
use Modules\Eshop360\Http\Controllers\ChannelPortal\ChannelShopController;
use Modules\Eshop360\Http\Controllers\Notification\NotificationController;
use Modules\Eshop360\Http\Controllers\Printing\ReceiptTemplateController;
use Modules\Eshop360\Http\Controllers\Printing\PrinterController;
use Modules\Eshop360\Http\Middleware\ApplyCurrentInstanceUrlDefaults;

/*
|--------------------------------------------------------------------------
| Eshop360 Routes — Instance-scoped
|--------------------------------------------------------------------------
|
| Toutes les routes Eshop360 sont sous /i/{slug}/ pour permettre
| la résolution d'instance via le path (mode de résolution par défaut).
|
*/

Route::middleware([
    'web',
    'core.redirect.not_installed',
    'core.instance.bind',
    'core.instance.resolved',
    'core.spatie.team',
    'auth',
    'core.instance.member',
    ApplyCurrentInstanceUrlDefaults::class,
])->prefix('/i/{slug}')->group(function () {

    // ─── POS Terminal ──────────────────────────────
    Route::prefix('pos')->name('eshop360.pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->middleware('can:eshop.pos.access')->name('index');
        Route::get('/layout-2', [PosController::class, 'layout2'])->middleware('can:eshop.pos.access')->name('layout2');
        Route::get('/layout-3', [PosController::class, 'layout3'])->middleware('can:eshop.pos.access')->name('layout3');
        Route::get('/layout-4', [PosController::class, 'layout4'])->middleware('can:eshop.pos.access')->name('layout4');
        Route::get('/layout-5', [PosController::class, 'layout5'])->middleware('can:eshop.pos.access')->name('layout5');
        Route::get('/orders', [PosController::class, 'orders'])->middleware('can:eshop.pos.access')->name('orders');
        Route::post('/registers', [PosController::class, 'openRegister'])->middleware('can:eshop.pos.access')->name('registers.open');
        Route::post('/registers/{register}/close', [PosController::class, 'closeRegister'])->middleware('can:eshop.pos.access')->name('registers.close');
        Route::post('/holdings', [PosController::class, 'storeHolding'])->middleware('can:eshop.pos.access')->name('holdings.store');
        Route::post('/holdings/{holding}/resume', [PosController::class, 'resumeHolding'])->middleware('can:eshop.pos.access')->name('holdings.resume');
        Route::get('/settings', [PosController::class, 'settings'])->middleware('can:eshop.settings.manage')->name('settings');
        Route::put('/settings', [PosController::class, 'updateSettings'])->middleware('can:eshop.settings.manage')->name('settings.update');
    });

    // ─── Products ─────────────────────────────────
    Route::prefix('products')->name('eshop360.products.')->middleware('can:eshop.products.view')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/list', [ProductController::class, 'index'])->name('list');
        Route::get('/search', [ProductController::class, 'search'])->name('search');
        Route::get('/create', [ProductController::class, 'create'])->middleware('can:eshop.products.manage')->name('create');
        Route::post('/', [ProductController::class, 'store'])->middleware('can:eshop.products.manage')->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->middleware('can:eshop.products.manage')->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->middleware('can:eshop.products.manage')->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->middleware('can:eshop.products.manage')->name('destroy');

        // Product Variations
        Route::get('/{product}/variations', [ProductController::class, 'variations'])->middleware('can:eshop.products.manage')->name('variations');
        Route::post('/{product}/variations', [ProductController::class, 'storeVariation'])->middleware('can:eshop.products.manage')->name('variations.store');
        Route::put('/{product}/variations/{variation}', [ProductController::class, 'updateVariation'])->middleware('can:eshop.products.manage')->name('variations.update');
        Route::delete('/{product}/variations/{variation}', [ProductController::class, 'destroyVariation'])->middleware('can:eshop.products.manage')->name('variations.destroy');
    });

    // ─── Categories ──────────────────────────────
    Route::prefix('categories')->name('eshop360.categories.')->middleware('can:eshop.products.view')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/subcategories', [CategoryController::class, 'subcategories'])->name('subcategories');
        Route::post('/', [CategoryController::class, 'store'])->middleware('can:eshop.products.manage')->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->middleware('can:eshop.products.manage')->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->middleware('can:eshop.products.manage')->name('destroy');
    });

    // ─── Brands ──────────────────────────────────
    Route::prefix('brands')->name('eshop360.brands.')->middleware('can:eshop.products.view')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('index');
        Route::post('/', [BrandController::class, 'store'])->middleware('can:eshop.products.manage')->name('store');
        Route::put('/{brand}', [BrandController::class, 'update'])->middleware('can:eshop.products.manage')->name('update');
        Route::delete('/{brand}', [BrandController::class, 'destroy'])->middleware('can:eshop.products.manage')->name('destroy');
    });

    // ─── Barcodes ─────────────────────────────────
    Route::prefix('barcodes')->name('eshop360.barcodes.')->middleware('can:eshop.products.view')->group(function () {
        Route::get('/', [BarcodeController::class, 'index'])->name('index');
        Route::get('/qrcode', [BarcodeController::class, 'qrcode'])->name('qrcode');
        Route::post('/generate', [BarcodeController::class, 'generate'])->name('generate');
        Route::post('/print-batch', [BarcodeController::class, 'printBatch'])->name('print-batch');
    });

    // ─── Inventory / Stocks ──────────────────────
    Route::prefix('stocks')->name('eshop360.stocks.')->middleware('can:eshop.inventory.view')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::post('/', [StockController::class, 'store'])->middleware('can:eshop.inventory.manage')->name('store');
        Route::put('/{stock}', [StockController::class, 'update'])->middleware('can:eshop.inventory.manage')->name('update');
        Route::delete('/{stock}', [StockController::class, 'destroy'])->middleware('can:eshop.inventory.manage')->name('destroy');
        Route::get('/low', [StockController::class, 'lowStock'])->name('low');
        Route::get('/expired', [StockController::class, 'expired'])->name('expired');
        Route::get('/expiry-report', [StockController::class, 'expiryReport'])->name('expiry-report');
        Route::get('/quantity-alert', [StockController::class, 'quantityAlert'])->name('quantity-alert');
    });

    // ─── Stock Adjustments ────────────────────────
    Route::prefix('stock-adjustments')->name('eshop360.stock-adjustments.')->middleware('can:eshop.inventory.manage')->group(function () {
        Route::get('/', [StockAdjustmentController::class, 'index'])->name('index');
        Route::post('/', [StockAdjustmentController::class, 'store'])->name('store');
        Route::put('/{movement}', [StockAdjustmentController::class, 'update'])->name('update');
        Route::delete('/{movement}', [StockAdjustmentController::class, 'destroy'])->name('destroy');
    });

    // ─── Stock Transfers ──────────────────────────
    Route::prefix('stock-transfers')->name('eshop360.stock-transfers.')->middleware('can:eshop.inventory.manage')->group(function () {
        Route::get('/', [StockTransferController::class, 'index'])->name('index');
        Route::post('/', [StockTransferController::class, 'store'])->name('store');
        Route::get('/{transfer}', [StockTransferController::class, 'show'])->name('show');
        Route::put('/{transfer}', [StockTransferController::class, 'update'])->name('update');
        Route::post('/{transfer}/complete', [StockTransferController::class, 'complete'])->name('complete');
        Route::post('/{transfer}/cancel', [StockTransferController::class, 'cancel'])->name('cancel');
    });

    // ─── Warehouses ──────────────────────────────
    Route::prefix('warehouses')->name('eshop360.warehouses.')->middleware('can:eshop.inventory.manage')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::post('/', [WarehouseController::class, 'store'])->name('store');
        Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
    });

    // ─── Stores (quick-create) ───────────────────
    Route::post('stores', [WarehouseController::class, 'storeStore'])->middleware('can:eshop.inventory.manage')->name('eshop360.stores.store');

    // ─── Sales ───────────────────────────────────
    Route::prefix('sales')->name('eshop360.sales.')->middleware('can:eshop.sales.view')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/dashboard', [SaleController::class, 'dashboard'])->name('dashboard');
        Route::get('/create', [SaleController::class, 'create'])->middleware('can:eshop.sales.manage')->name('create');
        Route::post('/', [SaleController::class, 'store'])->middleware('can:eshop.sales.manage')->name('store');

        // Static routes BEFORE wildcard /{order}
        Route::get('/returns/list', [SaleController::class, 'returns'])->name('returns');
        Route::post('/returns', [SaleController::class, 'storeReturn'])->middleware('can:eshop.sales.manage')->name('returns.store');
        Route::get('/tax/report', [SaleController::class, 'taxReport'])->name('tax-report');

        Route::get('/{order}', [SaleController::class, 'show'])->name('show');
        Route::put('/{order}', [SaleController::class, 'update'])->middleware('can:eshop.sales.manage')->name('update');
        Route::delete('/{order}', [SaleController::class, 'destroy'])->middleware('can:eshop.sales.manage')->name('destroy');
    });

    // ─── Orders ──────────────────────────────────
    Route::prefix('orders')->name('eshop360.orders.')->middleware('can:eshop.sales.view')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/online', [OrderController::class, 'online'])->name('online');
        Route::post('/', [OrderController::class, 'store'])->middleware('can:eshop.sales.manage')->name('store');
        Route::get('/{order}/receipt', [OrderController::class, 'receipt'])->name('receipt');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::put('/{order}', [OrderController::class, 'update'])->middleware('can:eshop.sales.manage')->name('update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->middleware('can:eshop.sales.manage')->name('destroy');
    });

    // ─── Cart & Checkout ─────────────────────────
    Route::prefix('cart')->name('eshop360.cart.')->middleware('can:eshop.pos.access')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::put('/{itemKey}', [CartController::class, 'update'])->name('update');
        Route::delete('/{itemKey}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/', [CartController::class, 'clear'])->name('clear');
        Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('coupon');
    });

    Route::prefix('checkout')->name('eshop360.checkout.')->middleware('can:eshop.pos.access')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/', [CheckoutController::class, 'process'])->name('process');
    });

    // ─── Customer Portal ───────────────────────────
    Route::prefix('portal')->name('eshop360.portal.')->group(function () {
        Route::get('/', [CustomerPortalController::class, 'catalog'])->name('catalog');
        Route::get('/cart', [CustomerPortalController::class, 'cart'])->name('cart');
        Route::post('/cart', [CustomerPortalController::class, 'addToCart'])->name('cart.add');
        Route::put('/cart/{itemKey}', [CustomerPortalController::class, 'updateCart'])->name('cart.update');
        Route::delete('/cart/{itemKey}', [CustomerPortalController::class, 'removeFromCart'])->name('cart.remove');
        Route::delete('/cart', [CustomerPortalController::class, 'clearCart'])->name('cart.clear');
        Route::post('/checkout', [CustomerPortalController::class, 'checkout'])->name('checkout');
        Route::get('/orders', [CustomerPortalController::class, 'orders'])->name('orders.index');
        Route::get('/orders/{onlineOrder}', [CustomerPortalController::class, 'showOrder'])->name('orders.show');
        Route::match(['put', 'patch'], '/orders/{onlineOrder}/received', [CustomerPortalController::class, 'confirmReceived'])->name('orders.received');
        Route::match(['put', 'patch'], '/orders/{onlineOrder}/cancel', [CustomerPortalController::class, 'cancelOrder'])->name('orders.cancel');
    });

    // ─── Customers ────────────────────────────────
    Route::prefix('customers')->name('eshop360.customers.')->middleware('can:eshop.customers.view')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::post('/', [CustomerController::class, 'store'])->middleware('can:eshop.customers.manage')->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::put('/{customer}', [CustomerController::class, 'update'])->middleware('can:eshop.customers.manage')->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->middleware('can:eshop.customers.manage')->name('destroy');
        Route::post('/{customer}/wallet-topup', [CustomerController::class, 'walletTopup'])->middleware('can:eshop.customers.manage')->name('wallet-topup');
        Route::get('/reports/summary', [CustomerController::class, 'report'])->name('report');
        Route::get('/reports/due', [CustomerController::class, 'dueReport'])->name('due-report');
    });

    // ─── Purchases ────────────────────────────────
    Route::prefix('purchases')->name('eshop360.purchases.')->middleware('can:eshop.purchases.view')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseController::class, 'create'])->middleware('can:eshop.purchases.manage')->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->middleware('can:eshop.purchases.manage')->name('store');

        // Static routes BEFORE wildcard /{purchase}
        Route::get('/reports/summary', [PurchaseController::class, 'report'])->name('report');
        Route::get('/reports/transactions', [PurchaseController::class, 'transactions'])->name('transactions');

        Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('show');
        Route::put('/{purchase}', [PurchaseController::class, 'update'])->middleware('can:eshop.purchases.manage')->name('update');
        Route::delete('/{purchase}', [PurchaseController::class, 'destroy'])->middleware('can:eshop.purchases.manage')->name('destroy');
        Route::get('/{purchase}/receive', [PurchaseController::class, 'receiveForm'])->middleware('can:eshop.purchases.manage')->name('receive.form');
        Route::post('/{purchase}/receive', [PurchaseController::class, 'receive'])->middleware('can:eshop.purchases.manage')->name('receive');
    });

    Route::prefix('purchase-returns')->name('eshop360.purchase-returns.')->middleware('can:eshop.purchases.manage')->group(function () {
        Route::get('/', [PurchaseReturnController::class, 'index'])->name('index');
        Route::post('/', [PurchaseReturnController::class, 'store'])->name('store');
        Route::put('/{purchaseReturn}', [PurchaseReturnController::class, 'update'])->name('update');
        Route::delete('/{purchaseReturn}', [PurchaseReturnController::class, 'destroy'])->name('destroy');
    });

    // ─── Invoices ──────────────────────────────────
    Route::prefix('invoices')->name('eshop360.invoices.')->middleware('can:eshop.invoices.view')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->middleware('can:eshop.invoices.manage')->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->middleware('can:eshop.invoices.manage')->name('store');

        // Config/settings routes MUST come before /{invoice} to avoid route parameter conflicts
        Route::get('/config/templates', [InvoiceController::class, 'templates'])->middleware('can:eshop.settings.manage')->name('templates');
        Route::get('/config/settings', [InvoiceController::class, 'settings'])->middleware('can:eshop.settings.manage')->name('settings');
        Route::put('/config/settings', [InvoiceController::class, 'updateSettings'])->middleware('can:eshop.settings.manage')->name('settings.update');
        Route::get('/reports/summary', [InvoiceController::class, 'report'])->name('report');

        // ─── Recurring Invoices (MUST be before /{invoice} to avoid route collision) ───
        Route::prefix('recurring')->name('recurring.')->middleware('can:eshop.invoices.manage')->group(function () {
            Route::get('/', [RecurringInvoiceController::class, 'index'])->name('index');
            Route::get('/create', [RecurringInvoiceController::class, 'create'])->name('create');
            Route::post('/', [RecurringInvoiceController::class, 'store'])->name('store');
            Route::get('/{recurringInvoice}/edit', [RecurringInvoiceController::class, 'edit'])->name('edit');
            Route::put('/{recurringInvoice}', [RecurringInvoiceController::class, 'update'])->name('update');
            Route::delete('/{recurringInvoice}', [RecurringInvoiceController::class, 'destroy'])->name('destroy');
            Route::patch('/{recurringInvoice}/toggle', [RecurringInvoiceController::class, 'toggle'])->name('toggle');
        });

        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->middleware('can:eshop.invoices.manage')->name('update');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->middleware('can:eshop.invoices.manage')->name('destroy');
    });

    // ─── Promotions ────────────────────────────────
    Route::prefix('coupons')->name('eshop360.coupons.')->middleware('can:eshop.promotions.manage')->group(function () {
        Route::get('/', [CouponController::class, 'index'])->name('index');
        Route::post('/', [CouponController::class, 'store'])->name('store');
        Route::put('/{coupon}', [CouponController::class, 'update'])->name('update');
        Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy');
        Route::post('/validate', [CouponController::class, 'validate'])->name('validate');
    });

    Route::prefix('discounts')->name('eshop360.discounts.')->middleware('can:eshop.promotions.manage')->group(function () {
        Route::get('/', [DiscountController::class, 'index'])->name('index');
        Route::post('/', [DiscountController::class, 'store'])->name('store');
        Route::put('/{discount}', [DiscountController::class, 'update'])->name('update');
        Route::delete('/{discount}', [DiscountController::class, 'destroy'])->name('destroy');
        Route::get('/plans', [DiscountController::class, 'plans'])->name('plans');
        Route::post('/plans', [DiscountController::class, 'storePlan'])->name('plans.store');
        Route::put('/plans/{plan}', [DiscountController::class, 'updatePlan'])->name('plans.update');
        Route::delete('/plans/{plan}', [DiscountController::class, 'destroyPlan'])->name('plans.destroy');
    });

    Route::prefix('quotations')->name('eshop360.quotations.')->middleware('can:eshop.sales.view')->group(function () {
        Route::get('/', [QuotationController::class, 'index'])->name('index');
        Route::post('/', [QuotationController::class, 'store'])->middleware('can:eshop.sales.manage')->name('store');
        Route::get('/{quotation}', [QuotationController::class, 'show'])->name('show');
        Route::put('/{quotation}', [QuotationController::class, 'update'])->middleware('can:eshop.sales.manage')->name('update');
        Route::delete('/{quotation}', [QuotationController::class, 'destroy'])->middleware('can:eshop.sales.manage')->name('destroy');
    });

    // ─── Reports ────────────────────────────────────
    Route::prefix('reports')->name('eshop360.reports.')->middleware('can:eshop.reports.view')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/products', [ReportController::class, 'products'])->name('products');
        Route::get('/best-sellers', [ReportController::class, 'bestSellers'])->name('best-sellers');
        Route::get('/stock-history', [ReportController::class, 'stockHistory'])->name('stock-history');
        Route::get('/sold-stock', [ReportController::class, 'soldStock'])->name('sold-stock');
        Route::get('/customers', [ReportController::class, 'customerReport'])->name('customers');
        Route::get('/purchases', [ReportController::class, 'purchaseReport'])->name('purchases');
        Route::get('/invoices', [ReportController::class, 'invoiceReport'])->name('invoices');
    });

    // ─── Eshop Settings ─────────────────────────────
    Route::prefix('eshop-settings')->name('eshop360.settings.')->middleware('can:eshop.settings.manage')->group(function () {
        Route::get('/pos', [EshopSettingsController::class, 'pos'])->name('pos');
        Route::put('/pos', [EshopSettingsController::class, 'updatePos'])->name('pos.update');
        Route::get('/printer', [EshopSettingsController::class, 'printer'])->name('printer');
        Route::put('/printer', [EshopSettingsController::class, 'updatePrinter'])->name('printer.update');
        Route::get('/invoice', [EshopSettingsController::class, 'invoice'])->name('invoice');
        Route::put('/invoice', [EshopSettingsController::class, 'updateInvoice'])->name('invoice.update');
    });

    // ─── Suppliers ──────────────────────────────────
    Route::prefix('suppliers')->name('eshop360.suppliers.')->middleware('can:eshop.suppliers.view')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->middleware('can:eshop.suppliers.manage')->name('create');
        Route::post('/', [SupplierController::class, 'store'])->middleware('can:eshop.suppliers.manage')->name('store');
        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->middleware('can:eshop.suppliers.manage')->name('edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->middleware('can:eshop.suppliers.manage')->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->middleware('can:eshop.suppliers.manage')->name('destroy');
        Route::get('/{supplier}/statement', [SupplierController::class, 'statement'])->name('statement');
    });

    // ─── Imports ─────────────────────────────────────
    Route::prefix('imports')->name('eshop360.imports.')->middleware(['can:eshop.imports.view', 'billing.feature:eshop360.imports'])->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::get('/create', [ImportController::class, 'create'])->middleware('can:eshop.imports.manage')->name('create');
        Route::post('/', [ImportController::class, 'store'])->middleware('can:eshop.imports.manage')->name('store');
        Route::get('/{import}', [ImportController::class, 'show'])->name('show');
        Route::put('/{import}', [ImportController::class, 'update'])->middleware('can:eshop.imports.manage')->name('update');
        Route::post('/{import}/costs', [ImportController::class, 'addCost'])->middleware('can:eshop.imports.manage')->name('costs.add');
        Route::delete('/{import}/costs/{cost}', [ImportController::class, 'removeCost'])->middleware('can:eshop.imports.manage')->name('costs.remove');
        Route::post('/{import}/allocate', [ImportController::class, 'allocateCosts'])->middleware('can:eshop.imports.manage')->name('allocate');
        Route::post('/{import}/receive', [ImportController::class, 'receive'])->middleware('can:eshop.imports.manage')->name('receive');
        Route::delete('/{import}', [ImportController::class, 'destroy'])->middleware('can:eshop.imports.manage')->name('destroy');
    });

    // ─── Finance: Accounts ──────────────────────────
    Route::prefix('finance/accounts')->name('eshop360.finance.accounts.')->middleware('can:eshop.finance.view')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::post('/', [AccountController::class, 'store'])->middleware('can:eshop.finance.manage')->name('store');
        Route::get('/{account}', [AccountController::class, 'show'])->name('show');
        Route::put('/{account}', [AccountController::class, 'update'])->middleware('can:eshop.finance.manage')->name('update');
        Route::delete('/{account}', [AccountController::class, 'destroy'])->middleware('can:eshop.finance.manage')->name('destroy');
        Route::post('/{account}/deposit', [AccountController::class, 'deposit'])->middleware('can:eshop.finance.manage')->name('deposit');
        Route::post('/{account}/withdraw', [AccountController::class, 'withdraw'])->middleware('can:eshop.finance.manage')->name('withdraw');
    });
    Route::post('finance/transfers', [AccountController::class, 'transfer'])->name('eshop360.finance.transfers.store')->middleware('can:eshop.finance.manage');

    // ─── Finance: Expenses ──────────────────────────
    Route::prefix('finance/expenses')->name('eshop360.finance.expenses.')->middleware('can:eshop.finance.view')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::post('/', [ExpenseController::class, 'store'])->middleware('can:eshop.finance.manage')->name('store');
        Route::put('/{expense}', [ExpenseController::class, 'update'])->middleware('can:eshop.finance.manage')->name('update');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->middleware('can:eshop.finance.manage')->name('destroy');
        Route::get('/categories', [ExpenseController::class, 'categories'])->name('categories');
        Route::post('/categories', [ExpenseController::class, 'storeCategory'])->middleware('can:eshop.finance.manage')->name('categories.store');
        Route::delete('/categories/{category}', [ExpenseController::class, 'destroyCategory'])->middleware('can:eshop.finance.manage')->name('categories.destroy');
    });

    // ─── Finance: Incomes ───────────────────────────
    Route::prefix('finance/incomes')->name('eshop360.finance.incomes.')->middleware('can:eshop.finance.view')->group(function () {
        Route::get('/', [IncomeController::class, 'index'])->name('index');
        Route::post('/', [IncomeController::class, 'store'])->middleware('can:eshop.finance.manage')->name('store');
        Route::put('/{income}', [IncomeController::class, 'update'])->middleware('can:eshop.finance.manage')->name('update');
        Route::delete('/{income}', [IncomeController::class, 'destroy'])->middleware('can:eshop.finance.manage')->name('destroy');
        Route::get('/sources', [IncomeController::class, 'sources'])->name('sources');
        Route::post('/sources', [IncomeController::class, 'storeSource'])->middleware('can:eshop.finance.manage')->name('sources.store');
        Route::put('/sources/{source}', [IncomeController::class, 'updateSource'])->middleware('can:eshop.finance.manage')->name('sources.update');
        Route::delete('/sources/{source}', [IncomeController::class, 'destroySource'])->middleware('can:eshop.finance.manage')->name('sources.destroy');
    });

    // ─── Finance: Loans ──────────────────────────────
    Route::prefix('finance/loans')->name('eshop360.finance.loans.')->middleware('can:eshop.finance.view')->group(function () {
        Route::get('/', [LoanController::class, 'index'])->name('index');
        Route::post('/', [LoanController::class, 'store'])->middleware('can:eshop.finance.manage')->name('store');
        Route::get('/{loan}', [LoanController::class, 'show'])->name('show');
        Route::post('/{loan}/payment', [LoanController::class, 'recordPayment'])->middleware('can:eshop.finance.manage')->name('payment');
        Route::delete('/{loan}', [LoanController::class, 'destroy'])->middleware('can:eshop.finance.manage')->name('destroy');
    });

    // ─── Finance: Gift Cards ─────────────────────────
    Route::prefix('finance/gift-cards')->name('eshop360.finance.gift-cards.')->middleware('can:eshop.finance.view')->group(function () {
        Route::get('/', [GiftCardController::class, 'index'])->name('index');
        Route::post('/', [GiftCardController::class, 'store'])->middleware('can:eshop.finance.manage')->name('store');
        Route::post('/{giftCard}/topup', [GiftCardController::class, 'topup'])->middleware('can:eshop.finance.manage')->name('topup');
        Route::match(['post', 'patch'], '/{giftCard}/disable', [GiftCardController::class, 'disable'])->middleware('can:eshop.finance.manage')->name('disable');
        Route::delete('/{giftCard}', [GiftCardController::class, 'destroy'])->middleware('can:eshop.finance.manage')->name('destroy');
    });

    // ─── Finance: Installments ───────────────────────
    Route::prefix('finance/installments')->name('eshop360.finance.installments.')->middleware('can:eshop.finance.view')->group(function () {
        Route::get('/', [InstallmentController::class, 'index'])->name('index');
        Route::post('/', [InstallmentController::class, 'store'])->middleware('can:eshop.finance.manage')->name('store');
        Route::get('/{plan}', [InstallmentController::class, 'show'])->name('show');
        Route::post('/payments/{payment}/pay', [InstallmentController::class, 'recordPayment'])->middleware('can:eshop.finance.manage')->name('payment');
    });

    // ─── HR: Employees ──────────────────────────────
    Route::prefix('hr/employees')->name('eshop360.hr.employees.')->middleware(['can:eshop.hr.view', 'billing.feature:eshop360.hr'])->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->middleware('can:eshop.hr.manage')->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->middleware('can:eshop.hr.manage')->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->middleware('can:eshop.hr.manage')->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->middleware('can:eshop.hr.manage')->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->middleware('can:eshop.hr.manage')->name('destroy');
    });

    // ─── HR: Salaries ────────────────────────────────
    Route::prefix('hr/salaries')->name('eshop360.hr.salaries.')->middleware(['can:eshop.hr.view', 'billing.feature:eshop360.hr'])->group(function () {
        Route::get('/', [SalaryController::class, 'index'])->name('index');
        Route::post('/process', [SalaryController::class, 'process'])->middleware('can:eshop.hr.manage')->name('process');
        Route::post('/', [SalaryController::class, 'process'])->middleware('can:eshop.hr.manage')->name('store');
        Route::match(['post', 'patch'], '/{salary}/pay', [SalaryController::class, 'markPaid'])->middleware('can:eshop.hr.manage')->name('pay');
    });

    // ─── HR: Attendance ──────────────────────────────
    Route::prefix('hr/attendance')->name('eshop360.hr.attendance.')->middleware(['can:eshop.hr.view', 'billing.feature:eshop360.hr'])->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('clock-out');
        Route::post('/{attendance}/clock-out', [AttendanceController::class, 'clockOut'])->name('clock-out-by-id');
        Route::get('/report', [AttendanceController::class, 'report'])->name('report');
    });

    // ─── Company Charges (Real-time) ─────────────────
    Route::prefix('charges')->name('eshop360.charges.')->middleware('can:eshop.charges.view')->group(function () {
        Route::get('/', [ChargesController::class, 'index'])->name('index');
        Route::post('/', [ChargesController::class, 'store'])->middleware('can:eshop.charges.manage')->name('store');
        Route::put('/{charge}', [ChargesController::class, 'update'])->middleware('can:eshop.charges.manage')->name('update');
        Route::delete('/{charge}', [ChargesController::class, 'destroy'])->middleware('can:eshop.charges.manage')->name('destroy');
        Route::get('/realtime', [ChargesController::class, 'realtime'])->name('realtime');
        Route::get('/realtime-data', [ChargesController::class, 'realtimeData'])->name('realtime-data');
        Route::get('/cost-absorption', [ChargesController::class, 'costAbsorption'])->name('cost-absorption');
    });

    // ─── Distribution Channels ──────────────────────────
    // (Codifarm functionality merged here — see ChannelController)
    Route::prefix('channels')->name('eshop360.channels.')->middleware(['can:eshop.channels.view', 'billing.feature:eshop360.channels'])->group(function () {
        Route::get('/', [ChannelController::class, 'index'])->name('index');
        Route::get('/create', [ChannelController::class, 'create'])->middleware('can:eshop.channels.manage')->name('create');
        Route::post('/', [ChannelController::class, 'store'])->middleware('can:eshop.channels.manage')->name('store');
        Route::get('/{channel}', [ChannelController::class, 'show'])->name('show');
        Route::get('/{channel}/dashboard', [ChannelController::class, 'show'])->name('dashboard');
        Route::get('/{channel}/edit', [ChannelController::class, 'edit'])->middleware('can:eshop.channels.manage')->name('edit');
        Route::get('/{channel}/settings', [ChannelController::class, 'edit'])->middleware('can:eshop.channels.manage')->name('settings');
        Route::put('/{channel}', [ChannelController::class, 'update'])->middleware('can:eshop.channels.manage')->name('update');
        Route::put('/{channel}/settings', [ChannelController::class, 'update'])->middleware('can:eshop.channels.manage')->name('settings.update');
        Route::delete('/{channel}', [ChannelController::class, 'destroy'])->middleware('can:eshop.channels.manage')->name('destroy');
        Route::get('/{channel}/margins', [ChannelController::class, 'margins'])->name('margins');
        Route::get('/{channel}/orders', [ChannelController::class, 'orders'])->name('orders');
    });

    // ─── Online Orders ──────────────────────────────
    Route::prefix('online-orders')->name('eshop360.online-orders.')->middleware('can:eshop.sales.view')->group(function () {
        Route::get('/', [OnlineOrderController::class, 'index'])->name('index');
        Route::get('/{onlineOrder}', [OnlineOrderController::class, 'show'])->name('show');
        Route::match(['put', 'patch'], '/{onlineOrder}/status', [OnlineOrderController::class, 'updateStatus'])->middleware('can:eshop.sales.manage')->name('status');
        Route::delete('/{onlineOrder}', [OnlineOrderController::class, 'destroy'])->middleware('can:eshop.sales.manage')->name('destroy');
    });

    // ─── Communication: Messages ─────────────────────
    Route::prefix('messages')->name('eshop360.messages.')->middleware('can:eshop.customers.view')->group(function () {
        Route::get('/inbox', [MessageController::class, 'inbox'])->name('inbox');
        Route::get('/sent', [MessageController::class, 'sent'])->name('sent');
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
    });

    // ─── Communication: SMS Gateways ─────────────────
    Route::prefix('communication/sms-gateways')->name('eshop360.sms-gateways.')->middleware('can:eshop.settings.manage')->group(function () {
        Route::get('/', [SmsGatewayController::class, 'index'])->name('index');
        Route::get('/create', [SmsGatewayController::class, 'create'])->name('create');
        Route::post('/', [SmsGatewayController::class, 'store'])->name('store');
        Route::get('/{gateway}/edit', [SmsGatewayController::class, 'edit'])->name('edit');
        Route::put('/{gateway}', [SmsGatewayController::class, 'update'])->name('update');
        Route::delete('/{gateway}', [SmsGatewayController::class, 'destroy'])->name('destroy');
        Route::post('/{gateway}/test', [SmsGatewayController::class, 'test'])->name('test');
        Route::post('/{gateway}/default', [SmsGatewayController::class, 'setDefault'])->name('set-default');
    });

    // ─── Communication: Bulk Messages ──────────────────
    Route::prefix('communication/bulk')->name('eshop360.bulk-messages.')->middleware('can:eshop.customers.manage')->group(function () {
        Route::get('/compose', [BulkMessageController::class, 'compose'])->name('compose');
        Route::post('/preview', [BulkMessageController::class, 'preview'])->name('preview');
        Route::post('/send', [BulkMessageController::class, 'send'])->name('send');
        Route::get('/history', [BulkMessageController::class, 'history'])->name('history');
        Route::get('/history/{log}', [BulkMessageController::class, 'show'])->name('show');
    });

    // ─── Communication: Support Tickets ──────────────
    Route::prefix('support-tickets')->name('eshop360.tickets.')->middleware('can:eshop.customers.view')->group(function () {
        Route::get('/', [SupportTicketController::class, 'index'])->name('index');
        Route::post('/', [SupportTicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [SupportTicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('reply');
        Route::put('/{ticket}/status', [SupportTicketController::class, 'updateStatus'])->name('status');
    });

    // ─── Communication: Email Templates ─────────────
    Route::prefix('communication/email-templates')->name('eshop360.email-templates.')->middleware('can:eshop.settings.manage')->group(function () {
        Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
        Route::get('/{template}/edit', [EmailTemplateController::class, 'edit'])->name('edit');
        Route::put('/{template}', [EmailTemplateController::class, 'update'])->name('update');
        Route::get('/{template}/preview', [EmailTemplateController::class, 'preview'])->name('preview');
        Route::post('/{template}/reset', [EmailTemplateController::class, 'resetToDefault'])->name('reset');
    });

    // ─── Advanced Reports ────────────────────────────
    Route::prefix('reports')->name('eshop360.reports.')->middleware('can:eshop.reports.view')->group(function () {
        Route::get('/overview', [AdvancedReportController::class, 'overview'])->name('overview');
        Route::get('/cashbook', [AdvancedReportController::class, 'cashbook'])->name('cashbook');
        Route::get('/profit-loss', [AdvancedReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/sales-by-category', [AdvancedReportController::class, 'salesByCategory'])->name('sales-by-category');
        Route::get('/sales-by-product', [AdvancedReportController::class, 'salesByProduct'])->name('sales-by-product');
        Route::get('/tax', [AdvancedReportController::class, 'taxReport'])->name('tax');
        Route::get('/customer-dues', [AdvancedReportController::class, 'customerDues'])->name('customer-dues');
        Route::get('/supplier-dues', [AdvancedReportController::class, 'supplierDues'])->name('supplier-dues');
        Route::get('/commissions', [AdvancedReportController::class, 'employeeCommissions'])->name('commissions');
        Route::get('/pos-overview', [AdvancedReportController::class, 'posOverview'])->name('pos-overview');
        Route::get('/monthly-revenue', [AdvancedReportController::class, 'monthlyRevenue'])->name('monthly-revenue');
        Route::get('/monthly-expenses', [AdvancedReportController::class, 'monthlyExpenses'])->name('monthly-expenses');
        Route::get('/stock-report', [AdvancedReportController::class, 'stockReport'])->name('stock-report');
        Route::get('/channels', [AdvancedReportController::class, 'channelsReport'])->name('channels');
        Route::get('/charges', [AdvancedReportController::class, 'chargesReport'])->name('charges');
        Route::get('/installments-overview', [AdvancedReportController::class, 'installmentsOverview'])->name('installments');
    });

    // ─── Quotation extras ────────────────────────────
    Route::prefix('quotations')->name('eshop360.quotations.')->middleware('can:eshop.sales.manage')->group(function () {
        Route::post('/{quotation}/convert', [\Modules\Eshop360\Http\Controllers\Promotion\QuotationController::class, 'convertToInvoice'])->name('convert');
        Route::get('/{quotation}/pdf', [\Modules\Eshop360\Http\Controllers\Promotion\QuotationController::class, 'pdf'])->middleware('can:eshop.sales.view')->name('pdf');
        Route::post('/{quotation}/send-email', [\Modules\Eshop360\Http\Controllers\Promotion\QuotationController::class, 'sendEmail'])->name('send-email');
    });

    // ─── CinetPay Payment Gateway ────────────────────
    Route::prefix('payment/cinetpay')->name('eshop360.cinetpay.')->middleware('can:eshop.sales.manage')->group(function () {
        Route::post('/orders/{order}/initiate', [CinetPayController::class, 'initiate'])->name('initiate');
        Route::get('/status/{transactionId}', [CinetPayController::class, 'status'])->name('status');
        Route::get('/settings', [CinetPayController::class, 'settings'])->middleware('can:eshop.settings.manage')->name('settings');
        Route::put('/settings', [CinetPayController::class, 'updateSettings'])->middleware('can:eshop.settings.manage')->name('settings.update');
    });

    // Legacy InetPay aliases kept for compatibility
    Route::prefix('payment/inetpay')->name('eshop360.inetpay.')->middleware('can:eshop.sales.manage')->group(function () {
        Route::post('/orders/{order}/initiate', [InetPayController::class, 'initiate'])->name('initiate');
        Route::get('/status/{transactionId}', [InetPayController::class, 'status'])->name('status');
        Route::get('/settings', [InetPayController::class, 'settings'])->middleware('can:eshop.settings.manage')->name('settings');
        Route::put('/settings', [InetPayController::class, 'updateSettings'])->middleware('can:eshop.settings.manage')->name('settings.update');
    });

    // ─── Payment Gateways Management (Admin) ────────
    Route::prefix('payment-gateways')->name('eshop360.payment-gateways.')->middleware('can:eshop.settings.manage')->group(function () {
        Route::get('/', [\Modules\Eshop360\Http\Controllers\Payment\PaymentGatewayController::class, 'index'])->name('index');
        Route::get('/create', [\Modules\Eshop360\Http\Controllers\Payment\PaymentGatewayController::class, 'create'])->name('create');
        Route::post('/', [\Modules\Eshop360\Http\Controllers\Payment\PaymentGatewayController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\Modules\Eshop360\Http\Controllers\Payment\PaymentGatewayController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\Modules\Eshop360\Http\Controllers\Payment\PaymentGatewayController::class, 'update'])->name('update');
        Route::delete('/{id}', [\Modules\Eshop360\Http\Controllers\Payment\PaymentGatewayController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle', [\Modules\Eshop360\Http\Controllers\Payment\PaymentGatewayController::class, 'toggle'])->name('toggle');
    });

    // ─── Webhooks Management ────────────────────────────
    Route::prefix('webhooks')->name('eshop360.webhooks.')->middleware('can:eshop.settings.manage')->group(function () {
        Route::get('/', [\Modules\Eshop360\Http\Controllers\Settings\WebhookController::class, 'index'])->name('index');
        Route::post('/', [\Modules\Eshop360\Http\Controllers\Settings\WebhookController::class, 'store'])->name('store');
        Route::put('/{webhook}', [\Modules\Eshop360\Http\Controllers\Settings\WebhookController::class, 'update'])->name('update');
        Route::delete('/{webhook}', [\Modules\Eshop360\Http\Controllers\Settings\WebhookController::class, 'destroy'])->name('destroy');
        Route::post('/{webhook}/ping', [\Modules\Eshop360\Http\Controllers\Settings\WebhookController::class, 'ping'])->name('ping');
        Route::get('/{webhook}/logs', [\Modules\Eshop360\Http\Controllers\Settings\WebhookController::class, 'logs'])->name('logs');
        Route::post('/{webhook}/reset', [\Modules\Eshop360\Http\Controllers\Settings\WebhookController::class, 'resetFailures'])->name('reset');
    });

    // ─── Invoice PDF / Email ──────────────────────────
    Route::prefix('invoices')->name('eshop360.invoices.')->middleware('can:eshop.invoices.view')->group(function () {
        Route::get('/{invoice}/pdf', [\Modules\Eshop360\Http\Controllers\Invoice\InvoiceController::class, 'pdf'])->name('pdf');
        Route::post('/{invoice}/send-email', [\Modules\Eshop360\Http\Controllers\Invoice\InvoiceController::class, 'sendEmail'])->name('send-email');
    });

    // ─── Export Routes ───────────────────────────────
    Route::prefix('export')->name('eshop360.export.')->middleware('can:eshop.reports.view')->group(function () {
        Route::get('/products', [\Modules\Eshop360\Http\Controllers\Report\ExportController::class, 'products'])->name('products');
        Route::get('/sales', [\Modules\Eshop360\Http\Controllers\Report\ExportController::class, 'sales'])->name('sales');
        Route::get('/invoices', [\Modules\Eshop360\Http\Controllers\Report\ExportController::class, 'invoices'])->name('invoices');
        Route::get('/customers', [\Modules\Eshop360\Http\Controllers\Report\ExportController::class, 'customers'])->name('customers');
        Route::get('/suppliers', [\Modules\Eshop360\Http\Controllers\Report\ExportController::class, 'suppliers'])->name('suppliers');
        Route::get('/stock', [\Modules\Eshop360\Http\Controllers\Report\ExportController::class, 'stock'])->name('stock');
        Route::get('/purchases', [\Modules\Eshop360\Http\Controllers\Report\ExportController::class, 'purchases'])->name('purchases');
        Route::get('/expenses', [\Modules\Eshop360\Http\Controllers\Report\ExportController::class, 'expenses'])->name('expenses');
    });

    // ─── Projects & Tasks ────────────────────────────
    Route::prefix('projects')->name('eshop360.projects.')->middleware(['can:eshop.sales.view', 'billing.feature:eshop360.projects'])->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/create', [ProjectController::class, 'create'])->middleware('can:eshop.sales.manage')->name('create');
        Route::post('/', [ProjectController::class, 'store'])->middleware('can:eshop.sales.manage')->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->middleware('can:eshop.sales.manage')->name('edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->middleware('can:eshop.sales.manage')->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->middleware('can:eshop.sales.manage')->name('destroy');
        Route::get('/{project}/calendar', [ProjectController::class, 'calendar'])->name('calendar');
        Route::get('/{project}/invoices', [ProjectController::class, 'invoices'])->name('invoices');

        // Tasks (nested under project)
        Route::post('/{project}/tasks', [TaskController::class, 'store'])->middleware('can:eshop.sales.manage')->name('tasks.store');
    });

    // ─── Calendar Events ────────────────────────────
    Route::prefix('projects/calendar')->name('eshop360.calendar.')->middleware('can:eshop.sales.view')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/events', [EventController::class, 'events'])->name('events');
        Route::post('/', [EventController::class, 'store'])->middleware('can:eshop.sales.manage')->name('store');
        Route::put('/{event}', [EventController::class, 'update'])->middleware('can:eshop.sales.manage')->name('update');
        Route::delete('/{event}', [EventController::class, 'destroy'])->middleware('can:eshop.sales.manage')->name('destroy');
    });

    // Task actions (standalone, for AJAX)
    Route::prefix('tasks')->name('eshop360.tasks.')->middleware(['can:eshop.sales.manage'])->group(function () {
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
        Route::post('/{task}/comments', [TaskController::class, 'addComment'])->name('comments.store');
        Route::post('/reorder', [TaskController::class, 'reorder'])->name('reorder');
    });

    // ─── Channel Portal ─────────────────────────────
    Route::prefix('channel-portal/{channel}')
        ->middleware(['eshop.channel.resolve', 'eshop.channel.member'])
        ->name('eshop360.channel-portal.')
        ->group(function () {
            Route::get('/', [ChannelPortalDashboardController::class, 'index'])->name('dashboard');

            Route::get('/orders', [ChannelPortalOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/create', [ChannelPortalOrderController::class, 'create'])->name('orders.create');
            Route::post('/orders', [ChannelPortalOrderController::class, 'store'])->name('orders.store');
            Route::get('/orders/{order}', [ChannelPortalOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/confirm-reception', [ChannelPortalOrderController::class, 'confirmReception'])->name('orders.confirm-reception');

            Route::get('/stock', [ChannelPortalStockController::class, 'index'])->name('stock.index');

            Route::get('/sales', [ChannelPortalSaleController::class, 'index'])->name('sales.index');
            Route::get('/sales/{order}', [ChannelPortalSaleController::class, 'show'])->name('sales.show');

            Route::get('/customers', [ChannelPortalCustomerController::class, 'index'])->name('customers.index');
            Route::get('/customers/{customer}', [ChannelPortalCustomerController::class, 'show'])->name('customers.show');

            Route::get('/margins', [ChannelPortalMarginController::class, 'index'])->name('margins.index');
        });

    // ─── Notifications ───
    Route::prefix('notifications')->name('eshop360.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // ─── Printing & Receipt Templates ──────────────
    Route::prefix('receipt-templates')->name('eshop360.receipt-templates.')->middleware('can:eshop.settings.manage')->group(function () {
        Route::get('/', [ReceiptTemplateController::class, 'index'])->name('index');
        Route::get('/create', [ReceiptTemplateController::class, 'create'])->name('create');
        Route::post('/', [ReceiptTemplateController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ReceiptTemplateController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ReceiptTemplateController::class, 'update'])->name('update');
        Route::delete('/{id}', [ReceiptTemplateController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/preview', [ReceiptTemplateController::class, 'preview'])->name('preview');
    });

    Route::prefix('printing')->name('eshop360.printing.')->middleware('can:eshop.pos.access')->group(function () {
        Route::post('/test-connection', [PrinterController::class, 'testConnection'])->middleware('can:eshop.settings.manage')->name('test-connection');
        Route::post('/receipt/{order}', [PrinterController::class, 'printReceipt'])->name('receipt');
        Route::post('/open-drawer', [PrinterController::class, 'openDrawer'])->name('open-drawer');
    });

    // ─── Channel Customer Shop (public-facing e-shop scoped to a channel) ───
    Route::prefix('channel-shop/{channel}')
        ->middleware(['eshop.channel.resolve'])
        ->name('eshop360.channel-shop.')
        ->group(function () {
            Route::get('/catalog', [ChannelShopController::class, 'catalog'])->name('catalog');
            Route::get('/product/{product}', [ChannelShopController::class, 'product'])->name('product');

            Route::middleware('auth')->group(function () {
                Route::post('/cart/add', [ChannelShopController::class, 'addToCart'])->name('cart.add');
                Route::get('/cart', [ChannelShopController::class, 'cart'])->name('cart');
                Route::post('/cart/update', [ChannelShopController::class, 'updateCart'])->name('cart.update');
                Route::post('/cart/remove', [ChannelShopController::class, 'removeFromCart'])->name('cart.remove');
                Route::get('/checkout', [ChannelShopController::class, 'checkout'])->name('checkout');
                Route::post('/checkout', [ChannelShopController::class, 'placeOrder'])->name('checkout.store');
                Route::get('/orders', [ChannelShopController::class, 'orders'])->name('orders');
                Route::get('/orders/{order}', [ChannelShopController::class, 'orderDetail'])->name('orders.show');
                Route::post('/orders/{order}/confirm', [ChannelShopController::class, 'confirmReception'])->name('orders.confirm');
            });
        });
});

/*
|--------------------------------------------------------------------------
| Public Payment Routes (no auth required)
|--------------------------------------------------------------------------
|
| These routes are accessible without authentication via a payment token.
| They allow customers to pay invoices from a link sent by email.
|
*/

Route::middleware(['web'])->prefix('pay')->name('eshop360.payment.')->group(function () {
    Route::get('/{token}', [\Modules\Eshop360\Http\Controllers\Payment\PublicPaymentController::class, 'show'])->name('show');
    Route::post('/{token}', [\Modules\Eshop360\Http\Controllers\Payment\PublicPaymentController::class, 'initiate'])->name('initiate');
    Route::get('/{token}/success', [\Modules\Eshop360\Http\Controllers\Payment\PublicPaymentController::class, 'success'])->name('success');
    Route::get('/callback/{gateway}', [\Modules\Eshop360\Http\Controllers\Payment\PublicPaymentController::class, 'callback'])->name('callback');
});

// Payment webhooks (no auth, no CSRF)
Route::middleware(['api'])->prefix('api/eshop360/payment')->name('eshop360.payment.')->group(function () {
    Route::post('/webhook/{gateway}', [\Modules\Eshop360\Http\Controllers\Payment\PublicPaymentController::class, 'webhook'])->name('webhook');
});

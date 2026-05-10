<?php

namespace Modules\Eshop360\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Console\BirthdayAlertCommand;
use Modules\Eshop360\Console\Commands\CheckExpiringProducts;
use Modules\Eshop360\Console\Commands\CheckLowStock;
use Modules\Eshop360\Console\ExpireSubscriptionsCommand;
use Modules\Eshop360\Console\ExpiryAlertCommand;
use Modules\Eshop360\Console\InstallmentReminderCommand;
use Modules\Eshop360\Console\RecurringInvoiceCommand;
use Modules\Eshop360\Console\StockAlertCommand;
use Modules\Eshop360\Http\Middleware\EnsurePaidFeature;
use Modules\Eshop360\Services\AuditService;
use Modules\Eshop360\Services\CartService;
use Modules\Eshop360\Services\CashRegisterService;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\ChannelB2BService;
use Modules\Eshop360\Services\ChargesService;
use Modules\Eshop360\Services\CinetPayService;
use Modules\Eshop360\Services\CostCalculatorService;
use Modules\Eshop360\Services\EmailService;
use Modules\Eshop360\Services\EshopSettingsService;
use Modules\Eshop360\Services\ExportService;
use Modules\Eshop360\Services\FinanceService;
use Modules\Eshop360\Services\HoldingService;
use Modules\Eshop360\Services\HRService;
use Modules\Eshop360\Services\ImportService;
use Modules\Eshop360\Services\InetPayService;
use Modules\Eshop360\Services\InvoiceService;
use Modules\Eshop360\Services\MarginService;
use Modules\Eshop360\Services\OnlineOrderService;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Services\PdfService;
use Modules\Eshop360\Services\ProductPricingService;
use Modules\Eshop360\Services\ReportService;
use Modules\Eshop360\Services\SmsService;
use Modules\Eshop360\Services\StockService;
use Modules\Eshop360\Services\SupplierService;
use Modules\Eshop360\Services\WebhookService;

final class Eshop360ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'eshop360');

        // Core services
        $this->app->singleton(\Modules\Eshop360\Services\UserResourceScopeService::class);
        $this->app->singleton(ChannelAccessService::class);
        $this->app->singleton(ChannelB2BService::class);
        $this->app->singleton(EshopSettingsService::class);
        $this->app->singleton(WebhookService::class);
        $this->app->singleton(CartService::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(StockService::class);
        $this->app->singleton(InvoiceService::class);
        $this->app->singleton(ImportService::class);
        $this->app->singleton(CostCalculatorService::class);
        $this->app->singleton(MarginService::class);
        $this->app->singleton(ChargesService::class);
        $this->app->singleton(FinanceService::class);
        $this->app->singleton(SupplierService::class);
        $this->app->singleton(HRService::class);
        $this->app->singleton(OnlineOrderService::class);
        $this->app->singleton(ReportService::class);
        $this->app->singleton(AuditService::class);
        $this->app->singleton(HoldingService::class);
        $this->app->singleton(CashRegisterService::class);
        $this->app->singleton(ProductPricingService::class);

        // Pricing Engine v2 (feature-flagged)
        $this->app->singleton(\Modules\Eshop360\Pricing\Cache\PricingCacheManager::class);
        $this->app->singleton(\Modules\Eshop360\Pricing\Engines\PricingEngine::class);
        $this->app->singleton(\Modules\Eshop360\Pricing\Registry\PricingRuleRegistry::class, function ($app) {
            $registry = new \Modules\Eshop360\Pricing\Registry\PricingRuleRegistry;

            // Register built-in pricing rules
            $registry->register(new \Modules\Eshop360\Pricing\Rules\Retail\BasePriceRule);
            $registry->register(new \Modules\Eshop360\Pricing\Rules\Retail\DiscountProductRule);
            $registry->register(new \Modules\Eshop360\Pricing\Rules\Retail\TaxRule);
            $registry->register(new \Modules\Eshop360\Pricing\Rules\Retail\MinimumPriceGuard);
            $registry->register(new \Modules\Eshop360\Pricing\Rules\Channel\ChannelBasePriceRule);
            $registry->register(new \Modules\Eshop360\Pricing\Rules\Channel\ChannelMarginRule);
            $registry->register(new \Modules\Eshop360\Pricing\Rules\Wholesale\WholesalePriceRule);
            $registry->register(new \Modules\Eshop360\Pricing\Rules\Wholesale\PharmacyPriceRule);

            return $registry;
        });

        // Feature services
        $this->app->singleton(PdfService::class);
        $this->app->singleton(ExportService::class);
        $this->app->singleton(EmailService::class);
        $this->app->singleton(SmsService::class);
        $this->app->singleton(CinetPayService::class);
        $this->app->singleton(InetPayService::class, fn ($app) => $app->make(CinetPayService::class));

        // Public contracts (ADR-021 §1) — minimum perimeter for L4 modules
        // (Menuiserie360, future CCC360, etc.). Each contract is bound to its
        // Eloquent adapter by default. Consumers depend on the interface, never
        // on the adapter or the underlying Domain model.
        $this->app->bind(
            \Modules\Eshop360\Contracts\Catalog\CatalogReader::class,
            \Modules\Eshop360\Adapters\Eloquent\EloquentCatalogReader::class,
        );
        $this->app->bind(
            \Modules\Eshop360\Contracts\Customer\CustomerReader::class,
            \Modules\Eshop360\Adapters\Eloquent\EloquentCustomerReader::class,
        );
        $this->app->bind(
            \Modules\Eshop360\Contracts\Pricing\PricingResolver::class,
            \Modules\Eshop360\Adapters\Eloquent\EloquentPricingResolver::class,
        );
    }

    public function boot(): void
    {
        // R-101 S12 : centralized morph map. Keys are legacy FQNs (the values
        // stored in DB before AND after extraction, by design); values are the
        // canonical Domain classes. Replaces the per-model $morphClass pinning
        // introduced in S8/S9 (ADR-016/017) by a single source of truth here.
        //
        // Note : Relation::morphMap() (non-strict) is intentional. enforceMorphMap()
        // would throw on any morph-target outside this map — including App\Models\User,
        // models in Billing/Auth/etc., and the 2 retained non-stubs in this module.
        // We only pin the FQNs of the 88 extracted Eshop360 models.
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'Modules\Eshop360\Models\Account' => \Modules\Eshop360\Domain\Finance\Models\Account::class,
            'Modules\Eshop360\Models\AccountTransaction' => \Modules\Eshop360\Domain\Finance\Models\AccountTransaction::class,
            'Modules\Eshop360\Models\AccountTransfer' => \Modules\Eshop360\Domain\Finance\Models\AccountTransfer::class,
            'Modules\Eshop360\Models\ApiLog' => \Modules\Eshop360\Domain\Reporting\Models\ApiLog::class,
            'Modules\Eshop360\Models\Attendance' => \Modules\Eshop360\Domain\HR\Models\Attendance::class,
            'Modules\Eshop360\Models\AuditLog' => \Modules\Eshop360\Domain\Reporting\Models\AuditLog::class,
            'Modules\Eshop360\Models\Brand' => \Modules\Eshop360\Domain\Catalog\Models\Brand::class,
            'Modules\Eshop360\Models\BulkMessageLog' => \Modules\Eshop360\Domain\Communication\Models\BulkMessageLog::class,
            'Modules\Eshop360\Models\CashRegister' => \Modules\Eshop360\Domain\Sales\Models\CashRegister::class,
            'Modules\Eshop360\Models\Category' => \Modules\Eshop360\Domain\Catalog\Models\Category::class,
            'Modules\Eshop360\Models\ChannelMarginLog' => \Modules\Eshop360\Domain\Channel\Models\ChannelMarginLog::class,
            'Modules\Eshop360\Models\ChannelProductPrice' => \Modules\Eshop360\Domain\Channel\Models\ChannelProductPrice::class,
            'Modules\Eshop360\Models\ChannelUser' => \Modules\Eshop360\Domain\Channel\Models\ChannelUser::class,
            'Modules\Eshop360\Models\ChargeCategory' => \Modules\Eshop360\Domain\Finance\Models\ChargeCategory::class,
            'Modules\Eshop360\Models\ChargeLog' => \Modules\Eshop360\Domain\Finance\Models\ChargeLog::class,
            'Modules\Eshop360\Models\CompanyCharge' => \Modules\Eshop360\Domain\Finance\Models\CompanyCharge::class,
            'Modules\Eshop360\Models\Coupon' => \Modules\Eshop360\Domain\Promotions\Models\Coupon::class,
            'Modules\Eshop360\Models\Customer' => \Modules\Eshop360\Domain\CRM\Models\Customer::class,
            'Modules\Eshop360\Models\CustomerDue' => \Modules\Eshop360\Domain\CRM\Models\CustomerDue::class,
            'Modules\Eshop360\Models\CustomerGroup' => \Modules\Eshop360\Domain\CRM\Models\CustomerGroup::class,
            'Modules\Eshop360\Models\CustomerTransaction' => \Modules\Eshop360\Domain\CRM\Models\CustomerTransaction::class,
            'Modules\Eshop360\Models\Discount' => \Modules\Eshop360\Domain\Promotions\Models\Discount::class,
            'Modules\Eshop360\Models\DiscountPlan' => \Modules\Eshop360\Domain\Promotions\Models\DiscountPlan::class,
            'Modules\Eshop360\Models\DistributionChannel' => \Modules\Eshop360\Domain\Channel\Models\DistributionChannel::class,
            'Modules\Eshop360\Models\EmailTemplate' => \Modules\Eshop360\Domain\Communication\Models\EmailTemplate::class,
            'Modules\Eshop360\Models\Employee' => \Modules\Eshop360\Domain\HR\Models\Employee::class,
            'Modules\Eshop360\Models\EmployeeCommission' => \Modules\Eshop360\Domain\HR\Models\EmployeeCommission::class,
            'Modules\Eshop360\Models\EmployeeSalary' => \Modules\Eshop360\Domain\HR\Models\EmployeeSalary::class,
            'Modules\Eshop360\Models\EshopPaymentGateway' => \Modules\Eshop360\Domain\Finance\Models\EshopPaymentGateway::class,
            'Modules\Eshop360\Models\Event' => \Modules\Eshop360\Domain\Projects\Models\Event::class,
            'Modules\Eshop360\Models\Expense' => \Modules\Eshop360\Domain\Finance\Models\Expense::class,
            'Modules\Eshop360\Models\ExpenseCategory' => \Modules\Eshop360\Domain\Finance\Models\ExpenseCategory::class,
            'Modules\Eshop360\Models\FneInvoice' => \Modules\Eshop360\Domain\Finance\Models\FneInvoice::class,
            'Modules\Eshop360\Models\GiftCard' => \Modules\Eshop360\Domain\Promotions\Models\GiftCard::class,
            'Modules\Eshop360\Models\GiftCardTopup' => \Modules\Eshop360\Domain\Promotions\Models\GiftCardTopup::class,
            'Modules\Eshop360\Models\Holding' => \Modules\Eshop360\Domain\Sales\Models\Holding::class,
            'Modules\Eshop360\Models\ImportCost' => \Modules\Eshop360\Domain\Purchasing\Models\ImportCost::class,
            'Modules\Eshop360\Models\ImportCostType' => \Modules\Eshop360\Domain\Purchasing\Models\ImportCostType::class,
            'Modules\Eshop360\Models\ImportOrder' => \Modules\Eshop360\Domain\Purchasing\Models\ImportOrder::class,
            'Modules\Eshop360\Models\ImportOrderItem' => \Modules\Eshop360\Domain\Purchasing\Models\ImportOrderItem::class,
            'Modules\Eshop360\Models\Income' => \Modules\Eshop360\Domain\Finance\Models\Income::class,
            'Modules\Eshop360\Models\IncomeSource' => \Modules\Eshop360\Domain\Finance\Models\IncomeSource::class,
            'Modules\Eshop360\Models\InstallmentPayment' => \Modules\Eshop360\Domain\Finance\Models\InstallmentPayment::class,
            'Modules\Eshop360\Models\InstallmentPlan' => \Modules\Eshop360\Domain\Finance\Models\InstallmentPlan::class,
            'Modules\Eshop360\Models\Invoice' => \Modules\Eshop360\Domain\Finance\Models\Invoice::class,
            'Modules\Eshop360\Models\InvoiceItem' => \Modules\Eshop360\Domain\Finance\Models\InvoiceItem::class,
            'Modules\Eshop360\Models\Loan' => \Modules\Eshop360\Domain\Finance\Models\Loan::class,
            'Modules\Eshop360\Models\LoanPayment' => \Modules\Eshop360\Domain\Finance\Models\LoanPayment::class,
            'Modules\Eshop360\Models\LoanSchedule' => \Modules\Eshop360\Domain\Finance\Models\LoanSchedule::class,
            'Modules\Eshop360\Models\Message' => \Modules\Eshop360\Domain\Communication\Models\Message::class,
            'Modules\Eshop360\Models\OnlineOrder' => \Modules\Eshop360\Domain\Sales\Models\OnlineOrder::class,
            'Modules\Eshop360\Models\OnlineOrderItem' => \Modules\Eshop360\Domain\Sales\Models\OnlineOrderItem::class,
            'Modules\Eshop360\Models\Order' => \Modules\Eshop360\Domain\Sales\Models\Order::class,
            'Modules\Eshop360\Models\OrderItem' => \Modules\Eshop360\Domain\Sales\Models\OrderItem::class,
            'Modules\Eshop360\Models\Payment' => \Modules\Eshop360\Domain\Finance\Models\Payment::class,
            'Modules\Eshop360\Models\PaymentMethod' => \Modules\Eshop360\Domain\Finance\Models\PaymentMethod::class,
            'Modules\Eshop360\Models\PersistentCart' => \Modules\Eshop360\Domain\Sales\Models\PersistentCart::class,
            'Modules\Eshop360\Models\Product' => \Modules\Eshop360\Domain\Catalog\Models\Product::class,
            'Modules\Eshop360\Models\ProductGroup' => \Modules\Eshop360\Domain\Catalog\Models\ProductGroup::class,
            'Modules\Eshop360\Models\ProductTax' => \Modules\Eshop360\Domain\Catalog\Models\ProductTax::class,
            'Modules\Eshop360\Models\ProductVariation' => \Modules\Eshop360\Domain\Catalog\Models\ProductVariation::class,
            'Modules\Eshop360\Models\Project' => \Modules\Eshop360\Domain\Projects\Models\Project::class,
            'Modules\Eshop360\Models\PurchaseItem' => \Modules\Eshop360\Domain\Purchasing\Models\PurchaseItem::class,
            'Modules\Eshop360\Models\PurchaseOrder' => \Modules\Eshop360\Domain\Purchasing\Models\PurchaseOrder::class,
            'Modules\Eshop360\Models\PurchaseReturn' => \Modules\Eshop360\Domain\Purchasing\Models\PurchaseReturn::class,
            'Modules\Eshop360\Models\PurchaseReturnItem' => \Modules\Eshop360\Domain\Purchasing\Models\PurchaseReturnItem::class,
            'Modules\Eshop360\Models\Quotation' => \Modules\Eshop360\Domain\Sales\Models\Quotation::class,
            'Modules\Eshop360\Models\QuotationItem' => \Modules\Eshop360\Domain\Sales\Models\QuotationItem::class,
            'Modules\Eshop360\Models\ReceiptTemplate' => \Modules\Eshop360\Domain\Communication\Models\ReceiptTemplate::class,
            'Modules\Eshop360\Models\RecurringInvoice' => \Modules\Eshop360\Domain\Finance\Models\RecurringInvoice::class,
            'Modules\Eshop360\Models\SaleReturn' => \Modules\Eshop360\Domain\Sales\Models\SaleReturn::class,
            'Modules\Eshop360\Models\SmsGateway' => \Modules\Eshop360\Domain\Communication\Models\SmsGateway::class,
            'Modules\Eshop360\Models\SmsLog' => \Modules\Eshop360\Domain\Communication\Models\SmsLog::class,
            'Modules\Eshop360\Models\Stock' => \Modules\Eshop360\Domain\Inventory\Models\Stock::class,
            'Modules\Eshop360\Models\StockMovement' => \Modules\Eshop360\Domain\Inventory\Models\StockMovement::class,
            'Modules\Eshop360\Models\StockTransfer' => \Modules\Eshop360\Domain\Inventory\Models\StockTransfer::class,
            'Modules\Eshop360\Models\StockTransferItem' => \Modules\Eshop360\Domain\Inventory\Models\StockTransferItem::class,
            'Modules\Eshop360\Models\Store' => \Modules\Eshop360\Domain\Inventory\Models\Store::class,
            'Modules\Eshop360\Models\Supplier' => \Modules\Eshop360\Domain\Purchasing\Models\Supplier::class,
            'Modules\Eshop360\Models\SupportTeam' => \Modules\Eshop360\Domain\Communication\Models\SupportTeam::class,
            'Modules\Eshop360\Models\SupportTicket' => \Modules\Eshop360\Domain\Communication\Models\SupportTicket::class,
            'Modules\Eshop360\Models\Task' => \Modules\Eshop360\Domain\Projects\Models\Task::class,
            'Modules\Eshop360\Models\TaskComment' => \Modules\Eshop360\Domain\Projects\Models\TaskComment::class,
            'Modules\Eshop360\Models\Tax' => \Modules\Eshop360\Domain\Catalog\Models\Tax::class,
            'Modules\Eshop360\Models\TicketMessage' => \Modules\Eshop360\Domain\Communication\Models\TicketMessage::class,
            'Modules\Eshop360\Models\Warehouse' => \Modules\Eshop360\Domain\Inventory\Models\Warehouse::class,
            'Modules\Eshop360\Models\Webhook' => \Modules\Eshop360\Domain\Communication\Models\Webhook::class,
            'Modules\Eshop360\Models\WebhookLog' => \Modules\Eshop360\Domain\Communication\Models\WebhookLog::class,
        ]);

        // Event listeners
        \Illuminate\Support\Facades\Event::listen(
            \Modules\Eshop360\Events\ReportDataChanged::class,
            \Modules\Eshop360\Listeners\InvalidateReportCache::class,
        );

        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'eshop360');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'eshop360');
        $this->loadJsonTranslationsFrom(__DIR__.'/../Resources/lang');
        // Backward-compatibility: some views still reference the old translation namespace.
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'eshop');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Middleware alias — 'eshop.feature' is now registered by BillingServiceProvider
        // pointing to Modules\Billing\Http\Middleware\EnsureFeature.
        // This registration is kept as fallback if Billing module is not loaded.
        /** @var Router $router */
        $router = $this->app['router'];
        $middlewareAliases = method_exists($router, 'getMiddleware')
            ? $router->getMiddleware()
            : [];

        if (! array_key_exists('eshop.feature', $middlewareAliases)) {
            $router->aliasMiddleware('eshop.feature', EnsurePaidFeature::class);
        }

        // Register API instance auth middleware
        $router->aliasMiddleware('eshop360.api.auth', \Modules\Eshop360\Http\Middleware\ApiInstanceAuth::class);

        // API request logger
        $router->aliasMiddleware('eshop360.api.log', \Modules\Eshop360\Http\Middleware\ApiLogger::class);

        // Channel portal middlewares
        $router->aliasMiddleware('eshop.channel.resolve', \Modules\Eshop360\Http\Middleware\ResolveChannel::class);
        $router->aliasMiddleware('eshop.channel.member', \Modules\Eshop360\Http\Middleware\ChannelMember::class);
        $router->aliasMiddleware('eshop.channel.role', \Modules\Eshop360\Http\Middleware\ChannelRole::class);
        $router->aliasMiddleware('eshop.channel.feature', \Modules\Eshop360\Http\Middleware\EnsureChannelFeature::class);
        $router->aliasMiddleware('eshop.user.assignments', \Modules\Eshop360\Http\Middleware\ResolveUserAssignments::class);
        $router->aliasMiddleware('eshop.channel.context', \Modules\Eshop360\Http\Middleware\ApplyChannelContext::class);

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                StockAlertCommand::class,
                ExpiryAlertCommand::class,
                InstallmentReminderCommand::class,
                RecurringInvoiceCommand::class,
                BirthdayAlertCommand::class,
                ExpireSubscriptionsCommand::class,
                CheckLowStock::class,
                CheckExpiringProducts::class,
                \Modules\Eshop360\Console\Commands\ReleaseExpiredCartReservations::class,
            ]);
        }

        // Register scheduled tasks
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('eshop360:stock-alerts')->dailyAt('07:00');
            $schedule->command('eshop360:expiry-alerts --days=30')->dailyAt('07:15');
            $schedule->command('eshop360:expiry-alerts --days=7')->dailyAt('07:20');
            $schedule->command('eshop360:installment-reminders')->dailyAt('08:00');
            $schedule->command('eshop360:birthday-alerts')->dailyAt('09:00');
            $schedule->command('eshop360:recurring-invoices')->dailyAt('06:00');
            $schedule->command('eshop360:expire-subscriptions')->hourly();
            $schedule->command('eshop:check-low-stock')->hourly();
            $schedule->command('eshop:check-expiry')->dailyAt('06:00');
            $schedule->command('eshop:release-expired-carts')->everyFifteenMinutes();
        });

        // Share $instance with all eshop360 views automatically
        View::composer('eshop360::*', function ($view) {
            if (! $view->offsetExists('instance')) {
                $view->with('instance', CurrentInstance::get());
            }
        });

        // Share hierarchical menu flag with all layouts that contain a sidebar
        View::composer(['layout.partials.sidebar', 'dashboard::components.layouts.master'], function ($view) {
            $enabled = (bool) config('eshop360.hierarchical_menu');
            if (! $enabled) {
                try {
                    $enabled = (bool) app(EshopSettingsService::class)->value('general', 'hierarchical_menu', false);
                } catch (\Throwable) {
                    // DB not ready yet (install phase)
                }
            }
            $view->with('hierarchicalMenuEnabled', $enabled);
        });
    }
}

<?php

namespace Modules\Eshop360\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Console\BirthdayAlertCommand;
use Modules\Eshop360\Console\ExpiryAlertCommand;
use Modules\Eshop360\Console\ExpireSubscriptionsCommand;
use Modules\Eshop360\Console\InstallmentReminderCommand;
use Modules\Eshop360\Console\RecurringInvoiceCommand;
use Modules\Eshop360\Console\StockAlertCommand;
use Modules\Eshop360\Console\Commands\CheckLowStock;
use Modules\Eshop360\Console\Commands\CheckExpiringProducts;
use Modules\Eshop360\Console\Commands\GenerateRecurringInvoices;
use Modules\Eshop360\Http\Middleware\EnsurePaidFeature;
use Modules\Eshop360\Services\AuditService;
use Modules\Eshop360\Services\CartService;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\ChannelB2BService;
use Modules\Eshop360\Services\EshopSettingsService;
use Modules\Eshop360\Services\WebhookService;
use Modules\Eshop360\Services\CashRegisterService;
use Modules\Eshop360\Services\ChargesService;
use Modules\Eshop360\Services\CostCalculatorService;
use Modules\Eshop360\Services\EmailService;
use Modules\Eshop360\Services\ExportService;
use Modules\Eshop360\Services\FeatureGate;
use Modules\Eshop360\Services\FinanceService;
use Modules\Eshop360\Services\HoldingService;
use Modules\Eshop360\Services\HRService;
use Modules\Eshop360\Services\CinetPayService;
use Modules\Eshop360\Services\InetPayService;
use Modules\Eshop360\Services\ImportService;
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

final class Eshop360ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'eshop360');

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

        // Feature services
        // Note: FeatureGate is deprecated — use Modules\Billing\Services\FeatureRegistry instead.
        // It is kept as a backward-compatible wrapper.
        $this->app->singleton(FeatureGate::class);
        $this->app->singleton(PdfService::class);
        $this->app->singleton(ExportService::class);
        $this->app->singleton(EmailService::class);
        $this->app->singleton(SmsService::class);
        $this->app->singleton(CinetPayService::class);
        $this->app->singleton(InetPayService::class, fn ($app) => $app->make(CinetPayService::class));
    }

    public function boot(): void
    {
        // Event listeners
        \Illuminate\Support\Facades\Event::listen(
            \Modules\Eshop360\Events\ReportDataChanged::class,
            \Modules\Eshop360\Listeners\InvalidateReportCache::class,
        );

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'eshop360');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'eshop360');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../Resources/lang');
        // Backward-compatibility: some views still reference the old translation namespace.
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'eshop');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Middleware alias — 'eshop.feature' is now registered by BillingServiceProvider
        // pointing to Modules\Billing\Http\Middleware\EnsureFeature.
        // This registration is kept as fallback if Billing module is not loaded.
        /** @var Router $router */
        $router = $this->app['router'];
        $middlewareAliases = method_exists($router, 'getMiddleware')
            ? $router->getMiddleware()
            : [];

        if (!array_key_exists('eshop.feature', $middlewareAliases)) {
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
                GenerateRecurringInvoices::class,
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
            $schedule->command('eshop:generate-recurring-invoices')->dailyAt('07:00');
            $schedule->command('eshop360:expire-subscriptions')->hourly();
            $schedule->command('eshop:check-low-stock')->hourly();
            $schedule->command('eshop:check-expiry')->dailyAt('06:00');
        });

        // Share $instance with all eshop360 views automatically
        View::composer('eshop360::*', function ($view) {
            if (!$view->offsetExists('instance')) {
                $view->with('instance', CurrentInstance::get());
            }
        });

        // Share hierarchical menu flag with all layouts that contain a sidebar
        View::composer(['layout.partials.sidebar', 'dashboard::components.layouts.master'], function ($view) {
            $enabled = (bool) config('eshop360.hierarchical_menu');
            if (!$enabled) {
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

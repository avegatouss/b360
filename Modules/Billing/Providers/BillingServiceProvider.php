<?php

namespace Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Billing\Http\Middleware\EnsureFeature;
use Modules\Billing\Services\FeatureRegistry;
use Modules\Billing\Services\GatewayManager;
use Modules\Billing\Services\InvoiceManager;
use Modules\Billing\Services\PlanManager;
use Modules\Billing\Services\SubscriptionManager;

final class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'billing');

        $this->app->singleton(PlanManager::class);
        $this->app->singleton(SubscriptionManager::class);
        $this->app->singleton(InvoiceManager::class);
        $this->app->singleton(FeatureRegistry::class);
        $this->app->singleton(GatewayManager::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'billing');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Register middleware alias
        $router = $this->app['router'];
        $router->aliasMiddleware('billing.feature', EnsureFeature::class);

        // Backward compatibility: keep eshop.feature as alias
        $router->aliasMiddleware('eshop.feature', EnsureFeature::class);
    }
}

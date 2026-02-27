<?php

namespace Modules\Instances\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Instances\Services\InstanceProvisioner;

final class InstancesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(InstanceProvisioner::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'instances');
    }
}

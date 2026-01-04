<?php

namespace App\Providers;

use App\Instances\InstanceManager;
use App\Instances\InstanceResolver;
use Illuminate\Support\ServiceProvider;

class InstanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
         $this->app->singleton(InstanceResolver::class);
        $this->app->singleton(InstanceManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

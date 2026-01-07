<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;

final class CoreConsoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            \Modules\Core\Console\ClearCoreCacheCommand::class,
        ]);
    }
}

<?php

namespace Modules\Core\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

final class CoreAuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Global super-admin bypass (role WITHOUT team_id / team context)
        Gate::before(function ($user, $ability) {
            return $user && method_exists($user, 'hasRole') && $user->hasRole('super-admin') ? true : null;
        });
    }
}

<?php

namespace Modules\Auth\Providers;

use App\Instances\Instance;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Modules\Auth\Http\Middleware\CheckIpAccess;
use Modules\Auth\Http\Middleware\CheckLockscreen;
use Modules\Auth\Http\Middleware\EnsureTwoFactorChallenge;

final class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'authmod');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'auth');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components', 'auth');

        // Register 2FA middleware alias
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('auth.2fa', EnsureTwoFactorChallenge::class);
        $router->aliasMiddleware('auth.ip-check', CheckIpAccess::class);
        $router->aliasMiddleware('auth.lockscreen', CheckLockscreen::class);

        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email', ''));
            return [
                Limit::perMinute(10)->by($request->ip() . '|' . $email),
            ];
        });

        // Capacité utilisée par connexion à l'instance (non disponible si super-administrateur dans les contrôleurs).
        Gate::define('instances.login', function ($user, Instance $instance) {
            // Contournement du super-administrateur géré par Gate::before (noyau)
            return DB::connection('system')->table('instance_user')
                ->where('instance_id', $instance->id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();
        });
    }
}

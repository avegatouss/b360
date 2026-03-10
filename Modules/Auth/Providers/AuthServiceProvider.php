<?php

namespace Modules\Auth\Providers;

use App\Instances\Instance;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

final class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'authmod');

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

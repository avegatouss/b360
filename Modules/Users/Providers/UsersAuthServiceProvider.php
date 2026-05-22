<?php

namespace Modules\Users\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Users\Policies\UserPolicy;
use App\Models\User;

final class UsersAuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Optional explicit gates (policies already cover).
        Gate::define('users.viewAny', fn(User $user) => app(UserPolicy::class)->viewAny($user));
    }
}

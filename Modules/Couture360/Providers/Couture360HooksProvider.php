<?php

declare(strict_types=1);

namespace Modules\Couture360\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Registers Couture360 menus, widgets, permissions and features via the
 * b360 HookRegistry. Intentionally empty in Lot 0 — populated as later
 * lots add web screens, permissions and SaaS features.
 */
final class Couture360HooksProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Lot 10+: menu entries, dashboard widgets.
        // Lot 2+: permissions (couture.clients.*, couture.paiements.encaisser, ...).
        // Billing: features (couture.core, couture.multi_atelier, ...).
    }
}

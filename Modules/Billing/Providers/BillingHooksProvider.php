<?php

namespace Modules\Billing\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\SettingsGroup;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\TeamContext;

final class BillingHooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Billing';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        // Menu visible pour toutes les instances (abonnement, factures)
        $registry->addMenu(new MenuItem(
            id: 'billing',
            label: 'Facturation',
            route: 'billing.index',
            icon: 'ti ti-receipt',
            priority: 700,
            requiredPermission: 'billing.view',
            requiredModule: 'Billing',
            group: 'admin',
        ));

        // Menu gestion des plans (super-admin root uniquement)
        $registry->addMenu(new MenuItem(
            id: 'billing-plans',
            label: 'Plans tarifaires',
            route: 'billing.plans.index',
            icon: 'ti ti-list-details',
            priority: 710,
            requiredPermission: 'billing.manage',
            requiredModule: 'Billing',
            group: 'admin',
            visibleWhen: fn ($user, $instance) =>
                $instance?->isRoot() && TeamContext::isSuperAdmin($user),
        ));

        $registry->addSettingsGroup(new SettingsGroup(
            id: 'billing',
            label: 'Facturation',
            view: 'billing::settings',
            priority: 600,
        ));
    }
}

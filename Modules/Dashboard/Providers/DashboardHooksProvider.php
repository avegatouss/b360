<?php

namespace Modules\Dashboard\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\Registry\HookRegistry;

final class DashboardHooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Dashboard';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addMenu(new MenuItem(
            id: 'dashboard',
            label: 'Tableau de bord',
            route: 'dashboard.instance',
            icon: 'ti ti-layout-grid',
            priority: 1000,
            group: 'main',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'dashboard',
            label: 'Tableau de bord',
            permissions: [
                'dashboard.view' => 'Voir le tableau de bord',
            ],
            priority: 1000,
            module: 'Dashboard',
        ));
    }
}

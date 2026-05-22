<?php

namespace Modules\Dashboard\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
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
            icon: 'iconoir-stats-up-square',
            priority: 1000,
            group: 'main',
        ));
    }
}

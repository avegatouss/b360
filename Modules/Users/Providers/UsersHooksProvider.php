<?php

namespace Modules\Users\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\Registry\HookRegistry;

final class UsersHooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Users';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addMenu(new MenuItem(
            id: 'users',
            label: 'Utilisateurs',
            route: 'users.index',
            icon: 'ti ti-users',
            priority: 900,
            requiredPermission: 'users.view',
            requiredModule: 'Users',
            group: 'main',
        ));
    }
}

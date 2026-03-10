<?php

namespace Modules\Users\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\PermissionGroup;
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

        $registry->addMenu(new MenuItem(
            id: 'roles',
            label: 'Roles et Permissions',
            route: 'roles.index',
            icon: 'ti ti-shield-lock',
            priority: 890,
            requiredPermission: 'users.manage',
            requiredModule: 'Users',
            group: 'main',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'users',
            label: 'Utilisateurs',
            permissions: [
                'users.view' => 'Voir les utilisateurs',
                'users.manage' => 'Gerer les utilisateurs et roles',
            ],
            priority: 900,
            module: 'Users',
        ));
    }
}

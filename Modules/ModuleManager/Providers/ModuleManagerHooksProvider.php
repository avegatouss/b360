<?php

namespace Modules\ModuleManager\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\TeamContext;

final class ModuleManagerHooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'ModuleManager';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addMenu(new MenuItem(
            id: 'modules',
            label: 'Modules',
            route: 'modules.index',
            icon: 'ti ti-puzzle',
            priority: 790,
            requiredModule: 'ModuleManager',
            group: 'admin',
            visibleWhen: fn ($user, $instance) =>
                $instance?->isRoot() && TeamContext::isSuperAdmin($user),
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'modules',
            label: 'Modules',
            permissions: [
                'modules.view' => 'Voir les modules',
                'modules.manage' => 'Gerer les modules',
            ],
            priority: 790,
            module: 'ModuleManager',
        ));
    }
}

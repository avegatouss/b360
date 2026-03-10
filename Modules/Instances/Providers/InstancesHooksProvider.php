<?php

namespace Modules\Instances\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\DTO\SettingsGroup;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\TeamContext;

final class InstancesHooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Instances';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addMenu(new MenuItem(
            id: 'instances',
            label: 'Instances',
            route: 'instances.index',
            icon: 'ti ti-building',
            priority: 800,
            requiredModule: 'Instances',
            group: 'admin',
            visibleWhen: fn ($user, $instance) =>
                $instance?->isRoot() && TeamContext::isSuperAdmin($user),
        ));

        $registry->addSettingsGroup(new SettingsGroup(
            id: 'instances',
            label: 'Instances',
            priority: 900,
            requiredModule: 'Instances',
            view: 'instances::settings',
            visibleWhen: fn ($user, $instance) =>
                $instance?->isRoot() && TeamContext::isSuperAdmin($user),
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'instances',
            label: 'Instances',
            permissions: [
                'instances.view' => 'Voir les instances',
                'instances.manage' => 'Gerer les instances',
            ],
            priority: 800,
            module: 'Instances',
        ));
    }
}

<?php

namespace Modules\Settings\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\DTO\SettingsGroup;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\TeamContext;

final class SettingsHooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Settings';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addMenu(new MenuItem(
            id: 'settings',
            label: 'Parametres',
            route: 'settings.index',
            icon: 'ti ti-settings',
            priority: 100,
            requiredModule: 'Settings',
            group: 'admin',
            visibleWhen: fn ($user, $instance) =>
                $instance?->isRoot() && TeamContext::isSuperAdmin($user),
        ));

        $registry->addSettingsGroup(new SettingsGroup(
            id: 'general',
            label: 'General',
            priority: 1000,
            view: 'settings::partials.general',
        ));

        $registry->addSettingsGroup(new SettingsGroup(
            id: 'branding',
            label: 'Apparence',
            priority: 900,
            view: 'settings::partials.branding',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'settings',
            label: 'Parametres',
            permissions: [
                'settings.view' => 'Voir les parametres',
                'settings.manage' => 'Modifier les parametres',
            ],
            priority: 100,
            module: 'Settings',
        ));
    }
}

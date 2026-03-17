<?php

namespace Modules\Core\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\TeamContext;

final class CoreHooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Core';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        // Admin parent menu — Systeme
        $registry->addMenu(new MenuItem(
            id: 'core-system',
            label: 'Systeme',
            icon: 'ti ti-settings-2',
            priority: 10,
            group: 'admin',
            visibleWhen: fn ($user, $instance) =>
                $instance?->isRoot() && TeamContext::isSuperAdmin($user),
        ));

        // Backups
        $registry->addMenu(new MenuItem(
            id: 'core-backups',
            label: 'Sauvegardes',
            route: 'backups.index',
            icon: 'ti ti-database-export',
            priority: 40,
            group: 'admin',
            parentId: 'core-system',
            activePattern: 'backups.*',
            visibleWhen: fn ($user, $instance) =>
                $instance?->isRoot() && TeamContext::isSuperAdmin($user),
        ));

        // File Manager
        $registry->addMenu(new MenuItem(
            id: 'core-file-manager',
            label: 'Fichiers',
            route: 'file-manager.index',
            icon: 'ti ti-folder',
            priority: 30,
            group: 'admin',
            parentId: 'core-system',
            activePattern: 'file-manager.*',
            visibleWhen: fn ($user, $instance) =>
                TeamContext::isSuperAdmin($user) || (
                    $user && $instance && $user->hasPermissionTo('manage-files', $instance->id)
                ),
        ));

        // Audit Logs
        $registry->addMenu(new MenuItem(
            id: 'core-audit-logs',
            label: 'Journal d\'audit',
            route: 'audit-logs.index',
            icon: 'ti ti-clipboard-list',
            priority: 20,
            group: 'admin',
            parentId: 'core-system',
            activePattern: 'audit-logs.*',
            visibleWhen: fn ($user, $instance) =>
                $instance?->isRoot() && TeamContext::isSuperAdmin($user),
        ));

        // Currencies
        $registry->addMenu(new MenuItem(
            id: 'core-currencies',
            label: 'Devises',
            route: 'currencies.index',
            icon: 'ti ti-currency-dollar',
            priority: 35,
            group: 'admin',
            requiredModule: 'Currency',
            activePattern: 'currencies.*',
            visibleWhen: fn ($user, $instance) =>
                TeamContext::isSuperAdmin($user),
        ));
    }
}

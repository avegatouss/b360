<?php

namespace Modules\Demo\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\TeamContext;

final class DemoHooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Demo';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addMenu(new MenuItem(
            id: 'demo',
            label: 'Donnees de demo',
            route: 'demo.index',
            icon: 'ti ti-database',
            priority: 50,
            requiredModule: 'Demo',
            group: 'admin',
            visibleWhen: fn ($user, $instance) =>
                $instance?->isRoot() && TeamContext::isSuperAdmin($user),
        ));
    }
}

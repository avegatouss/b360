<?php

namespace Modules\Currency\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\SettingsGroup;
use Modules\Core\Hooks\Registry\HookRegistry;

final class CurrencyHooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Currency';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addSettingsGroup(new SettingsGroup(
            id: 'currency',
            label: 'Devises',
            priority: 800,
            view: 'currency::partials.settings',
        ));
    }
}

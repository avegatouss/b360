<?php

namespace Modules\Lang\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\SettingsGroup;
use Modules\Core\Hooks\Registry\HookRegistry;

final class LangHooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Lang';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addSettingsGroup(new SettingsGroup(
            id: 'lang',
            label: 'Langue',
            priority: 850,
            view: 'lang::partials.settings',
        ));
    }
}

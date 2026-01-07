<?php

namespace Modules\Core\Hooks\Contracts;

use Modules\Core\Hooks\Registry\HookRegistry;

interface RegistersHooks
{
    public function registerHooks(HookRegistry $registry): void;

    /**
     * Optional: return module name to auto-skip if disabled.
     */
    public function moduleName(): string;
}

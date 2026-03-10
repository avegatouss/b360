<?php
// Modules/Core/Hooks/Contracts/HookContributor.php

namespace Modules\Core\Hooks\Contracts;

use Modules\Core\Hooks\Registry\HookRegistry;

interface HookContributor
{
    public function register(HookRegistry $registry): void;
}

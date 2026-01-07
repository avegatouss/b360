<?php
// Modules/Core/Hooks/DTO/MenuItem.php

namespace Modules\Core\Hooks\DTO;

use Closure;

final class MenuItem
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly ?string $route = null,
        public readonly ?string $icon = null,
        public readonly int $priority = 0,
        public readonly ?Closure $visibleWhen = null, // fn($user, $instance): bool
        public readonly ?string $requiredPermission = null,
        public readonly ?string $requiredModule = null,
        public readonly ?string $group = null, // sidebar group
    ) {}
}

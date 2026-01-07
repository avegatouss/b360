<?php
// Modules/Core/Hooks/DTO/SettingsGroup.php

namespace Modules\Core\Hooks\DTO;

use Closure;

final class SettingsGroup
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly int $priority = 0,
        public readonly ?string $requiredPermission = null,
        public readonly ?string $requiredModule = null,
        public readonly ?Closure $visibleWhen = null, // fn($user, $instance): bool
        public readonly string $view, // blade view path
    ) {}
}

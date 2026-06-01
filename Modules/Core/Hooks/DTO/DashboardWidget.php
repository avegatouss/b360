<?php
// Modules/Core/Hooks/DTO/DashboardWidget.php

namespace Modules\Core\Hooks\DTO;

use Closure;

final class DashboardWidget
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly Closure $render, // fn(): string|View
        public readonly int $priority = 0,
        public readonly ?string $requiredPermission = null,
        public readonly ?string $requiredModule = null,
        public readonly ?Closure $visibleWhen = null, // fn($user, $instance): bool
    ) {}
}

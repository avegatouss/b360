<?php

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
        public readonly ?Closure $visibleWhen = null,
        public readonly ?string $requiredPermission = null,
        public readonly ?string $requiredModule = null,
        public readonly ?string $group = null,
        public readonly ?string $activePattern = null,
    ) {}

    public function url($instance): string
    {
        if (!$this->route) {
            return '#';
        }

        return route($this->route, $instance?->slug ?? '');
    }

    public function isActive(): bool
    {
        if ($this->activePattern) {
            return request()->routeIs($this->activePattern);
        }

        if ($this->route) {
            $prefix = explode('.', $this->route)[0] ?? '';
            return request()->routeIs($prefix . '.*');
        }

        return false;
    }
}

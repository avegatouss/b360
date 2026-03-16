<?php

namespace Modules\Core\Hooks\DTO;

use Closure;

final class MenuItem
{
    /** @var MenuItem[] */
    public array $children = [];

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
        public readonly ?string $parentId = null,
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
        // Check children first - parent is active if any child is active
        foreach ($this->children as $child) {
            if ($child->isActive()) {
                return true;
            }
        }

        if ($this->activePattern) {
            return request()->routeIs($this->activePattern);
        }

        if ($this->route) {
            $prefix = explode('.', $this->route)[0] ?? '';
            return request()->routeIs($prefix . '.*');
        }

        return false;
    }

    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    public function addChild(MenuItem $child): void
    {
        $this->children[] = $child;
    }

    /**
     * Sort children by priority desc, id asc.
     */
    public function sortChildren(): void
    {
        usort($this->children, function (MenuItem $a, MenuItem $b) {
            if ($a->priority !== $b->priority) {
                return $b->priority <=> $a->priority;
            }
            return $a->id <=> $b->id;
        });
    }
}

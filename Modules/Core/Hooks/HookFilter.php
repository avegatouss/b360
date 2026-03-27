<?php

namespace Modules\Core\Hooks;

use Illuminate\Support\Collection;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Modules\ModuleManager;

final class HookFilter
{
    public function __construct(private readonly ModuleManager $modules) {}

    public function filter(Collection $items, $user, $instance): Collection
    {
        return $items->filter(function ($item) use ($user, $instance) {
            if (!$this->isVisible($item, $user, $instance)) {
                return false;
            }

            // Filter children if item has them
            if ($item instanceof MenuItem && $item->hasChildren()) {
                $item->children = array_values(array_filter(
                    $item->children,
                    fn ($child) => $this->isVisible($child, $user, $instance)
                ));

                // Hide parent if all children were filtered out
                if (empty($item->children) && !$item->route) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    private function isVisible(object $item, $user, $instance): bool
    {
        if (property_exists($item, 'requiredModule') && $item->requiredModule) {
            if (!$this->modules->isEnabled($item->requiredModule)) return false;
        }

        if (property_exists($item, 'requiredPermission') && $item->requiredPermission) {
            if (!$user || !$user->can($item->requiredPermission)) return false;
        }

        if (property_exists($item, 'visibleWhen') && $item->visibleWhen) {
            return (bool) ($item->visibleWhen)($user, $instance);
        }

        return true;
    }
}

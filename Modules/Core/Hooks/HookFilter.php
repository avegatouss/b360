<?php

namespace Modules\Core\Hooks;

use Illuminate\Support\Collection;
use Modules\Core\Modules\ModuleManager;

final class HookFilter
{
    public function __construct(private readonly ModuleManager $modules) {}

    public function filter(Collection $items, $user, $instance): Collection
    {
        return $items->filter(function ($item) use ($user, $instance) {
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
        })->values();
    }
}

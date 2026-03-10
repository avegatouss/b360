<?php

namespace Modules\Core\Hooks\Registry;

use Illuminate\Support\Collection;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\DashboardWidget;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\DTO\SettingsGroup;

final class HookRegistry
{
    /** @var array<string, array<string, object>> */
    private array $items = [
        'menu' => [],
        'widgets' => [],
        'settings_groups' => [],
        'permissions' => [],
        'notification_types' => [],
    ];

    /** @var array<string, array<string, true>> */
    private array $removed = [
        'menu' => [],
        'widgets' => [],
        'settings_groups' => [],
        'permissions' => [],
        'notification_types' => [],
    ];

    public function addMenu(MenuItem $item): void { $this->put('menu', $item->id, $item); }
    public function addWidget(DashboardWidget $item): void { $this->put('widgets', $item->id, $item); }
    public function addSettingsGroup(SettingsGroup $item): void { $this->put('settings_groups', $item->id, $item); }

    public function addPermissionGroup(PermissionGroup $group): void
    {
        $this->put('permissions', $group->id, $group);
    }

    /** @deprecated Use addPermissionGroup() instead */
    public function addPermission(string $id, string $name, int $priority = 0): void
    {
        $this->put('permissions', $id, (object)[
            'id' => $id, 'name' => $name, 'priority' => $priority
        ]);
    }

    public function addNotificationType(string $id, string $label, int $priority = 0): void
    {
        $this->put('notification_types', $id, (object)[
            'id' => $id, 'label' => $label, 'priority' => $priority
        ]);
    }

    public function remove(string $type, string $id): void
    {
        $this->removed[$type][$id] = true;
        unset($this->items[$type][$id]);
    }

    public function override(string $type, string $id, object $replacement): void
    {
        // deterministic: override always wins, also cancels removal
        unset($this->removed[$type][$id]);
        $this->items[$type][$id] = $replacement;
    }

    /** @return Collection<int, MenuItem> */
    public function menu(): Collection { return $this->sorted('menu'); }

    /** @return Collection<int, DashboardWidget> */
    public function widgets(): Collection { return $this->sorted('widgets'); }

    /** @return Collection<int, SettingsGroup> */
    public function settingsGroups(): Collection { return $this->sorted('settings_groups'); }

    /** @return Collection<int, PermissionGroup|object> */
    public function permissions(): Collection { return $this->sorted('permissions'); }

    /** @return Collection<int, object> */
    public function notificationTypes(): Collection { return $this->sorted('notification_types'); }

    private function put(string $type, string $id, object $obj): void
    {
        if (isset($this->removed[$type][$id])) {
            // deterministic: if removed earlier, add cancels removal only if explicit override
            // so add() does NOT resurrect removed items
            return;
        }
        $this->items[$type][$id] = $obj;
    }

    private function sorted(string $type): Collection
    {
        $values = array_values($this->items[$type]);

        // Deterministic stable ordering: priority desc, then id asc.
        usort($values, function ($a, $b) {
            $pa = (int)($a->priority ?? 0);
            $pb = (int)($b->priority ?? 0);
            if ($pa !== $pb) return $pb <=> $pa;

            $ia = (string)($a->id ?? '');
            $ib = (string)($b->id ?? '');
            return $ia <=> $ib;
        });

        return collect($values);
    }
}

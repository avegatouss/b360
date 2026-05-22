<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\TestCase;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\SettingsGroup;
use Modules\Core\Hooks\DTO\DashboardWidget;

final class HooksRegistryExtendedTest extends TestCase
{
    public function test_add_settings_group(): void
    {
        $r = new HookRegistry();
        $r->addSettingsGroup(new SettingsGroup(
            id: 'general',
            label: 'General',
            view: 'settings::general',
            priority: 1000,
        ));

        $groups = $r->settingsGroups();
        $this->assertCount(1, $groups);
        $this->assertSame('general', $groups->first()->id);
    }

    public function test_settings_groups_sorted_by_priority(): void
    {
        $r = new HookRegistry();
        $r->addSettingsGroup(new SettingsGroup(id: 'low', label: 'Low', view: 'v', priority: 100));
        $r->addSettingsGroup(new SettingsGroup(id: 'high', label: 'High', view: 'v', priority: 1000));
        $r->addSettingsGroup(new SettingsGroup(id: 'mid', label: 'Mid', view: 'v', priority: 500));

        $this->assertSame(['high', 'mid', 'low'], $r->settingsGroups()->pluck('id')->all());
    }

    public function test_remove_settings_group(): void
    {
        $r = new HookRegistry();
        $r->addSettingsGroup(new SettingsGroup(id: 'a', label: 'A', view: 'v'));
        $r->addSettingsGroup(new SettingsGroup(id: 'b', label: 'B', view: 'v'));
        $r->remove('settings_groups', 'a');

        $this->assertSame(['b'], $r->settingsGroups()->pluck('id')->all());
    }

    public function test_add_permission(): void
    {
        $r = new HookRegistry();
        $r->addPermission('users.view', 'View users', 10);
        $r->addPermission('users.edit', 'Edit users', 20);

        $perms = $r->permissions();
        $this->assertCount(2, $perms);
        $this->assertSame(['users.edit', 'users.view'], $perms->pluck('id')->all());
    }

    public function test_add_notification_type(): void
    {
        $r = new HookRegistry();
        $r->addNotificationType('email', 'Email', 10);

        $types = $r->notificationTypes();
        $this->assertCount(1, $types);
        $this->assertSame('email', $types->first()->id);
    }

    public function test_override_replaces_item(): void
    {
        $r = new HookRegistry();
        $r->addMenu(new MenuItem(id: 'a', label: 'Original', priority: 10));
        $r->override('menu', 'a', new MenuItem(id: 'a', label: 'Replaced', priority: 10));

        $this->assertSame('Replaced', $r->menu()->first()->label);
    }

    public function test_override_cancels_removal(): void
    {
        $r = new HookRegistry();
        $r->addMenu(new MenuItem(id: 'a', label: 'A'));
        $r->remove('menu', 'a');
        $this->assertCount(0, $r->menu());

        $r->override('menu', 'a', new MenuItem(id: 'a', label: 'Revived'));
        $this->assertCount(1, $r->menu());
        $this->assertSame('Revived', $r->menu()->first()->label);
    }

    public function test_add_after_remove_does_not_resurrect(): void
    {
        $r = new HookRegistry();
        $r->addMenu(new MenuItem(id: 'a', label: 'A'));
        $r->remove('menu', 'a');
        $r->addMenu(new MenuItem(id: 'a', label: 'A2'));

        $this->assertCount(0, $r->menu());
    }

    public function test_empty_registry_returns_empty_collections(): void
    {
        $r = new HookRegistry();

        $this->assertCount(0, $r->menu());
        $this->assertCount(0, $r->widgets());
        $this->assertCount(0, $r->settingsGroups());
        $this->assertCount(0, $r->permissions());
        $this->assertCount(0, $r->notificationTypes());
    }
}

<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Tests\TestCase;

final class PermissionGroupHookTest extends TestCase
{
    public function test_permission_group_dto_holds_data(): void
    {
        $group = new PermissionGroup(
            id: 'billing',
            label: 'Facturation',
            permissions: [
                'billing.view' => 'Voir',
                'billing.manage' => 'Gerer',
            ],
            priority: 700,
            module: 'Billing',
        );

        $this->assertSame('billing', $group->id);
        $this->assertSame('Facturation', $group->label);
        $this->assertCount(2, $group->permissions);
        $this->assertSame('Voir', $group->permissions['billing.view']);
        $this->assertSame(700, $group->priority);
        $this->assertSame('Billing', $group->module);
    }

    public function test_registry_stores_and_retrieves_permission_groups(): void
    {
        $registry = new HookRegistry();

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'users',
            label: 'Utilisateurs',
            permissions: ['users.view' => 'Voir', 'users.manage' => 'Gerer'],
            priority: 900,
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'billing',
            label: 'Facturation',
            permissions: ['billing.view' => 'Voir'],
            priority: 700,
        ));

        $groups = $registry->permissions();

        $this->assertCount(2, $groups);
        // Sorted by priority desc
        $this->assertSame('users', $groups->first()->id);
        $this->assertSame('billing', $groups->last()->id);
    }

    public function test_registry_remove_permission_group(): void
    {
        $registry = new HookRegistry();

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'temp',
            label: 'Temp',
            permissions: ['temp.view' => 'View'],
        ));

        $registry->remove('permissions', 'temp');

        $this->assertCount(0, $registry->permissions());
    }

    public function test_registry_override_permission_group(): void
    {
        $registry = new HookRegistry();

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'mod',
            label: 'Original',
            permissions: ['mod.view' => 'View'],
            priority: 100,
        ));

        $registry->override('permissions', 'mod', new PermissionGroup(
            id: 'mod',
            label: 'Overridden',
            permissions: ['mod.view' => 'View', 'mod.manage' => 'Manage'],
            priority: 200,
        ));

        $groups = $registry->permissions();
        $this->assertCount(1, $groups);
        $this->assertSame('Overridden', $groups->first()->label);
        $this->assertCount(2, $groups->first()->permissions);
    }

    public function test_modules_register_permission_groups_via_hooks(): void
    {
        // After app boots, hooks providers should have registered permission groups
        $registry = app(HookRegistry::class);
        $groups = $registry->permissions();

        // At minimum, Dashboard and Users should have registered their groups
        $ids = $groups->pluck('id')->toArray();

        $this->assertContains('dashboard', $ids);
        $this->assertContains('users', $ids);
    }

    public function test_permission_group_default_values(): void
    {
        $group = new PermissionGroup(
            id: 'minimal',
            label: 'Minimal',
            permissions: ['minimal.view' => 'View'],
        );

        $this->assertSame(0, $group->priority);
        $this->assertNull($group->module);
    }
}

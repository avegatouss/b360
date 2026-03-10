<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\TestCase;
use Modules\Core\Hooks\HookManager;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\Contracts\RegistersHooks;

final class HookManagerBootTest extends TestCase
{
    public function test_boot_registers_providers(): void
    {
        $registry = new HookRegistry();
        $manager = new HookManager($registry, app(\Modules\Core\Modules\ModuleManager::class));

        $manager->boot([StubHooksProvider::class]);

        $this->assertSame(['stub'], $registry->menu()->pluck('id')->all());
    }

    public function test_boot_runs_only_once(): void
    {
        $registry = new HookRegistry();
        $manager = new HookManager($registry, app(\Modules\Core\Modules\ModuleManager::class));

        $manager->boot([StubHooksProvider::class]);
        $manager->boot([StubHooksProvider2::class]); // second boot should be ignored

        $menu = $registry->menu();
        $this->assertSame(['stub'], $menu->pluck('id')->all());
    }

    public function test_boot_skips_disabled_module_providers(): void
    {
        $registry = new HookRegistry();
        $manager = new HookManager($registry, app(\Modules\Core\Modules\ModuleManager::class));

        // DisabledModuleProvider declares a module that doesn't exist
        $manager->boot([DisabledModuleStubProvider::class]);

        $this->assertCount(0, $registry->menu());
    }

    public function test_boot_skips_non_existent_classes(): void
    {
        $registry = new HookRegistry();
        $manager = new HookManager($registry, app(\Modules\Core\Modules\ModuleManager::class));

        // Should not throw
        $manager->boot(['NonExistent\\Class\\That\\Does\\Not\\Exist']);

        $this->assertCount(0, $registry->menu());
    }

    public function test_boot_skips_service_providers(): void
    {
        $registry = new HookRegistry();
        $manager = new HookManager($registry, app(\Modules\Core\Modules\ModuleManager::class));

        // Laravel ServiceProvider should be skipped
        $manager->boot([\Illuminate\Support\ServiceProvider::class]);

        $this->assertCount(0, $registry->menu());
    }

    public function test_registry_accessor(): void
    {
        $registry = new HookRegistry();
        $manager = new HookManager($registry, app(\Modules\Core\Modules\ModuleManager::class));

        $this->assertSame($registry, $manager->registry());
    }
}

class StubHooksProvider implements RegistersHooks
{
    public function moduleName(): string { return ''; } // empty = no module check
    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addMenu(new MenuItem(id: 'stub', label: 'Stub'));
    }
}

class StubHooksProvider2 implements RegistersHooks
{
    public function moduleName(): string { return ''; }
    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addMenu(new MenuItem(id: 'stub2', label: 'Stub2'));
    }
}

class DisabledModuleStubProvider implements RegistersHooks
{
    public function moduleName(): string { return 'SomeModuleThatDoesNotExist'; }
    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addMenu(new MenuItem(id: 'disabled', label: 'Disabled'));
    }
}

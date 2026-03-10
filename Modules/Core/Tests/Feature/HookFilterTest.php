<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\TestCase;
use Modules\Core\Tests\Helpers\CreatesInstanceContext;
use Modules\Core\Database\Seeders\CoreRbacSeeder;
use Modules\Core\Hooks\HookFilter;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Modules\ModuleManager;
use Modules\Core\Support\TeamContext;

final class HookFilterTest extends TestCase
{
    use CreatesInstanceContext;

    private function makeFilter(): HookFilter
    {
        return app(HookFilter::class);
    }

    public function test_items_without_constraints_pass_through(): void
    {
        $filter = $this->makeFilter();
        $items = collect([
            new MenuItem(id: 'dashboard', label: 'Dashboard'),
        ]);

        $user = $this->makeUser();
        $result = $filter->filter($items, $user, null);

        $this->assertCount(1, $result);
        $this->assertSame('dashboard', $result->first()->id);
    }

    public function test_required_module_filters_out_disabled_modules(): void
    {
        $filter = $this->makeFilter();
        $items = collect([
            new MenuItem(id: 'foo', label: 'Foo', requiredModule: 'NonExistentModule'),
        ]);

        $result = $filter->filter($items, $this->makeUser(), null);
        $this->assertCount(0, $result);
    }

    public function test_required_permission_filters_unauthorized_users(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $filter = $this->makeFilter();
        $items = collect([
            new MenuItem(id: 'users', label: 'Users', requiredPermission: 'users.view'),
        ]);

        // Regular user without permissions
        $user = $this->makeUser('noperm@test.com');
        $result = $filter->filter($items, $user, null);
        $this->assertCount(0, $result);
    }

    public function test_required_permission_allows_authorized_users(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $filter = $this->makeFilter();
        $items = collect([
            new MenuItem(id: 'users', label: 'Users', requiredPermission: 'users.view'),
        ]);

        // Super-admin can do anything (Gate::before)
        $user = $this->makeUser('sa@test.com');
        TeamContext::clear();
        $user->assignRole('super-admin');

        $result = $filter->filter($items, $user, null);
        $this->assertCount(1, $result);
    }

    public function test_visible_when_callback_filters_items(): void
    {
        $filter = $this->makeFilter();

        $items = collect([
            new MenuItem(
                id: 'admin-only',
                label: 'Admin',
                visibleWhen: fn ($user, $instance) => $instance?->slug === 'root',
            ),
        ]);

        $rootInstance = $this->makeInstance('root');
        $otherInstance = $this->makeInstance('acme');

        $user = $this->makeUser();

        // Visible on root
        $result = $filter->filter($items, $user, $rootInstance);
        $this->assertCount(1, $result);

        // Hidden on other instance
        $result = $filter->filter($items, $user, $otherInstance);
        $this->assertCount(0, $result);
    }

    public function test_null_user_filters_out_permission_items(): void
    {
        $filter = $this->makeFilter();
        $items = collect([
            new MenuItem(id: 'users', label: 'Users', requiredPermission: 'users.view'),
            new MenuItem(id: 'dash', label: 'Dashboard'),
        ]);

        $result = $filter->filter($items, null, null);
        $this->assertCount(1, $result);
        $this->assertSame('dash', $result->first()->id);
    }

    public function test_filter_reindexes_values(): void
    {
        $filter = $this->makeFilter();
        $items = collect([
            new MenuItem(id: 'a', label: 'A', visibleWhen: fn () => false),
            new MenuItem(id: 'b', label: 'B'),
            new MenuItem(id: 'c', label: 'C', visibleWhen: fn () => false),
            new MenuItem(id: 'd', label: 'D'),
        ]);

        $result = $filter->filter($items, null, null);
        $this->assertSame([0, 1], $result->keys()->all());
    }
}

<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Hooks\DTO\MenuItem;
use PHPUnit\Framework\TestCase;

final class MenuItemTest extends TestCase
{
    public function test_url_returns_hash_when_no_route(): void
    {
        $item = new MenuItem(id: 'test', label: 'Test');
        $this->assertSame('#', $item->url(null));
    }

    public function test_default_values(): void
    {
        $item = new MenuItem(id: 'x', label: 'X');

        $this->assertSame('x', $item->id);
        $this->assertSame('X', $item->label);
        $this->assertNull($item->route);
        $this->assertNull($item->icon);
        $this->assertSame(0, $item->priority);
        $this->assertNull($item->visibleWhen);
        $this->assertNull($item->requiredPermission);
        $this->assertNull($item->requiredModule);
        $this->assertNull($item->group);
        $this->assertNull($item->activePattern);
    }

    public function test_is_active_returns_false_when_no_route_and_no_pattern(): void
    {
        $item = new MenuItem(id: 'test', label: 'Test');
        $this->assertFalse($item->isActive());
    }

    public function test_constructor_accepts_all_parameters(): void
    {
        $cb = fn () => true;
        $item = new MenuItem(
            id: 'full',
            label: 'Full',
            route: 'dashboard.index',
            icon: 'ti ti-home',
            priority: 100,
            visibleWhen: $cb,
            requiredPermission: 'users.view',
            requiredModule: 'Users',
            group: 'main',
            activePattern: 'dashboard.*',
        );

        $this->assertSame('full', $item->id);
        $this->assertSame('Full', $item->label);
        $this->assertSame('dashboard.index', $item->route);
        $this->assertSame('ti ti-home', $item->icon);
        $this->assertSame(100, $item->priority);
        $this->assertSame($cb, $item->visibleWhen);
        $this->assertSame('users.view', $item->requiredPermission);
        $this->assertSame('Users', $item->requiredModule);
        $this->assertSame('main', $item->group);
        $this->assertSame('dashboard.*', $item->activePattern);
    }
}

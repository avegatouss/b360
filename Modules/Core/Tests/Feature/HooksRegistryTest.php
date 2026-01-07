<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\TestCase;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Hooks\DTO\MenuItem;

final class HooksRegistryTest extends TestCase
{
    public function test_override_and_remove_are_deterministic(): void
    {
        $r = new HookRegistry();

        $r->addMenu(new MenuItem(id: 'a', label: 'A', priority: 10));
        $r->addMenu(new MenuItem(id: 'b', label: 'B', priority: 10));
        $r->remove('menu', 'b');

        // add after remove does NOT resurrect (must use override)
        $r->addMenu(new MenuItem(id: 'b', label: 'B2', priority: 999));
        $this->assertSame(['a'], $r->menu()->pluck('id')->all());

        // override resurrects deterministically
        $r->override('menu', 'b', new MenuItem(id: 'b', label: 'B3', priority: 10));
        $this->assertSame(['a', 'b'], $r->menu()->pluck('id')->all()); // stable order (priority tie => id asc)
    }

    public function test_ordering_is_stable_priority_desc_then_id_asc(): void
    {
        $r = new HookRegistry();
        $r->addMenu(new MenuItem(id: 'z', label: 'Z', priority: 10));
        $r->addMenu(new MenuItem(id: 'a', label: 'A', priority: 10));
        $r->addMenu(new MenuItem(id: 'm', label: 'M', priority: 11));

        $this->assertSame(['m', 'a', 'z'], $r->menu()->pluck('id')->all());
    }
}

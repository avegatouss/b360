<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Feature;

use Modules\Core\Hooks\DTO\LayoutSlotContribution;
use Modules\Core\Hooks\Registry\HookRegistry;
use PHPUnit\Framework\TestCase;

/**
 * R-401-FIX S1 / ADR-022 — Verrouille l'API layout_slots de HookRegistry.
 *
 * Le composant `<x-dashboard::layout-slot>` dépend de cette API pour
 * itérer sur les contributions filtrées par nom de slot et priorité.
 */
final class HookRegistryLayoutSlotsTest extends TestCase
{
    public function test_add_layout_slot_and_retrieve_by_id(): void
    {
        $registry = new HookRegistry;
        $contrib = new LayoutSlotContribution(
            id: 'eshop360.header.notifications',
            slot: 'header.notifications',
            view: 'eshop360::layouts.notification-bell',
        );

        $registry->addLayoutSlot($contrib);

        $this->assertSame(1, $registry->layoutSlots()->count());
    }

    public function test_layout_slots_filters_by_slot_name(): void
    {
        $registry = new HookRegistry;
        $registry->addLayoutSlot(new LayoutSlotContribution(
            id: 'eshop360.header.notifications',
            slot: 'header.notifications',
            view: 'eshop360::layouts.notification-bell',
        ));
        $registry->addLayoutSlot(new LayoutSlotContribution(
            id: 'eshop360.hierarchical-nav.fab',
            slot: 'hierarchical-nav.fab',
            view: 'eshop360::layouts.nav-fab',
        ));

        $this->assertSame(2, $registry->layoutSlots()->count());
        $this->assertSame(1, $registry->layoutSlots('header.notifications')->count());
        $this->assertSame(0, $registry->layoutSlots('inexistant.slot')->count());
    }

    public function test_layout_slots_sorted_by_priority_desc(): void
    {
        $registry = new HookRegistry;
        $registry->addLayoutSlot(new LayoutSlotContribution(
            id: 'a',
            slot: 'common',
            view: 'a::view',
            priority: 10,
        ));
        $registry->addLayoutSlot(new LayoutSlotContribution(
            id: 'b',
            slot: 'common',
            view: 'b::view',
            priority: 100,
        ));
        $registry->addLayoutSlot(new LayoutSlotContribution(
            id: 'c',
            slot: 'common',
            view: 'c::view',
            priority: 50,
        ));

        $ids = $registry->layoutSlots('common')->pluck('id')->all();
        $this->assertSame(['b', 'c', 'a'], $ids, 'Doit être trié par priority desc.');
    }

    public function test_remove_layout_slot_takes_effect(): void
    {
        $registry = new HookRegistry;
        $registry->addLayoutSlot(new LayoutSlotContribution(
            id: 'temp.slot',
            slot: 'header.notifications',
            view: 'temp::view',
        ));

        $this->assertSame(1, $registry->layoutSlots('header.notifications')->count());

        $registry->remove('layout_slots', 'temp.slot');

        $this->assertSame(0, $registry->layoutSlots('header.notifications')->count());
    }

    public function test_override_layout_slot_replaces_view(): void
    {
        $registry = new HookRegistry;
        $original = new LayoutSlotContribution(
            id: 'eshop360.header.notifications',
            slot: 'header.notifications',
            view: 'eshop360::layouts.notification-bell',
        );
        $override = new LayoutSlotContribution(
            id: 'eshop360.header.notifications',
            slot: 'header.notifications',
            view: 'custom::override-bell',
        );

        $registry->addLayoutSlot($original);
        $registry->override('layout_slots', 'eshop360.header.notifications', $override);

        /** @var LayoutSlotContribution $only */
        $only = $registry->layoutSlots('header.notifications')->first();
        $this->assertSame('custom::override-bell', $only->view);
    }
}

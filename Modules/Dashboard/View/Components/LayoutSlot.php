<?php

declare(strict_types=1);

namespace Modules\Dashboard\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Modules\Core\Hooks\HookFilter;
use Modules\Core\Hooks\Registry\HookRegistry;

/**
 * R-401-FIX S2 / ADR-022 — Composant Blade qui rend les contributions
 * inscrites au slot nommé via HookRegistry::addLayoutSlot().
 *
 * Usage Blade :
 *   <x-dashboard::layout-slot name="header.notifications" :instance="$instance ?? null" />
 *
 * Le composant :
 *  1. Lit `HookRegistry::layoutSlots($name)` (déjà trié par priority).
 *  2. Applique HookFilter sur la collection — filtre par requiredModule,
 *     requiredPermission, visibleWhen.
 *  3. Inclut chaque vue contributrice via `@include($contrib->view,
 *     $contrib->params)` (cf. vue partielle layout-slot.blade.php).
 *
 * Renvoie un wrapper vide si 0 contribution active → aucun output HTML
 * (utile pour le layout : `@if(layoutSlotsCount > 0) ... @endif` n'est
 * pas nécessaire, le composant se "désactive" tout seul).
 */
final class LayoutSlot extends Component
{
    /** @var Collection<int, \Modules\Core\Hooks\DTO\LayoutSlotContribution> */
    public Collection $contributions;

    /** @var mixed */
    public $instance;

    public function __construct(
        public string $name,
        mixed $instance = null,
    ) {
        $this->instance = $instance;

        $registry = app(HookRegistry::class);
        $filter = app(HookFilter::class);

        $this->contributions = $filter->filter(
            $registry->layoutSlots($name),
            auth()->user(),
            $instance,
        );
    }

    public function render(): View
    {
        return view('dashboard::components.layout-slot');
    }

    /**
     * Aide les vues consommatrices à savoir si le slot a au moins une
     * contribution active. Utile pour wrapping conditionnel.
     */
    public function hasContributions(): bool
    {
        return $this->contributions->isNotEmpty();
    }
}

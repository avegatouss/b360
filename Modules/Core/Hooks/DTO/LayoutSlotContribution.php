<?php

declare(strict_types=1);

namespace Modules\Core\Hooks\DTO;

use Closure;

/**
 * R-401-FIX S1 / ADR-022 — Contribution UI nommée à un slot de layout.
 *
 * Permet à un module de contribuer du HTML à un slot nommé d'un layout
 * socle (header.notifications, hierarchical-nav.fab, etc.) sans coupler
 * le layout aux noms de routes du module contributeur.
 *
 * Le composant `<x-dashboard::layout-slot name="..." />` itère sur les
 * contributions filtrées par `HookFilter` (requiredModule, permission,
 * visibleWhen) et inclut chaque vue avec ses params.
 */
final class LayoutSlotContribution
{
    /**
     * @param  array<string, mixed>  $params  passés à la vue via @include
     */
    public function __construct(
        public readonly string $id,
        public readonly string $slot,
        public readonly string $view,
        public readonly int $priority = 0,
        public readonly ?string $requiredPermission = null,
        public readonly ?string $requiredModule = null,
        public readonly ?Closure $visibleWhen = null,
        public readonly array $params = [],
    ) {}
}

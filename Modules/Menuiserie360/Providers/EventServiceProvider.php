<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as BaseEventServiceProvider;
use Modules\Menuiserie360\Domain\Chantier\Events\ChantierTermine;
use Modules\Menuiserie360\Domain\Commercial\Events\DevisAccepte;
use Modules\Menuiserie360\Domain\Finance\Listeners\CreateAcompteOnDevisAccepte;
use Modules\Menuiserie360\Domain\Finance\Listeners\CreateSoldeOnChantierTermine;

/**
 * P2-14 — Wiring Event → Listener pour le workflow cross-BC.
 *
 * Pattern hérité du Auth EventServiceProvider — registration explicite
 * pour ne pas dépendre du auto-discovery (qui peut être imprévisible
 * en module nwidart).
 */
final class EventServiceProvider extends BaseEventServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        DevisAccepte::class => [
            CreateAcompteOnDevisAccepte::class,
        ],
        ChantierTermine::class => [
            CreateSoldeOnChantierTermine::class,
        ],
    ];
}

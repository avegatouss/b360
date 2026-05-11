<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event dispatché par TransformDevisToBcAction quand un devis est marqué accepté.
 * Payload immuable. Listener cible : CreateAcompteOnDevisAccepte.
 */
final readonly class DevisAccepte
{
    use Dispatchable;

    public function __construct(
        public int $instanceId,
        public int $devisId,
        public int $bcId,
        public int $clientId,
        public float $montantTtc,
        public float $acomptePct,
    ) {}
}

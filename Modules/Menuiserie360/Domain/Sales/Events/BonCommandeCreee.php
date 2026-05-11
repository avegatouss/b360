<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Sales\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event dispatché après création d'un BonCommande issu d'un devis accepté.
 * Listener cible : CreateOrdreFabricationOnBonCommandeCreee (P2-6).
 */
final readonly class BonCommandeCreee
{
    use Dispatchable;

    public function __construct(
        public int $instanceId,
        public int $bcId,
        public int $devisId,
        public int $clientId,
    ) {}
}

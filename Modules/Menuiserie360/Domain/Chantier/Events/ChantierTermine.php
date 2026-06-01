<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Chantier\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event dispatché à la clôture d'un chantier (passage statut → termine).
 * Listener cible : CreateFactureFinaleOnChantierTermine.
 */
final readonly class ChantierTermine
{
    use Dispatchable;

    public function __construct(
        public int $instanceId,
        public int $chantierId,
        public int $bcId,
        public int $clientId,
    ) {}
}

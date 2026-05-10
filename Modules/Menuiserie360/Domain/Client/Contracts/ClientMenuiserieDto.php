<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Client\Contracts;

/**
 * DTO Client menuiserie — agrégat (identité Eshop360 + attributs métier).
 *
 * Combine :
 *   - identité partagée (id, nom, contact) — récupérée via `CustomerReader`
 *   - attributs propres menuiserie (préférences, historique chantier, etc.)
 *     stockés dans des tables `mnu_*` dédiées (P1-4 introduit `ClientMenuiserie`).
 *
 * v1.0 — surface minimale. Champs additionnels en P1..P2 selon besoins
 * BC-Commercial et BC-Chantier.
 */
final readonly class ClientMenuiserieDto
{
    public function __construct(
        public int $id,
        public int $instanceId,
        public string $code,
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $city,
        public bool $isActive,
        // Attributs spécifiques menuiserie (à enrichir P1+) :
        public ?string $preferredContactMethod = null,
        public int $totalChantiersCount = 0,
        public float $totalRevenueXof = 0.0,
    ) {}
}

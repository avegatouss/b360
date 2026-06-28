<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Finance\Services;

/**
 * Résultat d'une tentative de matching d'une facture locale contre le registre
 * financier miroir (ADR-031 / Lot 3).
 *
 * Domaine Finance = match LIEN-ONLY (1 facture locale = 1 document miroir, pas de
 * dédup) ; comme le domaine Article, il n'existe pas de cas `review`/collision.
 * DTO dédié (non partagé) pour que les sous-domaines évoluent indépendamment.
 *
 * - `documentId !== null`  → lien existant : rafraîchir ce document miroir.
 * - `documentId === null`  → aucun lien : créer un nouveau document miroir.
 */
final readonly class FinanceMatchResult
{
    private function __construct(
        public ?int $documentId,
        public ?string $rule,
    ) {}

    public static function matched(int $documentId, string $rule): self
    {
        return new self($documentId, $rule);
    }

    public static function none(): self
    {
        return new self(null, null);
    }
}

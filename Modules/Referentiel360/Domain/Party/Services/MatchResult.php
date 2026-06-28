<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Party\Services;

/**
 * Résultat d'une tentative de matching (dédup) d'un tiers contre le golden
 * record existant.
 *
 * - `partyId !== null && ! review`  → match franc, réutiliser ce party.
 * - `review === true`               → collision ambiguë (>1 candidat) : PAS de
 *                                     fusion auto, à signaler dans le rapport.
 * - `partyId === null && ! review`  → aucun match : créer un nouveau party.
 */
final readonly class MatchResult
{
    private function __construct(
        public ?int $partyId,
        public bool $review,
        public ?string $rule,
        public ?string $reason,
    ) {}

    public static function matched(int $partyId, string $rule): self
    {
        return new self($partyId, false, $rule, null);
    }

    public static function none(): self
    {
        return new self(null, false, null, null);
    }

    public static function review(string $rule, string $reason): self
    {
        return new self(null, true, $rule, $reason);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Article\Services;

/**
 * Résultat d'une tentative de matching d'un article contre le golden record.
 *
 * Domaine Article = match LIEN-ONLY (pas de dédup par code/label : univers
 * disjoints) ; il n'existe donc pas de cas `review`/collision ici, à la
 * différence du domaine Party. DTO dédié (non partagé avec MatchResult) pour
 * que les deux sous-domaines évoluent indépendamment.
 *
 * - `articleId !== null`  → lien existant : réutiliser ce golden.
 * - `articleId === null`  → aucun lien : créer un nouveau golden.
 */
final readonly class ArticleMatchResult
{
    private function __construct(
        public ?int $articleId,
        public ?string $rule,
    ) {}

    public static function matched(int $articleId, string $rule): self
    {
        return new self($articleId, $rule);
    }

    public static function none(): self
    {
        return new self(null, null);
    }
}

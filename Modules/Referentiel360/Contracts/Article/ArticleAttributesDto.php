<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Article;

/**
 * DTO immutable d'ENTRÉE neutre (ADR-030 / Lot 2).
 *
 * Poussé par les modules L3 vers `ArticleWriter`/`ArticleResolver` (et émis par
 * les `ArticleSource` pour le backfill). Referentiel360 ignore l'origine : il ne
 * reçoit qu'une identité catalogue normalisée + l'identifiant local du module.
 *
 * Pas de clé de déduplication cross-module : les univers articles des deux
 * modules sont disjoints (sur-mesure + BOM vs SKU e-commerce). Le seul critère
 * d'idempotence est le lien (`linkType` + `localId`), porté côté matcher/writer.
 */
final readonly class ArticleAttributesDto
{
    public function __construct(
        public int $localId,
        public string $code = '',
        public string $label = '',
        public string $articleType = 'produit',
        public ?string $unit = null,
        public ?string $salePrice = null,
        public ?string $taxRate = null,
        public ?string $categoryLabel = null,
        public ?string $description = null,
        public bool $isActive = true,
        public ?string $sourceModule = null,
    ) {}
}

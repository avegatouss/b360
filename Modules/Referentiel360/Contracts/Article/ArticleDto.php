<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Article;

/**
 * DTO immutable de LECTURE du golden record article (ADR-030, pattern ADR-021).
 *
 * Surface publique consommée par les modules L3. Ajout d'un champ optionnel =
 * non-breaking ; retrait / changement de type = breaking (ADR de remplacement).
 */
final readonly class ArticleDto
{
    public function __construct(
        public int $id,
        public int $instanceId,
        public string $articleUid,
        public string $code,
        public string $label,
        public string $articleType,
        public ?string $unit,
        public ?string $salePrice,
        public ?string $taxRate,
        public ?string $categoryLabel,
        public ?string $description,
        public bool $isActive,
        public ?string $sourceModule,
    ) {}
}

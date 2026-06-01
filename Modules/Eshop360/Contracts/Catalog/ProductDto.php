<?php

declare(strict_types=1);

namespace Modules\Eshop360\Contracts\Catalog;

/**
 * Immutable DTO publié par Eshop360 pour la lecture catalogue.
 *
 * Surface publique — voir ADR-021 §1. Ne jamais ajouter un champ obligatoire
 * sans bumper en breaking change (ADR de remplacement requis).
 *
 * Convention de stabilité (ADR-021 §contraintes 4) :
 *   - ajouter un champ optionnel (avec valeur par défaut) = non-breaking
 *   - retirer un champ ou changer son type = breaking
 */
final readonly class ProductDto
{
    public function __construct(
        public int $id,
        public int $instanceId,
        public ?int $channelId,
        public ?int $categoryId,
        public ?int $brandId,
        public string $sku,
        public string $name,
        public string $slug,
        public ?string $description,
        public float $price,
        public float $costPrice,
        public float $taxRate,
        public bool $taxInclusive,
        public ?string $unit,
        public bool $isActive,
    ) {}
}

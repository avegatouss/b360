<?php

declare(strict_types=1);

namespace Modules\Eshop360\Integration\Referentiel;

use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;

/**
 * Lot 2.b (ADR-030) — Mapper unique Eshop360 → ArticleAttributesDto.
 *
 * Partagé par l'observer (push temps réel sur created/updated) et par
 * l'ArticleSource (backfill). Convertit un Product local Eshop360 en DTO neutre
 * d'entrée pour le golden record Referentiel360. Aucune logique d'écriture ici.
 *
 * 1 article par ROW (localId = id du product). Le code retombe sur 'PRD-'.id si
 * le sku est absent (sku nullable). Pas de lookup catégorie : categoryLabel reste
 * null (acceptable — non destructif côté Eshop).
 *
 * N'importe QUE la surface publique `Contracts\Article\*` de Referentiel360.
 */
final class EshopArticleMapper
{
    /**
     * Product → ArticleAttributesDto.
     *
     * code = sku ?? ('PRD-'.id). salePrice/taxRate castés en ?string (type DTO).
     */
    public function fromProduct(Product $product): ArticleAttributesDto
    {
        $localId = (int) $product->getKey();
        $sku = self::nullableString($product->getAttribute('sku'));

        return new ArticleAttributesDto(
            localId: $localId,
            code: $sku ?? 'PRD-'.$localId,
            label: self::nullableString($product->getAttribute('name')) ?? '',
            articleType: 'produit',
            unit: self::nullableString($product->getAttribute('unit')),
            salePrice: self::nullableString($product->getAttribute('price')),
            taxRate: self::nullableString($product->getAttribute('tax_rate')),
            categoryLabel: null,
            description: self::nullableString($product->getAttribute('description')),
            isActive: (bool) $product->getAttribute('is_active'),
            sourceModule: 'eshop',
        );
    }

    /**
     * Normalise une valeur d'attribut en ?string : null si vide après trim.
     */
    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = is_string($value) ? trim($value) : trim((string) $value);

        return $string === '' ? null : $string;
    }
}

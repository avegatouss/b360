<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Modules\Menuiserie360\Domain\Catalog\Enums\ItemType;
use Modules\Menuiserie360\Domain\Catalog\Models\CatalogItem;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;

/**
 * Lot 2.a (ADR-030) — Mapper Menuiserie360 → ArticleAttributesDto.
 *
 * Partagé par les observers (push temps réel sur created/updated) et par les
 * ArticleSource (backfill). Convertit les DEUX modèles articles locaux de
 * Menuiserie360 (catalogue unifié `mnu_catalog_items` + matières premières
 * `mnu_matieres_premieres`) en DTO neutre d'entrée pour le golden record
 * Referentiel360. Aucune logique d'écriture ici.
 *
 * N'importe QUE la surface publique `Contracts\Article\*` de Referentiel360.
 */
final class MenuiserieArticleMapper
{
    /**
     * CatalogItem → ArticleAttributesDto.
     *
     * `articleType` est déduit du type d'item :
     *   - matiere_premiere                     → 'matiere'
     *   - service / main_oeuvre / sous_traitance → 'service'
     *   - produit_fini / fourniture (autres)   → 'produit'
     *
     * `prix_unitaire_ht` est bien un prix de VENTE HT (salePrice).
     */
    public function fromCatalogItem(CatalogItem $item): ArticleAttributesDto
    {
        return new ArticleAttributesDto(
            localId: (int) $item->getKey(),
            code: self::nullableString($item->getAttribute('code')) ?? '',
            label: self::nullableString($item->getAttribute('libelle')) ?? '',
            articleType: self::articleTypeForItem($item->getAttribute('item_type')),
            unit: self::nullableString($item->getAttribute('unite')),
            salePrice: self::nullableString($item->getAttribute('prix_unitaire_ht')),
            taxRate: self::nullableString($item->getAttribute('taux_tva')),
            categoryLabel: self::nullableString($item->getAttribute('category_code')),
            description: self::nullableString($item->getAttribute('description')),
            isActive: (bool) $item->getAttribute('is_active'),
            sourceModule: 'menuiserie',
        );
    }

    /**
     * MatierePremiere → ArticleAttributesDto (toujours articleType 'matiere').
     *
     * `prix_unitaire` est un COÛT d'achat, PAS un prix de vente : salePrice et
     * taxRate restent null (le golden record ne stocke ici aucune donnée de
     * vente pour une matière première).
     */
    public function fromMatiere(MatierePremiere $matiere): ArticleAttributesDto
    {
        return new ArticleAttributesDto(
            localId: (int) $matiere->getKey(),
            code: self::nullableString($matiere->getAttribute('code')) ?? '',
            label: self::nullableString($matiere->getAttribute('designation')) ?? '',
            articleType: 'matiere',
            unit: self::nullableString($matiere->getAttribute('unite')),
            salePrice: null,
            taxRate: null,
            categoryLabel: self::nullableString($matiere->getAttribute('categorie')),
            description: self::nullableString($matiere->getAttribute('notes')),
            isActive: (bool) $matiere->getAttribute('is_active'),
            sourceModule: 'menuiserie',
        );
    }

    /**
     * Déduit l'articleType neutre à partir du type d'item Menuiserie.
     *
     * `item_type` est casté en {@see ItemType} sur le modèle, mais on tolère une
     * valeur string (backfill via cursor / attribut brut).
     */
    private static function articleTypeForItem(mixed $itemType): string
    {
        $value = $itemType instanceof ItemType
            ? $itemType->value
            : (is_string($itemType) ? $itemType : '');

        return match ($value) {
            ItemType::MATIERE_PREMIERE->value => 'matiere',
            ItemType::SERVICE->value,
            ItemType::MAIN_OEUVRE->value,
            ItemType::SOUS_TRAITANCE->value => 'service',
            default => 'produit',
        };
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

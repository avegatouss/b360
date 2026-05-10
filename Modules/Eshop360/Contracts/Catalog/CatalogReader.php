<?php

declare(strict_types=1);

namespace Modules\Eshop360\Contracts\Catalog;

use Illuminate\Support\Collection;

/**
 * Contrat public de lecture catalogue Eshop360 (ADR-021 §1).
 *
 * Consommé par les modules métier futurs (Menuiserie360, etc.) via
 * injection de dépendance. L'implémentation par défaut est
 * {@see \Modules\Eshop360\Adapters\Eloquent\EloquentCatalogReader}.
 *
 * Toutes les méthodes sont scopées par instance — aucun risque de fuite
 * cross-tenant côté contrat (la responsabilité d'isolation reste néanmoins
 * partagée avec l'appelant qui doit fournir le bon `$instanceId`).
 */
interface CatalogReader
{
    /**
     * Récupère un produit par son ID au sein d'une instance.
     *
     * @return ProductDto|null null si le produit n'existe pas ou appartient
     *                         à une autre instance.
     */
    public function findProduct(int $instanceId, int $productId): ?ProductDto;

    /**
     * Récupère un produit par son SKU au sein d'une instance.
     */
    public function findProductBySku(int $instanceId, string $sku): ?ProductDto;

    /**
     * Vérifie l'existence d'un produit (utile pour validation FK applicative).
     */
    public function productExists(int $instanceId, int $productId): bool;

    /**
     * Recherche par catégorie. Retourne une Collection de ProductDto
     * (empty si aucune correspondance).
     *
     * @return Collection<int, ProductDto>
     */
    public function productsByCategory(int $instanceId, int $categoryId): Collection;
}

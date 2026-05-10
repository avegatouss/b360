<?php

declare(strict_types=1);

namespace Modules\Eshop360\Adapters\Eloquent;

use Illuminate\Support\Collection;
use Modules\Eshop360\Contracts\Catalog\CatalogReader;
use Modules\Eshop360\Contracts\Catalog\ProductDto;
use Modules\Eshop360\Domain\Catalog\Models\Product;

/**
 * Implémentation par défaut du {@see CatalogReader} via Eloquent.
 *
 * Bind par défaut dans {@see \Modules\Eshop360\Providers\Eshop360ServiceProvider::register()}.
 *
 * Note ADR-021 §1 : c'est l'unique endroit où les modèles Eloquent
 * `Modules\Eshop360\Domain\Catalog\Models\*` sont importés au-delà
 * du périmètre Eshop360. Tout consommateur externe (L4) passe par le
 * contrat, jamais par le modèle.
 */
final class EloquentCatalogReader implements CatalogReader
{
    public function findProduct(int $instanceId, int $productId): ?ProductDto
    {
        $product = Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('id', $productId)
            ->first();

        return $product ? $this->mapToDto($product) : null;
    }

    public function findProductBySku(int $instanceId, string $sku): ?ProductDto
    {
        $product = Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('sku', $sku)
            ->first();

        return $product ? $this->mapToDto($product) : null;
    }

    public function productExists(int $instanceId, int $productId): bool
    {
        return Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('id', $productId)
            ->exists();
    }

    public function productsByCategory(int $instanceId, int $categoryId): Collection
    {
        return Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('category_id', $categoryId)
            ->get()
            ->map(fn (Product $p): ProductDto => $this->mapToDto($p));
    }

    private function mapToDto(Product $p): ProductDto
    {
        $channelId = $p->getAttribute('channel_id');
        $categoryId = $p->getAttribute('category_id');
        $brandId = $p->getAttribute('brand_id');
        $description = $p->getAttribute('description');
        $unit = $p->getAttribute('unit');

        return new ProductDto(
            id: (int) $p->getAttribute('id'),
            instanceId: (int) $p->getAttribute('instance_id'),
            channelId: $channelId !== null ? (int) $channelId : null,
            categoryId: $categoryId !== null ? (int) $categoryId : null,
            brandId: $brandId !== null ? (int) $brandId : null,
            sku: (string) $p->getAttribute('sku'),
            name: (string) $p->getAttribute('name'),
            slug: (string) $p->getAttribute('slug'),
            description: $description !== null ? (string) $description : null,
            price: (float) $p->getAttribute('price'),
            costPrice: (float) $p->getAttribute('cost_price'),
            taxRate: (float) $p->getAttribute('tax_rate'),
            taxInclusive: (bool) $p->getAttribute('tax_inclusive'),
            unit: $unit !== null ? (string) $unit : null,
            isActive: (bool) $p->getAttribute('is_active'),
        );
    }
}

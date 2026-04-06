<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Illuminate\Support\Collection;
use Modules\Core\DTOs\ProductDTO;
use Modules\Core\DTOs\ProductPricingDTO;

interface ProductRepositoryInterface
{
    public function findById(int $productId): ?ProductDTO;

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, ProductDTO>
     */
    public function findByInstance(int $instanceId, array $filters = []): Collection;

    public function getPricingData(int $productId): ProductPricingDTO;

    public function belongsToInstance(int $productId, int $instanceId): bool;

    /**
     * @param  array<string, mixed>  $fields
     */
    public function updatePricingFields(int $productId, array $fields): void;
}

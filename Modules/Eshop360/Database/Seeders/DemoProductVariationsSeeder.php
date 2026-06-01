<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Catalog\Models\ProductVariation;

final class DemoProductVariationsSeeder
{
    public function run(int $instanceId): void
    {
        // Pick some existing products to add variations to
        $products = Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')
            ->take(5)
            ->get();

        if ($products->isEmpty()) {
            return;
        }

        foreach ($products as $product) {
            $this->createVariationsForProduct($product);
        }
    }

    public function reset(int $instanceId): void
    {
        $productIds = Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')
            ->pluck('id');

        ProductVariation::whereIn('product_id', $productIds)
            ->where('name', 'like', '[DEMO]%')
            ->delete();
    }

    private function createVariationsForProduct(Product $product): void
    {
        $basePrice = (float) $product->price;
        $baseCost = (float) ($product->cost_price ?? $basePrice * 0.6);

        $variations = [
            [
                'name' => '[DEMO] Boite 10 comprimes',
                'sku' => $product->sku.'-B10',
                'barcode' => '690'.str_pad((string) $product->id, 7, '0', STR_PAD_LEFT).'01',
                'price' => round($basePrice * 0.4, 2),
                'cost_price' => round($baseCost * 0.4, 2),
                'quantity' => rand(50, 200),
                'values' => ['Conditionnement' => 'Boite 10', 'Forme' => 'Comprime'],
                'is_active' => true,
            ],
            [
                'name' => '[DEMO] Boite 30 comprimes',
                'sku' => $product->sku.'-B30',
                'barcode' => '690'.str_pad((string) $product->id, 7, '0', STR_PAD_LEFT).'02',
                'price' => $basePrice,
                'cost_price' => $baseCost,
                'quantity' => rand(20, 100),
                'values' => ['Conditionnement' => 'Boite 30', 'Forme' => 'Comprime'],
                'is_active' => true,
            ],
            [
                'name' => '[DEMO] Flacon sirop 125ml',
                'sku' => $product->sku.'-S125',
                'barcode' => '690'.str_pad((string) $product->id, 7, '0', STR_PAD_LEFT).'03',
                'price' => round($basePrice * 1.2, 2),
                'cost_price' => round($baseCost * 1.1, 2),
                'quantity' => rand(10, 50),
                'values' => ['Conditionnement' => 'Flacon 125ml', 'Forme' => 'Sirop'],
                'is_active' => true,
            ],
        ];

        foreach ($variations as $v) {
            ProductVariation::updateOrCreate(
                ['product_id' => $product->id, 'name' => $v['name']],
                $v
            );
        }
    }
}

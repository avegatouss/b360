<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Services;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Pricing\Exceptions\InvalidOrderQuantityException;

/**
 * Resolves min/max order quantity constraints with a 3-level priority:
 *
 * 1. Channel-product override (eshop_channel_product_prices)
 * 2. Product-level setting (eshop_products)
 * 3. Global instance setting (eshop_module_settings)
 */
class OrderQuantityResolver
{
    /**
     * Resolve the effective min/max order quantity for a product.
     *
     * @return array{min: int, max: ?int, source: string}
     */
    public function resolve(int $productId, ?int $channelId = null): array
    {
        // Priority 1: Channel-product override
        if ($channelId !== null) {
            $channelConfig = DB::table('eshop_channel_product_prices')
                ->where('product_id', $productId)
                ->where('channel_id', $channelId)
                ->first(['min_order_quantity', 'max_order_quantity']);

            if ($channelConfig && $channelConfig->min_order_quantity !== null) {
                return [
                    'min'    => (int) $channelConfig->min_order_quantity,
                    'max'    => $channelConfig->max_order_quantity !== null
                        ? (int) $channelConfig->max_order_quantity
                        : null,
                    'source' => 'channel',
                ];
            }
        }

        // Priority 2: Product-level setting
        $product = DB::table('eshop_products')
            ->where('id', $productId)
            ->first(['min_order_quantity', 'max_order_quantity', 'instance_id']);

        if ($product && $product->min_order_quantity !== null && (int) $product->min_order_quantity > 0) {
            return [
                'min'    => (int) $product->min_order_quantity,
                'max'    => $product->max_order_quantity !== null
                    ? (int) $product->max_order_quantity
                    : null,
                'source' => 'product',
            ];
        }

        // Priority 3: Global instance setting
        $instanceId = $product->instance_id ?? 0;
        $globalSetting = DB::table('eshop_module_settings')
            ->where('instance_id', $instanceId)
            ->where('group', 'ordering')
            ->value('data');

        if ($globalSetting) {
            $data = is_string($globalSetting) ? json_decode($globalSetting, true) : $globalSetting;

            if (is_array($data) && isset($data['min_order_quantity'])) {
                return [
                    'min'    => (int) ($data['min_order_quantity'] ?? 1),
                    'max'    => isset($data['max_order_quantity'])
                        ? (int) $data['max_order_quantity']
                        : null,
                    'source' => 'global',
                ];
            }
        }

        // Default: min=1, no max
        return [
            'min'    => 1,
            'max'    => null,
            'source' => 'default',
        ];
    }

    /**
     * Validate that a quantity falls within the resolved min/max bounds.
     *
     * @throws InvalidOrderQuantityException
     */
    public function validate(int $quantity, int $productId, ?int $channelId = null): void
    {
        $limits = $this->resolve($productId, $channelId);

        if ($quantity < $limits['min']) {
            throw InvalidOrderQuantityException::belowMinimum(
                $quantity,
                $limits['min'],
                $limits['source'],
            );
        }

        if ($limits['max'] !== null && $quantity > $limits['max']) {
            throw InvalidOrderQuantityException::aboveMaximum(
                $quantity,
                $limits['max'],
                $limits['source'],
            );
        }
    }
}

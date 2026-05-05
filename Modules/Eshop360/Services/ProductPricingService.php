<?php

namespace Modules\Eshop360\Services;

use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;

class ProductPricingService
{
    /**
     * @return array{
     *     unit_price: float,
     *     original_price: float,
     *     channel_id: int|null,
     *     price_source: string
     * }
     */
    public function resolve(
        Product $product,
        ?int $channelId = null,
        bool $applyProductDiscount = false,
    ): array {
        $originalPrice = round((float) ($product->price ?? 0), 2);

        if ($channelId !== null) {
            $channel = DistributionChannel::query()
                ->whereKey($channelId)
                ->where('instance_id', $product->instance_id)
                ->first();

            if ($channel) {
                $channelPrice = (float) $product->channelPrices()
                    ->where('channel_id', $channel->id)
                    ->value('sale_price');

                if ($channelPrice <= 0) {
                    $pght = (float) ($product->pght ?? 0);
                    $channelPrice = $pght > 0
                        ? round($channel->calculateSalePrice($pght), 2)
                        : $originalPrice;
                }

                return [
                    'unit_price' => round($channelPrice, 2),
                    'original_price' => $originalPrice,
                    'channel_id' => $channel->id,
                    'price_source' => 'channel',
                ];
            }
        }

        return [
            'unit_price' => $applyProductDiscount
                ? $this->applyProductDiscount($product, $originalPrice)
                : $originalPrice,
            'original_price' => $originalPrice,
            'channel_id' => null,
            'price_source' => 'default',
        ];
    }

    private function applyProductDiscount(Product $product, float $price): float
    {
        if ($product->discount_type === 'percentage' && (float) $product->discount_value > 0) {
            return round($price - ($price * ((float) $product->discount_value / 100)), 2);
        }

        if ($product->discount_type === 'fixed' && (float) $product->discount_value > 0) {
            return round(max(0, $price - (float) $product->discount_value), 2);
        }

        return round($price, 2);
    }
}

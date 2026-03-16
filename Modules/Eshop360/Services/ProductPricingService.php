<?php

namespace Modules\Eshop360\Services;

use Modules\Eshop360\Models\CodifarmMarginConfig;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Product;

class ProductPricingService
{
    /**
     * @return array{
     *     unit_price: float,
     *     original_price: float,
     *     channel_id: int|null,
     *     is_codifarm: bool,
     *     price_source: string
     * }
     */
    public function resolve(
        Product $product,
        ?int $channelId = null,
        bool $isCodifarm = false,
        bool $applyProductDiscount = false,
    ): array {
        $originalPrice = round((float) ($product->price ?? 0), 2);

        if ($isCodifarm) {
            $codifarmPrice = (float) ($product->sale_price_codifarm ?? 0);

            if ($codifarmPrice <= 0) {
                $codifarmConfig = CodifarmMarginConfig::query()
                    ->where('instance_id', $product->instance_id)
                    ->first();

                $pght = (float) ($product->pght ?? 0);
                $codifarmPrice = $codifarmConfig && $pght > 0
                    ? round($pght * (1 + (float) $codifarmConfig->codifarm_buy_rate), 2)
                    : $originalPrice;
            }

            return [
                'unit_price' => round($codifarmPrice, 2),
                'original_price' => $originalPrice,
                'channel_id' => null,
                'is_codifarm' => true,
                'price_source' => 'codifarm',
            ];
        }

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
                    'is_codifarm' => false,
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
            'is_codifarm' => false,
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

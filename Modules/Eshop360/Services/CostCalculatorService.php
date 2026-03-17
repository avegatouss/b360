<?php

namespace Modules\Eshop360\Services;

use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\ChannelProductPrice;

class CostCalculatorService
{
    /**
     * Calculate PGHT from provisional purchase price.
     * PGHT = prix_achat_provisoire × (1 + margin_rate)
     */
    public function calculatePGHT(float $purchasePriceProvisional, ?float $marginRate = null): float
    {
        $rate = $marginRate ?? 0.13;
        return round($purchasePriceProvisional * (1 + $rate), 4);
    }

    /**
     * Calculate channel sale price from PGHT using the channel's buy_rate.
     * channel_price = PGHT × (1 + buy_rate)
     */
    public function calculateChannelPrice(float $pght, DistributionChannel $channel): float
    {
        return $channel->calculateSalePrice($pght);
    }

    /**
     * Update product pricing automatically.
     * When provisional price changes, recalculate PGHT and all channel prices.
     */
    public function updateProductPricing(Product $product, ?float $defaultMarginRate = null): void
    {
        if (!$product->purchase_price_provisional) return;

        $marginRate = $defaultMarginRate ?? 0.13;
        $pght = $this->calculatePGHT($product->purchase_price_provisional, $marginRate);
        $product->update(['pght' => $pght]);

        // Update all active channel prices (auto-calculated only, skip manual overrides)
        $channels = DistributionChannel::where('instance_id', $product->instance_id)
            ->where('is_active', true)
            ->get();

        foreach ($channels as $channel) {
            $channelPrice = $this->calculateChannelPrice($pght, $channel);

            ChannelProductPrice::updateOrCreate(
                ['channel_id' => $channel->id, 'product_id' => $product->id],
                ['sale_price' => $channelPrice, 'is_manual_override' => false]
            );
        }
    }

    /**
     * Calculate Level 1 margin (manager view).
     * marge_niv1 = prix_vente_client - prix_achat_provisoire
     */
    public function marginLevel1(float $salePrice, float $provisionalPrice): array
    {
        $margin = $salePrice - $provisionalPrice;
        $rate = $provisionalPrice > 0 ? ($margin / $provisionalPrice) * 100 : 0;
        return ['margin' => round($margin, 2), 'rate' => round($rate, 2)];
    }

    /**
     * Calculate Level 2 margin (owner/DG view).
     * marge_niv2 = prix_vente_client - cout_revient_reel
     */
    public function marginLevel2(float $salePrice, float $realCostPrice): array
    {
        $margin = $salePrice - $realCostPrice;
        $rate = $realCostPrice > 0 ? ($margin / $realCostPrice) * 100 : 0;
        return ['margin' => round($margin, 2), 'rate' => round($rate, 2)];
    }

    /**
     * Calculate real cost price from factory price + allocated import costs.
     */
    public function calculateRealCostPrice(float $factoryPrice, float $allocatedCosts, int $quantity): float
    {
        if ($quantity <= 0) return $factoryPrice;
        return round($factoryPrice + ($allocatedCosts / $quantity), 4);
    }
}

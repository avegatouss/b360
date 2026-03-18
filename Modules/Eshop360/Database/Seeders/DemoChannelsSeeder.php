<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Models\ChannelMarginLog;
use Modules\Eshop360\Models\ChannelProductPrice;
use Modules\Eshop360\Models\ChannelUser;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Product;

final class DemoChannelsSeeder
{
    public function run(int $instanceId): void
    {
        $channels = $this->seedChannels($instanceId);
        $this->seedChannelProductPrices($instanceId, $channels);
        $this->seedChannelMarginLogs($instanceId, $channels);
    }

    public function reset(int $instanceId): void
    {
        $channelIds = DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')
            ->pluck('id');

        ChannelProductPrice::whereIn('channel_id', $channelIds)->delete();
        ChannelMarginLog::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->whereIn('channel_id', $channelIds)
            ->delete();
        ChannelUser::whereIn('channel_id', $channelIds)->delete();
        DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')
            ->delete();
    }

    private function seedChannels(int $instanceId): array
    {
        $data = [
            [
                'name' => '[DEMO] CODIFARM',
                'slug' => 'demo-codifarm',
                'code' => 'DEMO-CDF',
                'description' => 'Canal de distribution CODIFARM - grossiste pharmaceutique',
                'is_active' => true,
                'margin_rate' => 0.1300,
                'buy_rate' => 0.2000,
                'debt_share' => 0.3333,
                'channel_share' => 0.3333,
                'owner_share' => 0.3334,
                'portal_enabled' => true,
                'portal_settings' => ['theme_color' => '#2c3e50', 'logo' => null],
            ],
            [
                'name' => '[DEMO] PHARMAPLUS',
                'slug' => 'demo-pharmaplus',
                'code' => 'DEMO-PHP',
                'description' => 'Canal de distribution PharmaPlus - reseau pharmacies partenaires',
                'is_active' => true,
                'margin_rate' => 0.1000,
                'buy_rate' => 0.1500,
                'debt_share' => 0.3000,
                'channel_share' => 0.3500,
                'owner_share' => 0.3500,
                'portal_enabled' => true,
                'portal_settings' => ['theme_color' => '#27ae60', 'logo' => null],
            ],
        ];

        $result = [];
        foreach ($data as $d) {
            $result[$d['slug']] = DistributionChannel::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'slug' => $d['slug']],
                array_merge($d, ['instance_id' => $instanceId])
            );
        }

        return $result;
    }

    private function seedChannelProductPrices(int $instanceId, array $channels): void
    {
        $products = Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->limit(8)
            ->get();

        if ($products->isEmpty()) {
            return;
        }

        foreach ($channels as $channel) {
            foreach ($products->take(5) as $product) {
                $pght = (float) $product->sale_price;
                $salePrice = $channel->calculateSalePrice($pght);

                ChannelProductPrice::updateOrCreate(
                    ['channel_id' => $channel->id, 'product_id' => $product->id],
                    [
                        'sale_price' => $salePrice,
                        'is_manual_override' => false,
                    ]
                );
            }
        }
    }

    private function seedChannelMarginLogs(int $instanceId, array $channels): void
    {
        foreach ($channels as $channel) {
            $logs = [
                ['total_margin' => 125000, 'debt_part' => 41667, 'channel_part' => 41667, 'owner_part' => 41666],
                ['total_margin' => 87500, 'debt_part' => 29167, 'channel_part' => 29167, 'owner_part' => 29166],
                ['total_margin' => 200000, 'debt_part' => 66667, 'channel_part' => 66667, 'owner_part' => 66666],
            ];

            foreach ($logs as $log) {
                ChannelMarginLog::withoutGlobalScopes()->create(array_merge($log, [
                    'instance_id' => $instanceId,
                    'channel_id' => $channel->id,
                    'order_id' => null,
                ]));
            }
        }
    }
}

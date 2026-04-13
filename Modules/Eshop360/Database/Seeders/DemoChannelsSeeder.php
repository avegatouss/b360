<?php

namespace Modules\Eshop360\Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Eshop360\Models\ChannelMarginLog;
use Modules\Eshop360\Models\ChannelProductPrice;
use Modules\Eshop360\Models\ChannelUser;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Services\ChannelB2BService;
use Modules\Eshop360\Services\EshopSettingsService;

final class DemoChannelsSeeder
{
    public function run(int $instanceId): void
    {
        $channels = $this->seedChannels($instanceId);
        $this->seedWarehouses($instanceId, $channels);
        $this->seedMembers($instanceId, $channels);
        $this->seedFeatures($instanceId, $channels);
        $this->seedBranding($instanceId, $channels);
        $this->seedChannelProductPrices($instanceId, $channels);
        $this->seedChannelMarginLogs($instanceId, $channels);

        // Sync hub customers for B2B
        $b2b = app(ChannelB2BService::class);
        foreach ($channels as $channel) {
            if ($channel->slug !== 'saphir-plus') {
                $b2b->syncHubCustomer($channel);
            }
        }
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
                'name' => '[DEMO] Saphir Plus',
                'slug' => 'saphir-plus',
                'code' => 'SAPHIR',
                'description' => 'Canal global Saphir Plus - hub principal, acces complet a tous les modules',
                'is_active' => true,
                'is_hub' => true,
                'margin_rate' => 0.0000,
                'buy_rate' => 0.0000,
                'debt_share' => 0.3333,
                'channel_share' => 0.3333,
                'owner_share' => 0.3334,
                'portal_enabled' => false,
                'portal_settings' => ['theme_color' => '#4f46e5', 'logo' => null],
            ],
            [
                'name' => '[DEMO] CODIFARM',
                'slug' => 'demo-codifarm',
                'code' => 'DEMO-CDF',
                'description' => 'Canal de distribution CODIFARM - grossiste pharmaceutique',
                'is_active' => true,
                'is_hub' => false,
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
                'is_hub' => false,
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

    private function seedWarehouses(int $instanceId, array $channels): void
    {
        $warehouseMap = [
            'saphir-plus' => ['name' => 'Entrepot Saphir Plus (Principal)', 'code' => 'WH-SAPHIR'],
            'demo-codifarm' => ['name' => 'Depot CODIFARM', 'code' => 'WH-CODIFARM'],
            'demo-pharmaplus' => ['name' => 'Depot PHARMAPLUS', 'code' => 'WH-PHARMAPLUS'],
        ];

        foreach ($channels as $slug => $channel) {
            if (!isset($warehouseMap[$slug])) {
                continue;
            }
            $wh = Warehouse::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'code' => $warehouseMap[$slug]['code']],
                ['name' => $warehouseMap[$slug]['name'], 'is_active' => true, 'instance_id' => $instanceId]
            );
            $channel->update(['warehouse_id' => $wh->id]);
        }
    }

    private function seedMembers(int $instanceId, array $channels): void
    {
        // Assign admin user (ID=1) to Saphir Plus
        if (isset($channels['saphir-plus'])) {
            ChannelUser::updateOrCreate(
                ['channel_id' => $channels['saphir-plus']->id, 'user_id' => 1],
                ['role' => 'admin']
            );
        }

        // Create manager + agent for each non-hub channel
        foreach (['demo-codifarm', 'demo-pharmaplus'] as $slug) {
            if (!isset($channels[$slug])) {
                continue;
            }
            $channel = $channels[$slug];
            $cleanName = str_replace(['[DEMO] ', '[DEMO]'], '', $channel->name);

            // Manager
            $manager = User::updateOrCreate(
                ['email' => "demo-channel-manager-{$slug}@b360.test"],
                [
                    'full_name' => "[DEMO] Gerant {$channel->name}",
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            DB::connection('system')->table('instance_user')->updateOrInsert(
                ['instance_id' => $instanceId, 'user_id' => $manager->id],
                ['status' => 'active', 'created_at' => now(), 'updated_at' => now()]
            );
            ChannelUser::updateOrCreate(
                ['channel_id' => $channel->id, 'user_id' => $manager->id],
                ['role' => 'admin']
            );

            // Agent
            $agent = User::updateOrCreate(
                ['email' => "demo-channel-agent-{$slug}@b360.test"],
                [
                    'full_name' => "[DEMO] Agent {$channel->name}",
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            DB::connection('system')->table('instance_user')->updateOrInsert(
                ['instance_id' => $instanceId, 'user_id' => $agent->id],
                ['status' => 'active', 'created_at' => now(), 'updated_at' => now()]
            );
            ChannelUser::updateOrCreate(
                ['channel_id' => $channel->id, 'user_id' => $agent->id],
                ['role' => 'operator']
            );
        }
    }

    private function seedFeatures(int $instanceId, array $channels): void
    {
        $settings = app(EshopSettingsService::class);

        // Saphir Plus: all features enabled
        if (isset($channels['saphir-plus'])) {
            $settings->setChannelFeatures([
                'portal' => true, 'shop' => true, 'orders' => true, 'online_orders' => true,
                'stock' => true, 'stock_adjustments' => true,
                'sales' => true, 'customers' => true, 'pos' => true,
                'promotions' => true, 'settings' => true, 'reports' => true, 'margins' => true,
                'finance' => true, 'hr' => true, 'support' => true,
            ], $channels['saphir-plus']->id, $instanceId);
        }

        // Channels: standard features (no finance/hr/support)
        $channelFeatures = [
            'portal' => true, 'shop' => true, 'orders' => true, 'online_orders' => true,
            'stock' => true, 'stock_adjustments' => false,
            'sales' => true, 'customers' => true, 'pos' => true,
            'promotions' => true, 'settings' => true, 'reports' => true, 'margins' => true,
            'finance' => false, 'hr' => false, 'support' => false,
        ];

        foreach (['demo-codifarm', 'demo-pharmaplus'] as $slug) {
            if (isset($channels[$slug])) {
                $settings->setChannelFeatures($channelFeatures, $channels[$slug]->id, $instanceId);
            }
        }
    }

    private function seedBranding(int $instanceId, array $channels): void
    {
        $settings = app(EshopSettingsService::class);

        $brandingData = [
            'saphir-plus' => [
                'company_name' => 'Saphir Pharma Plus',
                'company_address' => 'Abidjan, Cocody Riviera Palmeraie',
                'company_phone' => '+225 07 08 09 10 11',
                'company_email' => 'contact@saphir-pharma.ci',
                'tax_number' => 'CI-2025-SAPHIR-001',
                'invoice_header' => 'Saphir Pharma Plus - Distribution pharmaceutique',
                'invoice_footer' => 'Merci pour votre confiance. Saphir Pharma Plus.',
                'receipt_header' => 'SAPHIR PHARMA PLUS',
                'receipt_footer' => 'Merci et a bientot !',
                'currency_symbol' => 'FCFA',
            ],
            'demo-codifarm' => [
                'company_name' => 'CODIFARM Sarl',
                'company_address' => 'Abidjan, Marcory Zone 4',
                'company_phone' => '+225 01 02 03 04 05',
                'company_email' => 'contact@codifarm.ci',
                'tax_number' => 'CI-2025-CDF-002',
                'invoice_header' => 'CODIFARM - Votre grossiste pharma de confiance',
                'invoice_footer' => 'CODIFARM Sarl - RC: CI-ABJ-2024-12345',
                'receipt_header' => 'CODIFARM',
                'receipt_footer' => 'Merci pour votre achat !',
                'currency_symbol' => 'FCFA',
            ],
            'demo-pharmaplus' => [
                'company_name' => 'PharmaPlus Network',
                'company_address' => 'Bouake, Quartier Commerce',
                'company_phone' => '+225 05 06 07 08 09',
                'company_email' => 'info@pharmaplus.ci',
                'tax_number' => 'CI-2025-PHP-003',
                'invoice_header' => 'PharmaPlus - Reseau pharmacies partenaires',
                'invoice_footer' => 'PharmaPlus Network - Sante pour tous',
                'receipt_header' => 'PHARMAPLUS',
                'receipt_footer' => 'Votre sante, notre priorite.',
                'currency_symbol' => 'FCFA',
            ],
        ];

        foreach ($brandingData as $slug => $branding) {
            if (isset($channels[$slug])) {
                $settings->setForChannel('channel_branding', $branding, $channels[$slug]->id, $instanceId);
            }
        }
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

        foreach ($channels as $slug => $channel) {
            if ($slug === 'saphir-plus') {
                continue; // Hub doesn't have channel-specific pricing
            }
            foreach ($products->take(5) as $product) {
                $pght = (float) ($product->pght ?? $product->cost_price ?? $product->price);
                $salePrice = $channel->calculateSalePrice($pght);

                ChannelProductPrice::updateOrCreate(
                    ['channel_id' => $channel->id, 'product_id' => $product->id],
                    ['sale_price' => $salePrice, 'is_manual_override' => false]
                );
            }
        }
    }

    private function seedChannelMarginLogs(int $instanceId, array $channels): void
    {
        foreach ($channels as $slug => $channel) {
            if ($slug === 'saphir-plus') {
                continue; // Hub doesn't have margin logs
            }
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

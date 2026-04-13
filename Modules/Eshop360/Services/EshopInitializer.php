<?php

namespace Modules\Eshop360\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Eshop360\Models\ChannelUser;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Warehouse;

final class EshopInitializer
{
    public function __construct(
        private readonly EshopSettingsService $settings,
    ) {}

    public function isInitialized(int $instanceId): bool
    {
        return DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('is_active', true)
            ->where('is_hub', true)
            ->exists();
    }

    public function initialize(
        int $instanceId,
        array $hubData,
        array $channels,
        array $baseSettings,
        User $admin,
    ): void {
        DB::transaction(function () use ($instanceId, $hubData, $channels, $baseSettings, $admin) {
            $hub = $this->createHub($instanceId, $hubData, $admin);
            $this->createChannels($instanceId, $channels);
            $this->saveBaseSettings($instanceId, $hub, $baseSettings);
            $this->activateHierarchicalMenu($instanceId);
        });
    }

    private function createHub(int $instanceId, array $hubData, User $admin): DistributionChannel
    {
        $hub = DistributionChannel::withoutGlobalScopes()->create([
            'instance_id' => $instanceId,
            'name' => $hubData['name'],
            'slug' => Str::slug($hubData['name']),
            'code' => $hubData['code'],
            'is_active' => true,
            'is_hub' => true,
            'margin_rate' => 0,
            'buy_rate' => 0,
            'debt_share' => 0.3333,
            'channel_share' => 0.3333,
            'owner_share' => 0.3334,
            'portal_enabled' => false,
            'portal_settings' => ['theme_color' => $hubData['theme_color'] ?? '#4f46e5'],
        ]);

        $warehouse = Warehouse::withoutGlobalScopes()->create([
            'instance_id' => $instanceId,
            'name' => "Entrepot {$hubData['name']}",
            'code' => 'WH-' . strtoupper(Str::slug($hubData['code'], '-')),
            'is_active' => true,
        ]);
        $hub->update(['warehouse_id' => $warehouse->id]);

        ChannelUser::create([
            'channel_id' => $hub->id,
            'user_id' => $admin->id,
            'role' => 'manager',
        ]);

        $features = $hubData['features'] ?? $this->settings->defaults('features');
        $allTrue = array_map(fn () => true, $features);
        $this->settings->setChannelFeatures($allTrue, $hub->id, $instanceId);

        return $hub;
    }

    private function createChannels(int $instanceId, array $channels): void
    {
        foreach ($channels as $channelData) {
            $channel = DistributionChannel::withoutGlobalScopes()->create([
                'instance_id' => $instanceId,
                'name' => $channelData['name'],
                'slug' => Str::slug($channelData['name']),
                'code' => $channelData['code'] ?? strtoupper(Str::substr(Str::slug($channelData['name']), 0, 6)),
                'is_active' => true,
                'is_hub' => false,
                'margin_rate' => $channelData['margin_rate'] ?? 0.13,
                'buy_rate' => $channelData['buy_rate'] ?? 0.20,
                'debt_share' => 0.3333,
                'channel_share' => 0.3333,
                'owner_share' => 0.3334,
                'portal_enabled' => true,
                'portal_settings' => ['theme_color' => $channelData['theme_color'] ?? '#2c3e50'],
            ]);

            $warehouse = Warehouse::withoutGlobalScopes()->create([
                'instance_id' => $instanceId,
                'name' => "Depot {$channelData['name']}",
                'code' => 'WH-' . strtoupper(Str::slug($channelData['code'] ?? $channelData['name'], '-')),
                'is_active' => true,
            ]);
            $channel->update(['warehouse_id' => $warehouse->id]);

            if (!empty($channelData['features'])) {
                $this->settings->setChannelFeatures($channelData['features'], $channel->id, $instanceId);
            }
        }
    }

    private function saveBaseSettings(int $instanceId, DistributionChannel $hub, array $baseSettings): void
    {
        $invoiceData = array_intersect_key($baseSettings, array_flip([
            'company_name', 'company_address', 'company_phone', 'company_email',
            'tax_number', 'currency_symbol',
        ]));
        if (!empty($invoiceData)) {
            $invoiceDefaults = $this->settings->defaults('invoice');
            $this->settings->set('invoice', array_merge($invoiceDefaults, $invoiceData), $instanceId);
        }

        $posData = [];
        if (isset($baseSettings['pos_layout'])) {
            $posData['default_layout'] = $baseSettings['pos_layout'];
        }
        if (isset($baseSettings['payment_methods'])) {
            $posData['payment_methods'] = $baseSettings['payment_methods'];
        }
        if (!empty($posData)) {
            $posDefaults = $this->settings->defaults('pos');
            $this->settings->set('pos', array_merge($posDefaults, $posData), $instanceId);
        }

        $brandingData = array_intersect_key($baseSettings, array_flip([
            'company_name', 'company_address', 'company_phone', 'company_email',
            'tax_number', 'currency_symbol',
        ]));
        if (!empty($brandingData)) {
            $this->settings->setForChannel('channel_branding', $brandingData, $hub->id, $instanceId);
        }
    }

    private function activateHierarchicalMenu(int $instanceId): void
    {
        $general = $this->settings->get('general', $instanceId);
        $general['hierarchical_menu'] = true;
        $this->settings->set('general', $general, $instanceId);
    }
}

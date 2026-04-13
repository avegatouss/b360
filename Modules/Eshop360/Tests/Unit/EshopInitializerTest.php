<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Services\EshopInitializer;
use Modules\Eshop360\Services\EshopSettingsService;
use Modules\Eshop360\Tests\TestCase;

final class EshopInitializerTest extends TestCase
{
    public function test_is_initialized_returns_false_when_no_channels(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $initializer = app(EshopInitializer::class);

        $this->assertFalse($initializer->isInitialized($instance->id));
    }

    public function test_is_initialized_returns_true_when_hub_exists(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Hub Test',
            'slug' => 'hub-test',
            'is_active' => true,
            'is_hub' => true,
            'margin_rate' => 0,
            'buy_rate' => 0,
            'debt_share' => 0.3333,
            'channel_share' => 0.3333,
            'owner_share' => 0.3334,
        ]);

        $initializer = app(EshopInitializer::class);

        $this->assertTrue($initializer->isInitialized($instance->id));
    }

    public function test_initialize_creates_hub_channels_and_settings(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        $initializer = app(EshopInitializer::class);

        $initializer->initialize(
            instanceId: $instance->id,
            hubData: [
                'name' => 'Mon Hub',
                'code' => 'HUB',
                'theme_color' => '#4f46e5',
                'features' => ['sales' => true, 'stock' => true],
            ],
            channels: [
                [
                    'name' => 'Canal Test',
                    'code' => 'CT',
                    'theme_color' => '#2c3e50',
                    'margin_rate' => 0.10,
                    'features' => ['sales' => true, 'stock' => false],
                ],
            ],
            baseSettings: [
                'company_name' => 'Test Corp',
                'currency_symbol' => 'FCFA',
                'pos_layout' => 'layout2',
                'payment_methods' => ['cash'],
            ],
            admin: $admin,
        );

        // Hub created
        $hub = DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->where('is_hub', true)
            ->first();
        $this->assertNotNull($hub);
        $this->assertSame('Mon Hub', $hub->name);
        $this->assertSame('mon-hub', $hub->slug);

        // Hub has warehouse
        $this->assertNotNull($hub->warehouse_id);

        // Admin assigned to hub
        $this->assertTrue($hub->isUserMember($admin->id));

        // Channel created
        $channel = DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->where('is_hub', false)
            ->first();
        $this->assertNotNull($channel);
        $this->assertSame('Canal Test', $channel->name);
        $this->assertNotNull($channel->warehouse_id);

        // Hierarchical menu activated
        $settings = app(EshopSettingsService::class);
        $general = $settings->get('general', $instance->id);
        $this->assertTrue($general['hierarchical_menu']);

        // Base settings saved
        $invoice = $settings->get('invoice', $instance->id);
        $this->assertSame('Test Corp', $invoice['company_name']);

        $pos = $settings->get('pos', $instance->id);
        $this->assertSame('layout2', $pos['default_layout']);

        // Hub branding saved
        $branding = $settings->getForChannel('channel_branding', $hub->id, $instance->id);
        $this->assertSame('Test Corp', $branding['company_name']);

        // isInitialized returns true
        $this->assertTrue($initializer->isInitialized($instance->id));
    }

    public function test_initialize_works_with_zero_channels(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        $initializer = app(EshopInitializer::class);

        $initializer->initialize(
            instanceId: $instance->id,
            hubData: [
                'name' => 'Hub Solo',
                'code' => 'SOLO',
                'theme_color' => '#4f46e5',
                'features' => [],
            ],
            channels: [],
            baseSettings: [
                'company_name' => 'Solo Corp',
                'currency_symbol' => 'FCFA',
            ],
            admin: $admin,
        );

        $count = DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->count();
        $this->assertSame(1, $count);

        $this->assertTrue($initializer->isInitialized($instance->id));
    }
}

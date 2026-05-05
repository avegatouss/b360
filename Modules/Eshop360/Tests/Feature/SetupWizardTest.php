<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Tests\TestCase;

final class SetupWizardTest extends TestCase
{
    public function test_hub_step_shows_form_when_not_initialized(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        $response = $this->get("/i/{$instance->slug}/setup/hub");

        $response->assertOk();
        $response->assertViewIs('eshop360::setup.hub');
    }

    public function test_hub_step_redirects_when_already_initialized(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        DistributionChannel::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'name' => 'Existing Hub',
            'slug' => 'existing-hub',
            'is_active' => true,
            'is_hub' => true,
            'margin_rate' => 0, 'buy_rate' => 0,
            'debt_share' => 0.3333, 'channel_share' => 0.3333, 'owner_share' => 0.3334,
        ]);

        $response = $this->get("/i/{$instance->slug}/setup/hub");

        $response->assertRedirect();
    }

    public function test_full_wizard_creates_hub_and_redirects(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        // Step 1: Store hub data
        $response = $this->post("/i/{$instance->slug}/setup/hub", [
            'name' => 'Mon Hub',
            'code' => 'HUB',
            'theme_color' => '#4f46e5',
            'features' => [
                'sales' => '1', 'stock' => '1', 'customers' => '1',
                'orders' => '1', 'pos' => '1',
            ],
        ]);
        $response->assertRedirect("/i/{$instance->slug}/setup/channels");

        // Step 2: Skip channels
        $response = $this->post("/i/{$instance->slug}/setup/channels", [
            'channels' => [],
        ]);
        $response->assertRedirect("/i/{$instance->slug}/setup/settings");

        // Step 3: Store settings and finalize
        $response = $this->post("/i/{$instance->slug}/setup/settings", [
            'company_name' => 'Test SARL',
            'company_address' => '123 Rue Test',
            'company_phone' => '+225 00 00 00 00',
            'company_email' => 'test@test.ci',
            'currency_symbol' => 'FCFA',
            'pos_layout' => 'layout1',
            'payment_methods' => ['cash', 'card'],
        ]);
        $response->assertRedirect("/i/{$instance->slug}/nav");

        // Verify hub was created
        $hub = DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->where('is_hub', true)
            ->first();
        $this->assertNotNull($hub);
        $this->assertSame('Mon Hub', $hub->name);
    }

    public function test_wizard_with_channels_creates_hub_and_channels(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        // Step 1
        $this->post("/i/{$instance->slug}/setup/hub", [
            'name' => 'Hub Test',
            'code' => 'HUB',
            'theme_color' => '#4f46e5',
            'features' => ['sales' => '1', 'stock' => '1'],
        ]);

        // Step 2 with channels
        $this->post("/i/{$instance->slug}/setup/channels", [
            'channels' => [
                [
                    'name' => 'Canal A',
                    'code' => 'CA',
                    'theme_color' => '#2c3e50',
                    'margin_rate' => '0.13',
                    'features' => ['sales' => '1', 'stock' => '0'],
                ],
            ],
        ]);

        // Step 3
        $this->post("/i/{$instance->slug}/setup/settings", [
            'company_name' => 'Multi Corp',
            'currency_symbol' => 'FCFA',
            'pos_layout' => 'layout1',
            'payment_methods' => ['cash'],
        ]);

        // Verify hub + 1 channel = 2 total
        $count = DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->count();
        $this->assertSame(2, $count);

        // Channel is not hub
        $channel = DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->where('is_hub', false)
            ->first();
        $this->assertSame('Canal A', $channel->name);
    }

    public function test_settings_redirect_to_wizard_when_enabling_hierarchical_menu(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        $response = $this->put("/i/{$instance->slug}/eshop-settings/general", [
            'hierarchical_menu' => 1,
        ]);

        $response->assertRedirect("/i/{$instance->slug}/setup/hub");
    }
}

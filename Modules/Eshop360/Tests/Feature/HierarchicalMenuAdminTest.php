<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;
use Modules\Eshop360\Tests\TestCase;

final class HierarchicalMenuAdminTest extends TestCase
{
    public function test_admin_tile_visible_for_super_admin(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        DistributionChannel::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'name' => 'Hub',
            'slug' => 'hub',
            'is_active' => true,
            'is_hub' => true,
            'margin_rate' => 0, 'buy_rate' => 0,
            'debt_share' => 0.3333, 'channel_share' => 0.3333, 'owner_share' => 0.3334,
        ]);

        $response = $this->get("/i/{$instance->slug}/nav");

        $response->assertOk();
        $response->assertSee('Administration');
    }

    public function test_admin_page_shows_menu_sections(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        DistributionChannel::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'name' => 'Hub',
            'slug' => 'hub',
            'is_active' => true,
            'is_hub' => true,
            'margin_rate' => 0, 'buy_rate' => 0,
            'debt_share' => 0.3333, 'channel_share' => 0.3333, 'owner_share' => 0.3334,
        ]);

        $response = $this->get("/i/{$instance->slug}/nav/admin");

        $response->assertOk();
        $response->assertViewIs('eshop360::hierarchical-menu.admin');
    }
}

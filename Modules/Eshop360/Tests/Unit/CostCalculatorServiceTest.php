<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Services\CostCalculatorService;
use Modules\Eshop360\Tests\TestCase;

final class CostCalculatorServiceTest extends TestCase
{
    private CostCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new CostCalculatorService;
    }

    public function test_calculate_pght(): void
    {
        // PGHT = provisional * (1 + margin_rate)
        // 1000 * (1 + 0.13) = 1130
        $pght = $this->calculator->calculatePGHT(1000, 0.13);

        $this->assertSame(1130.0, $pght);
    }

    public function test_calculate_pght_with_default_margin_rate(): void
    {
        // Default margin rate is 0.13
        $pght = $this->calculator->calculatePGHT(500);

        $this->assertSame(565.0, $pght);
    }

    public function test_calculate_channel_price(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Pharmacie',
            'slug' => 'pharmacie',
            'code' => 'PH-01',
            'is_active' => true,
            'margin_rate' => 0.13,
            'buy_rate' => 0.20,
            'debt_share' => 0.33,
            'channel_share' => 0.34,
            'owner_share' => 0.33,
        ]);

        // channel_price = PGHT * (1 + buy_rate) = 1000 * 1.20 = 1200
        $channelPrice = $this->calculator->calculateChannelPrice(1000, $channel);

        $this->assertSame(1200.0, $channelPrice);
    }

    public function test_margin_level_1(): void
    {
        // marge_niv1 = prix_vente_client - prix_achat_provisoire
        $result = $this->calculator->marginLevel1(1500, 1000);

        $this->assertSame(500.0, $result['margin']);
        $this->assertSame(50.0, $result['rate']); // (500/1000)*100
    }

    public function test_margin_level_2(): void
    {
        // marge_niv2 = prix_vente_client - cout_revient_reel
        $result = $this->calculator->marginLevel2(1500, 1200);

        $this->assertSame(300.0, $result['margin']);
        $this->assertSame(25.0, $result['rate']); // (300/1200)*100
    }

    public function test_calculate_real_cost_price_with_import_costs(): void
    {
        // real_cost = factory_price + (allocated_costs / quantity)
        // = 500 + (1000 / 10) = 600
        $realCost = $this->calculator->calculateRealCostPrice(500, 1000, 10);

        $this->assertSame(600.0, $realCost);
    }

    public function test_calculate_real_cost_price_with_zero_quantity(): void
    {
        // When quantity is 0, returns factory price only
        $realCost = $this->calculator->calculateRealCostPrice(500, 1000, 0);

        $this->assertSame(500.0, $realCost);
    }
}

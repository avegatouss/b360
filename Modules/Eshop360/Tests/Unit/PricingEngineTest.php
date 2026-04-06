<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;
use Modules\Eshop360\Pricing\DTOs\PricingResult;
use Modules\Eshop360\Pricing\Engines\PricingEngine;
use Modules\Eshop360\Tests\TestCase;

/**
 * Tests for the PricingEngine v2 pipeline.
 */
final class PricingEngineTest extends TestCase
{
    private PricingEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInstanceWithAdmin();

        // Seed the pricing_rules table so the registry can filter active rules
        $this->seedPricingRules();

        // Clear the pricing rules cache (may have cached empty result before seed)
        \Illuminate\Support\Facades\Cache::flush();

        $this->engine = app(PricingEngine::class);
    }

    private function seedPricingRules(): void
    {
        $rules = [
            ['slug' => 'base_price',        'pipeline' => 'retail',  'priority' => 10, 'class_name' => 'BasePriceRule'],
            ['slug' => 'discount_product',   'pipeline' => 'retail',  'priority' => 50, 'class_name' => 'DiscountProductRule'],
            ['slug' => 'tax',               'pipeline' => 'all',     'priority' => 80, 'class_name' => 'TaxRule'],
            ['slug' => 'minimum_price',     'pipeline' => 'all',     'priority' => 90, 'class_name' => 'MinimumPriceGuard'],
            ['slug' => 'channel_base_price', 'pipeline' => 'channel', 'priority' => 10, 'class_name' => 'ChannelBasePriceRule'],
            ['slug' => 'channel_margin',     'pipeline' => 'channel', 'priority' => 30, 'class_name' => 'ChannelMarginRule'],
            ['slug' => 'wholesale_price',    'pipeline' => 'retail',  'priority' => 5,  'class_name' => 'WholesalePriceRule'],
            ['slug' => 'pharmacy_price',     'pipeline' => 'retail',  'priority' => 6,  'class_name' => 'PharmacyPriceRule'],
        ];

        foreach ($rules as $rule) {
            \Illuminate\Support\Facades\DB::table('eshop_pricing_rules')->insert(array_merge($rule, [
                'name' => ucfirst(str_replace('_', ' ', $rule['slug'])),
                'is_active' => true,
                'is_core' => true,
                'created_at' => now(),
            ]));
        }
    }

    // ──────────────────────────────────────────
    // Retail pipeline
    // ──────────────────────────────────────────

    public function test_retail_pipeline_calculates_base_price(): void
    {
        $ctx = new PricingContext(
            instanceId: 1,
            productId: 1,
            basePrice: 1000,
            costPrice: 600,
            pght: 800,
            wholesalePrice: 0,
            taxRate: 18.0,
            taxInclusive: false,
            quantity: 2,
        );

        $result = $this->engine->calculateLine($ctx);

        $this->assertInstanceOf(LineItemPrice::class, $result);
        $this->assertEquals(1000, $result->unitPrice);
        // total should include tax: (1000*2) + (1000*2*18%) = 2000 + 360 = 2360
        $this->assertGreaterThan(0, $result->total);
        $this->assertGreaterThan(0, $result->taxAmount);
    }

    public function test_retail_pipeline_with_tax_inclusive(): void
    {
        $ctx = new PricingContext(
            instanceId: 1,
            productId: 2,
            basePrice: 1180,
            costPrice: 600,
            pght: 800,
            wholesalePrice: 0,
            taxRate: 18.0,
            taxInclusive: true,
            quantity: 1,
        );

        $result = $this->engine->calculateLine($ctx);

        $this->assertEquals(1180, $result->unitPrice);
        // TTC: total should be close to unitPrice (tax already included)
        $this->assertEquals(1180, $result->total);
    }

    public function test_retail_pipeline_with_percentage_discount(): void
    {
        $ctx = new PricingContext(
            instanceId: 1,
            productId: 3,
            basePrice: 1000,
            costPrice: 600,
            pght: 800,
            wholesalePrice: 0,
            taxRate: 0,
            taxInclusive: false,
            quantity: 1,
            discountType: 'percentage',
            discountValue: 10.0,
        );

        $result = $this->engine->calculateLine($ctx);

        $this->assertEquals(100, $result->discountAmount);
        $this->assertEquals(900, $result->total);
    }

    public function test_minimum_price_guard_prevents_negative(): void
    {
        $ctx = new PricingContext(
            instanceId: 1,
            productId: 4,
            basePrice: 100,
            costPrice: 60,
            pght: 80,
            wholesalePrice: 0,
            taxRate: 0,
            taxInclusive: false,
            quantity: 1,
            discountType: 'fixed',
            discountValue: 200.0,
        );

        $result = $this->engine->calculateLine($ctx);

        $this->assertGreaterThanOrEqual(0, $result->total);
    }

    // ──────────────────────────────────────────
    // Channel pipeline
    // ──────────────────────────────────────────

    public function test_channel_pipeline_returns_result(): void
    {
        $ctx = new PricingContext(
            instanceId: 1,
            productId: 5,
            basePrice: 1000,
            costPrice: 600,
            pght: 800,
            wholesalePrice: 0,
            taxRate: 18.0,
            taxInclusive: false,
            quantity: 1,
            channelId: 1,
        );

        $result = $this->engine->calculateLine($ctx);

        $this->assertInstanceOf(LineItemPrice::class, $result);
        $this->assertGreaterThan(0, $result->total);
    }

    // ──────────────────────────────────────────
    // Order-level aggregation
    // ──────────────────────────────────────────

    public function test_calculate_order_aggregates_multiple_lines(): void
    {
        $result = $this->engine->calculateOrder(
            instanceId: 1,
            items: [
                [
                    'productId' => 1,
                    'basePrice' => 1000,
                    'costPrice' => 600,
                    'pght' => 800,
                    'wholesalePrice' => 0,
                    'taxRate' => 18.0,
                    'taxInclusive' => false,
                    'quantity' => 2,
                ],
                [
                    'productId' => 2,
                    'basePrice' => 500,
                    'costPrice' => 300,
                    'pght' => 400,
                    'wholesalePrice' => 0,
                    'taxRate' => 0,
                    'taxInclusive' => false,
                    'quantity' => 3,
                ],
            ],
        );

        $this->assertInstanceOf(PricingResult::class, $result);
        $this->assertCount(2, $result->lines);
        $this->assertGreaterThan(0, $result->grandTotal);
    }

    // ──────────────────────────────────────────
    // DTOs
    // ──────────────────────────────────────────

    public function test_pricing_context_from_product_array(): void
    {
        $productData = [
            'id' => 42,
            'instance_id' => 1,
            'price' => 1500,
            'cost_price' => 900,
            'pght' => 1200,
            'wholesale_price' => 1100,
            'tax_rate' => 18.0,
            'tax_inclusive' => false,
            'discount_type' => 'percentage',
            'discount_value' => 5.0,
        ];

        $ctx = PricingContext::fromProduct($productData, ['quantity' => 3]);

        $this->assertEquals(42, $ctx->productId);
        $this->assertEquals(1500, $ctx->basePrice);
        $this->assertEquals(1200, $ctx->pght);
        $this->assertEquals(3, $ctx->quantity);
        $this->assertEquals('percentage', $ctx->discountType);
        $this->assertFalse($ctx->isChannelSale());
    }

    public function test_cache_key_is_deterministic(): void
    {
        $ctx = new PricingContext(
            instanceId: 1,
            productId: 10,
            basePrice: 500,
            costPrice: 300,
            pght: 400,
            wholesalePrice: 0,
            taxRate: 0,
            taxInclusive: false,
            quantity: 1,
        );

        $key1 = $ctx->cacheKey();
        $key2 = $ctx->cacheKey();

        $this->assertSame($key1, $key2);
        $this->assertStringContainsString('pricing:', $key1);
    }

    public function test_line_item_price_snapshot_roundtrip(): void
    {
        $price = new LineItemPrice(
            unitPrice: 1500.0,
            discountAmount: 150.0,
            taxAmount: 243.0,
            total: 1593.0,
            appliedRules: [['slug' => 'base', 'version' => 1, 'delta' => 1500.0]],
        );

        $snapshot = $price->toSnapshot();
        $restored = LineItemPrice::fromSnapshot($snapshot);

        $this->assertEquals($price->unitPrice, $restored->unitPrice);
        $this->assertEquals($price->total, $restored->total);
        $this->assertEquals($price->appliedRules, $restored->appliedRules);
    }
}

<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\CodifarmMarginConfig;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Tests\TestCase;

final class OrderPricingAndMarginsTest extends TestCase
{
    public function test_create_from_items_uses_codifarm_price_and_creates_codifarm_margin_log(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $this->actingAs($user);
        CurrentInstance::set($instance);

        CodifarmMarginConfig::create([
            'instance_id' => $instance->id,
            'saphir_margin_rate' => 0.13,
            'codifarm_buy_rate' => 0.20,
            'debt_share' => 0.20,
            'codifarm_share' => 0.30,
            'saphir_share' => 0.50,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Masques chirurgicaux',
            'slug' => 'masques-chirurgicaux',
            'sku' => 'MASK-CH',
            'price' => 120,
            'cost_price' => 90,
            'pght' => 100,
            'sale_price_codifarm' => 150,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'box',
            'min_quantity' => 0,
            'alert_quantity' => 1,
            'is_active' => true,
        ]);

        $order = app(OrderService::class)->createFromItems([
            [
                'product_id' => $product->id,
                'quantity' => 2,
            ],
        ], [
            'instance_id' => $instance->id,
            'status' => 'completed',
            'is_codifarm' => true,
            'source' => 'manual',
            'biller_id' => $user->id,
        ], false);

        $this->assertTrue($order->is_codifarm);
        $this->assertSame(300.0, (float) $order->total);
        $this->assertSame(150.0, (float) $order->items->first()->unit_price);
        $this->assertNotNull($order->codifarmMarginLog);

        $this->assertDatabaseHas('eshop_codifarm_margin_logs', [
            'order_id' => $order->id,
            'total_margin' => 100.00,
            'debt_part' => 20.00,
            'codifarm_part' => 30.00,
            'saphir_part' => 50.00,
        ]);
    }
}

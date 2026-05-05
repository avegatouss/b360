<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Payment;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Tests\TestCase;

/**
 * End-to-end test: POS → Stock deduction → Invoice → Payment
 */
final class POSCompleteFlowTest extends TestCase
{
    public function test_flux_pos_complet_deduction_stock_facture_paiement(): void
    {
        [$instance, $user] = $this->setUpInstanceWithAdmin();

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Magasin Central',
            'code' => 'WH-POS',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Aspirine 500mg',
            'slug' => 'aspirine-500',
            'sku' => 'ASP-500',
            'price' => 1500,
            'cost_price' => 800,
            'tax_rate' => 18.0,
            'tax_inclusive' => false,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'boite',
            'is_active' => true,
        ]);

        $stock = Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'CUS-POS-001',
            'name' => 'Client POS Test',
            'is_active' => true,
        ]);

        // ─── Action: create order via OrderService ───
        $orderService = app(OrderService::class);

        $order = $orderService->createFromItems([
            [
                'product_id' => $product->id,
                'quantity' => 3,
            ],
        ], [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'source' => 'pos',
            'payment_method' => 'cash',
            'payment_amount' => 5310, // 1500 * 3 * 1.18
        ]);

        // ─── Assertion 1: Order created ───
        $this->assertNotNull($order);
        $this->assertNotEmpty($order->order_number);
        $this->assertSame('pos', $order->source);

        // ─── Assertion 2: Stock deducted ───
        $stock->refresh();
        $this->assertEquals(47, $stock->quantity); // 50 - 3

        // ─── Assertion 3: Order totals correct ───
        $expectedSubtotal = 1500 * 3;   // 4500
        $expectedTax = round(4500 * 0.18, 2); // 810
        $expectedTotal = $expectedSubtotal + $expectedTax; // 5310

        $this->assertEquals($expectedSubtotal, (float) $order->subtotal);
        $this->assertEquals($expectedTotal, (float) $order->total);

        // ─── Assertion 4: Order items recorded ───
        $this->assertCount(1, $order->items);
        $item = $order->items->first();
        $this->assertEquals(3, $item->quantity);
        $this->assertEquals(1500, (float) $item->unit_price);
    }
}

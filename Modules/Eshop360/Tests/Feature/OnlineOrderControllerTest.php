<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\OnlineOrder;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Tests\TestCase;

final class OnlineOrderControllerTest extends TestCase
{
    public function test_invoiced_status_converts_online_order_to_order_and_invoice(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'CUS-000002',
            'name' => 'Hopital Regional',
            'email' => 'hopital@example.test',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Tensiometre',
            'slug' => 'tensiometre',
            'sku' => 'TENSIO-01',
            'price' => 20,
            'cost_price' => 12,
            'tax_rate' => 10,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'piece',
            'min_quantity' => 0,
            'alert_quantity' => 1,
            'is_active' => true,
        ]);

        $onlineOrder = OnlineOrder::create([
            'instance_id' => $instance->id,
            'customer_id' => $customer->id,
            'reference' => 'ONL-TEST-001',
            'status' => 'received',
            'subtotal' => 40,
            'tax_amount' => 4,
            'total' => 44,
            'delivery_address' => 'Rue 1',
        ]);

        $onlineOrder->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 20,
            'total' => 40,
        ]);

        $response = $this->actingAs($user)->patch(route('eshop360.online-orders.status', [
            'slug' => $instance->slug,
            'onlineOrder' => $onlineOrder,
        ]), [
            'status' => 'invoiced',
        ]);

        $response->assertRedirect();

        $onlineOrder->refresh();
        $order = Order::with('invoice')->where('source', 'online')->firstOrFail();

        $this->assertSame('invoiced', $onlineOrder->status);
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame('completed', $order->status);
        $this->assertSame(44.0, (float) $order->total);
        $this->assertNotNull($order->invoice);

        $this->assertDatabaseHas('eshop_invoices', [
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'total' => 44.00,
        ]);
    }
}

<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Tests\TestCase;

final class PurchaseReceivingTest extends TestCase
{
    private function makeBasicFixtures(): array
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Entrepot Principal',
            'code' => 'WH-MAIN',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Amoxicilline 500mg',
            'slug' => 'amoxicilline-500mg',
            'sku' => 'AMOX-500',
            'price' => 2500,
            'cost_price' => 1500,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'boite',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
            'reserved_quantity' => 0,
        ]);

        $purchase = PurchaseOrder::create([
            'instance_id' => $instance->id,
            'supplier_name' => 'CIPHARM SA',
            'reference' => 'PO-RCV-0001',
            'warehouse_id' => $warehouse->id,
            'status' => 'ordered',
            'total' => 15000,
            'paid_amount' => 0,
            'due_amount' => 15000,
            'payment_status' => 'unpaid',
        ]);

        $item = $purchase->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'received_qty' => 0,
            'unit_cost' => 1500,
            'total' => 15000,
        ]);

        return compact('instance', 'user', 'warehouse', 'product', 'purchase', 'item');
    }

    public function test_full_reception_creates_stock_movement_and_sets_received(): void
    {
        ['instance' => $instance, 'user' => $user, 'warehouse' => $warehouse,
            'product' => $product, 'purchase' => $purchase, 'item' => $item] = $this->makeBasicFixtures();

        $response = $this->actingAs($user)->post(
            route('eshop360.purchases.receive', ['slug' => $instance->slug, 'purchase' => $purchase]),
            [
                'warehouse_id' => $warehouse->id,
                'items' => [
                    ['purchase_item_id' => $item->id, 'received_qty' => 10],
                ],
            ]
        );

        $response->assertRedirect(
            route('eshop360.purchases.show', ['slug' => $instance->slug, 'purchase' => $purchase])
        );

        // Stock movement created
        $this->assertDatabaseHas('eshop_stock_movements', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'type' => 'in',
        ]);

        // Stock quantity updated
        $this->assertDatabaseHas('eshop_stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]);

        // PO status = received
        $this->assertDatabaseHas('eshop_purchase_orders', [
            'id' => $purchase->id,
            'status' => 'received',
        ]);

        $this->assertNotNull($purchase->fresh()->received_at);
    }

    public function test_partial_reception_sets_status_partial(): void
    {
        ['instance' => $instance, 'user' => $user, 'warehouse' => $warehouse,
            'purchase' => $purchase, 'item' => $item] = $this->makeBasicFixtures();

        $this->actingAs($user)->post(
            route('eshop360.purchases.receive', ['slug' => $instance->slug, 'purchase' => $purchase]),
            [
                'warehouse_id' => $warehouse->id,
                'items' => [
                    ['purchase_item_id' => $item->id, 'received_qty' => 4],
                ],
            ]
        );

        $this->assertDatabaseHas('eshop_purchase_orders', [
            'id' => $purchase->id,
            'status' => 'partial',
        ]);

        $this->assertDatabaseHas('eshop_purchase_items', [
            'id' => $item->id,
            'received_qty' => 4,
        ]);
    }

    public function test_receiving_already_received_order_is_rejected(): void
    {
        ['instance' => $instance, 'user' => $user, 'warehouse' => $warehouse,
            'purchase' => $purchase, 'item' => $item] = $this->makeBasicFixtures();

        // Mark as already received
        $purchase->update(['received_at' => now(), 'status' => 'received']);

        $response = $this->actingAs($user)->post(
            route('eshop360.purchases.receive', ['slug' => $instance->slug, 'purchase' => $purchase]),
            [
                'warehouse_id' => $warehouse->id,
                'items' => [
                    ['purchase_item_id' => $item->id, 'received_qty' => 10],
                ],
            ]
        );

        $response->assertStatus(422);
    }
}

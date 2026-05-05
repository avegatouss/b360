<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;
use Modules\Eshop360\Domain\Purchasing\Models\PurchaseOrder;
use Modules\Eshop360\Domain\Purchasing\Models\PurchaseReturn;
use Modules\Eshop360\Tests\TestCase;

final class PurchaseReturnControllerTest extends TestCase
{
    public function test_store_creates_purchase_return_items_and_deducts_stock_when_received(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Depot Nord',
            'code' => 'DEP-NORD',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Vitamin C',
            'slug' => 'vitamin-c',
            'sku' => 'VITC-001',
            'price' => 15,
            'cost_price' => 8,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'box',
            'min_quantity' => 0,
            'alert_quantity' => 3,
            'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $purchase = PurchaseOrder::create([
            'instance_id' => $instance->id,
            'supplier_name' => 'Pharma Supply',
            'supplier_email' => 'supply@example.test',
            'reference' => 'PO-TEST-0001',
            'warehouse_id' => $warehouse->id,
            'status' => 'received',
            'total' => 30,
            'paid_amount' => 0,
            'due_amount' => 30,
            'payment_status' => 'unpaid',
        ]);

        $purchase->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_cost' => 15,
            'total' => 30,
        ]);

        $response = $this->actingAs($user)->post(route('eshop360.purchase-returns.store', [
            'slug' => $instance->slug,
        ]), [
            'purchase_order_id' => $purchase->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'received',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_cost' => 15,
                ],
            ],
            'notes' => 'Retour partiel test',
        ]);

        $response->assertRedirect(route('eshop360.purchase-returns.index', [
            'slug' => $instance->slug,
        ]));

        $purchaseReturn = PurchaseReturn::with('items')->firstOrFail();

        $this->assertSame('received', $purchaseReturn->status);
        $this->assertNotNull($purchaseReturn->processed_at);
        $this->assertCount(1, $purchaseReturn->items);
        $this->assertSame(30.0, (float) $purchaseReturn->total);

        $this->assertDatabaseHas('eshop_purchase_return_items', [
            'purchase_return_id' => $purchaseReturn->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('eshop_stock_movements', [
            'reference_type' => 'Modules\Eshop360\Models\PurchaseReturn',
            'reference_id' => $purchaseReturn->id,
            'type' => 'return',
            'quantity' => -2,
        ]);

        $this->assertDatabaseHas('eshop_stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 8,
        ]);
    }
}

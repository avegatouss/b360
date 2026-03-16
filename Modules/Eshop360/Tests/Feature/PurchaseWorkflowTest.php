<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Supplier;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class PurchaseWorkflowTest extends TestCase
{
    private function makePermissions(): void
    {
        foreach (['eshop.purchases.view', 'eshop.purchases.manage'] as $perm) {
            Permission::findOrCreate($perm);
        }
    }

    private function makeWarehouse($instance): Warehouse
    {
        return Warehouse::create([
            'instance_id' => $instance->id,
            'name'        => 'Depot Achats',
            'code'        => 'DEP-ACH',
            'is_active'   => true,
        ]);
    }

    private function makeProduct($instance, string $sku = 'PROD-001'): Product
    {
        return Product::create([
            'instance_id'   => $instance->id,
            'name'          => 'Produit ' . $sku,
            'slug'          => strtolower(str_replace('-', '', $sku)),
            'sku'           => $sku,
            'price'         => 2000,
            'cost_price'    => 1200,
            'tax_rate'      => 0,
            'discount_type' => 'none',
            'discount_value'=> 0,
            'unit'          => 'boite',
            'min_quantity'  => 0,
            'alert_quantity'=> 5,
            'is_active'     => true,
        ]);
    }

    public function test_can_create_purchase_order_with_supplier_relation(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $warehouse = $this->makeWarehouse($instance);
        $product = $this->makeProduct($instance, 'PROD-SUP-01');

        $supplier = Supplier::create([
            'instance_id' => $instance->id,
            'name'        => 'Pharma Fournisseur',
            'company'     => 'PharmaSupply SARL',
            'email'       => 'supplier@pharma.test',
            'is_active'   => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('eshop360.purchases.store', $instance->slug), [
                'supplier_name'      => $supplier->name,
                'supplier_email'     => $supplier->email,
                'warehouse_id'       => $warehouse->id,
                'status'             => 'pending',
                'paid_amount'        => 0,
                'items'              => [
                    [
                        'product_id' => $product->id,
                        'quantity'   => 10,
                        'unit_cost'  => 1200,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $purchase = PurchaseOrder::with('items')->firstOrFail();
        $this->assertSame($supplier->name, $purchase->supplier_name);
        $this->assertSame($supplier->email, $purchase->supplier_email);
        $this->assertCount(1, $purchase->items);
        $this->assertSame(12000.0, (float) $purchase->total);
        $this->assertSame('pending', $purchase->status);
    }

    public function test_receiving_purchase_order_updates_stock(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $warehouse = $this->makeWarehouse($instance);
        $product1 = $this->makeProduct($instance, 'STOCK-P1');
        $product2 = $this->makeProduct($instance, 'STOCK-P2');

        // Create a PO directly with status 'pending', then update to 'received'
        $purchase = PurchaseOrder::create([
            'instance_id'    => $instance->id,
            'supplier_name'  => 'Fournisseur Test',
            'reference'      => 'PO-TEST-STOCK-001',
            'warehouse_id'   => $warehouse->id,
            'status'         => 'pending',
            'total'          => 5000,
            'paid_amount'    => 0,
            'due_amount'     => 5000,
            'payment_status' => 'unpaid',
        ]);

        $purchase->items()->create([
            'product_id' => $product1->id,
            'quantity'   => 20,
            'unit_cost'  => 100,
            'total'      => 2000,
        ]);

        $purchase->items()->create([
            'product_id' => $product2->id,
            'quantity'   => 30,
            'unit_cost'  => 100,
            'total'      => 3000,
        ]);

        $response = $this->actingAs($user)
            ->put(route('eshop360.purchases.update', [$instance->slug, $purchase->id]), [
                'supplier_name' => 'Fournisseur Test',
                'warehouse_id'  => $warehouse->id,
                'status'        => 'received',
                'paid_amount'   => 5000,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_stock_movements', [
            'reference_type' => PurchaseOrder::class,
            'reference_id'   => $purchase->id,
            'type'           => 'in',
        ]);

        $this->assertDatabaseHas('eshop_stocks', [
            'product_id'   => $product1->id,
            'warehouse_id' => $warehouse->id,
            'quantity'     => 20,
        ]);

        $this->assertDatabaseHas('eshop_stocks', [
            'product_id'   => $product2->id,
            'warehouse_id' => $warehouse->id,
            'quantity'     => 30,
        ]);
    }

    public function test_creating_purchase_order_with_received_status_creates_stock_movements(): void
    {
        // Tests that creating a PO directly with status='received' injects stock.
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $warehouse = $this->makeWarehouse($instance);
        $product = $this->makeProduct($instance, 'RECV-P1');

        $response = $this->actingAs($user)
            ->post(route('eshop360.purchases.store', $instance->slug), [
                'supplier_name' => 'Fournisseur Direct',
                'warehouse_id'  => $warehouse->id,
                'status'        => 'received',
                'paid_amount'   => 500,
                'items'         => [
                    [
                        'product_id' => $product->id,
                        'quantity'   => 5,
                        'unit_cost'  => 100,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $purchase = PurchaseOrder::firstOrFail();

        $this->assertDatabaseHas('eshop_stock_movements', [
            'reference_type' => PurchaseOrder::class,
            'reference_id'   => $purchase->id,
            'type'           => 'in',
            'quantity'       => 5,
        ]);

        $this->assertDatabaseHas('eshop_stocks', [
            'product_id'   => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity'     => 5,
        ]);
    }

    public function test_full_reception_sets_status_received(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $warehouse = $this->makeWarehouse($instance);
        $product = $this->makeProduct($instance, 'FULL-RECV-01');

        $purchase = PurchaseOrder::create([
            'instance_id'    => $instance->id,
            'supplier_name'  => 'Fournisseur Full',
            'reference'      => 'PO-FULL-001',
            'warehouse_id'   => $warehouse->id,
            'status'         => 'ordered',
            'total'          => 3000,
            'paid_amount'    => 0,
            'due_amount'     => 3000,
            'payment_status' => 'unpaid',
        ]);

        $purchase->items()->create([
            'product_id' => $product->id,
            'quantity'   => 30,
            'unit_cost'  => 100,
            'total'      => 3000,
        ]);

        $response = $this->actingAs($user)
            ->put(route('eshop360.purchases.update', [$instance->slug, $purchase->id]), [
                'supplier_name' => 'Fournisseur Full',
                'warehouse_id'  => $warehouse->id,
                'status'        => 'received',
                'paid_amount'   => 3000,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_purchase_orders', [
            'id'     => $purchase->id,
            'status' => 'received',
        ]);
    }

    public function test_purchase_return_creates_stock_movement(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        foreach (['eshop.purchases.view', 'eshop.purchases.manage'] as $perm) {
            Permission::findOrCreate($perm);
        }

        $warehouse = $this->makeWarehouse($instance);
        $product = $this->makeProduct($instance, 'RET-PROD-01');

        Stock::create([
            'instance_id'      => $instance->id,
            'product_id'       => $product->id,
            'warehouse_id'     => $warehouse->id,
            'quantity'         => 15,
            'reserved_quantity'=> 0,
        ]);

        $purchase = PurchaseOrder::create([
            'instance_id'    => $instance->id,
            'supplier_name'  => 'Fournisseur Retour',
            'reference'      => 'PO-RET-001',
            'warehouse_id'   => $warehouse->id,
            'status'         => 'received',
            'total'          => 1500,
            'paid_amount'    => 1500,
            'due_amount'     => 0,
            'payment_status' => 'paid',
        ]);

        $purchase->items()->create([
            'product_id' => $product->id,
            'quantity'   => 15,
            'unit_cost'  => 100,
            'total'      => 1500,
        ]);

        $response = $this->actingAs($user)
            ->post(route('eshop360.purchase-returns.store', $instance->slug), [
                'purchase_order_id' => $purchase->id,
                'warehouse_id'      => $warehouse->id,
                'status'            => 'received',
                'items'             => [
                    [
                        'product_id' => $product->id,
                        'quantity'   => 5,
                        'unit_cost'  => 100,
                    ],
                ],
                'notes' => 'Produits defectueux',
            ]);

        $response->assertRedirect();

        // Verify a negative (return) stock movement was created
        $this->assertDatabaseHas('eshop_stock_movements', [
            'type'     => 'return',
            'quantity' => -5,
        ]);

        // Stock should have been reduced
        $this->assertDatabaseHas('eshop_stocks', [
            'product_id'   => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity'     => 10,
        ]);
    }
}

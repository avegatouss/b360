<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\ImportCost;
use Modules\Eshop360\Models\ImportOrder;
use Modules\Eshop360\Models\ImportOrderItem;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Supplier;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class ImportOrderTest extends TestCase
{
    private function makePermissions(): void
    {
        foreach (['eshop.imports.view', 'eshop.imports.manage', 'eshop.imports.costs'] as $perm) {
            Permission::findOrCreate($perm);
        }
    }

    private function makeSupplier($instance): Supplier
    {
        return Supplier::create([
            'instance_id' => $instance->id,
            'name' => 'Import Fournisseur',
            'company' => 'GlobalPharma Ltd',
            'email' => 'import@globalpharma.test',
            'is_active' => true,
        ]);
    }

    private function makeWarehouse($instance): Warehouse
    {
        return Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Entrepot Import',
            'code' => 'WH-IMP',
            'is_active' => true,
        ]);
    }

    private function makeProduct($instance, string $sku): Product
    {
        static $counter = 0;
        $counter++;

        return Product::create([
            'instance_id' => $instance->id,
            'name' => 'Import Produit '.$sku,
            'slug' => 'import-produit-'.strtolower($sku).'-'.$counter,
            'sku' => $sku.'-'.$counter,
            'price' => 5000,
            'cost_price' => 3000,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'boite',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);
    }

    public function test_can_create_import_order(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $supplier = $this->makeSupplier($instance);
        $warehouse = $this->makeWarehouse($instance);
        $product = $this->makeProduct($instance, 'IMP-001');

        $response = $this->actingAs($user)
            ->post(route('eshop360.imports.store', $instance->slug), [
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'shipping_type' => 'sea',
                'container_no' => 'CONT-2026-001',
                'ship_date' => now()->toDateString(),
                'eta' => now()->addMonths(2)->toDateString(),
                'cost_allocation_method' => 'value',
                'notes' => 'Commande import test',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 100,
                        'unit_price_factory' => 2500,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_import_orders', [
            'instance_id' => $instance->id,
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'container_no' => 'CONT-2026-001',
            'status' => 'draft',
        ]);

        $import = ImportOrder::firstOrFail();
        $this->assertCount(1, $import->items);
        $this->assertSame(100, $import->items->first()->quantity);
        $this->assertSame(2500.0, (float) $import->items->first()->unit_price_factory);
    }

    public function test_can_add_cost_to_import(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $supplier = $this->makeSupplier($instance);
        $warehouse = $this->makeWarehouse($instance);
        $product = $this->makeProduct($instance, 'COST-P');

        $import = ImportOrder::create([
            'instance_id' => $instance->id,
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'reference' => 'IMP-2026-001',
            'shipping_type' => 'sea',
            'cost_allocation_method' => 'value',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        ImportOrderItem::create([
            'import_order_id' => $import->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'unit_price_factory' => 2000,
            'total_factory' => 100000,
        ]);

        $response = $this->actingAs($user)
            ->post(route('eshop360.imports.costs.add', [$instance->slug, $import->id]), [
                'type' => 'freight',
                'description' => 'Fret maritime',
                'amount' => 500000,
                'notes' => 'Transport depuis Shanghai',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_import_costs', [
            'import_order_id' => $import->id,
            'type' => 'freight',
            'amount' => 500000,
        ]);
    }

    public function test_can_allocate_costs_to_items(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $supplier = $this->makeSupplier($instance);
        $warehouse = $this->makeWarehouse($instance);
        $product1 = $this->makeProduct($instance, 'ALLOC-P1');
        $product2 = $this->makeProduct($instance, 'ALLOC-P2');

        $import = ImportOrder::create([
            'instance_id' => $instance->id,
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'reference' => 'IMP-ALLOC-001',
            'shipping_type' => 'sea',
            'cost_allocation_method' => 'value',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        // Product 1: 100 units × 1000 = 100000 factory
        $item1 = ImportOrderItem::create([
            'import_order_id' => $import->id,
            'product_id' => $product1->id,
            'quantity' => 100,
            'unit_price_factory' => 1000,
            'total_factory' => 100000,
        ]);

        // Product 2: 100 units × 3000 = 300000 factory
        $item2 = ImportOrderItem::create([
            'import_order_id' => $import->id,
            'product_id' => $product2->id,
            'quantity' => 100,
            'unit_price_factory' => 3000,
            'total_factory' => 300000,
        ]);

        // Add a cost of 80000 — to be allocated by value (25% / 75%)
        ImportCost::create([
            'import_order_id' => $import->id,
            'type' => 'customs',
            'amount' => 80000,
        ]);

        $response = $this->actingAs($user)
            ->post(route('eshop360.imports.allocate', [$instance->slug, $import->id]));

        $response->assertRedirect();

        // By value: total=400000, item1 share=25%, item2 share=75%
        // item1 allocated_cost = 20000, cost_price_real = 1000 + (20000/100) = 1200
        // item2 allocated_cost = 60000, cost_price_real = 3000 + (60000/100) = 3600
        $this->assertDatabaseHas('eshop_import_order_items', [
            'id' => $item1->id,
            'allocated_cost' => 20000,
        ]);

        $this->assertDatabaseHas('eshop_import_order_items', [
            'id' => $item2->id,
            'allocated_cost' => 60000,
        ]);
    }

    public function test_receive_import_updates_stock(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $supplier = $this->makeSupplier($instance);
        $warehouse = $this->makeWarehouse($instance);
        $product = $this->makeProduct($instance, 'RECV-IMP');

        $import = ImportOrder::create([
            'instance_id' => $instance->id,
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'reference' => 'IMP-RECV-001',
            'shipping_type' => 'air',
            'cost_allocation_method' => 'quantity',
            'status' => 'confirmed',
            'created_by' => $user->id,
        ]);

        ImportOrderItem::create([
            'import_order_id' => $import->id,
            'product_id' => $product->id,
            'quantity' => 200,
            'unit_price_factory' => 1500,
            'total_factory' => 300000,
        ]);

        $response = $this->actingAs($user)
            ->post(route('eshop360.imports.receive', [$instance->slug, $import->id]));

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_import_orders', [
            'id' => $import->id,
            'status' => 'received',
        ]);

        $this->assertDatabaseHas('eshop_stock_movements', [
            'reference_type' => ImportOrder::class,
            'reference_id' => $import->id,
            'type' => 'in',
            'quantity' => 200,
        ]);

        $this->assertDatabaseHas('eshop_stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 200,
        ]);
    }
}

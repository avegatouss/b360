<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class WarehouseControllerTest extends TestCase
{
    private $instance;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = $this->makeRootInstance();
        CurrentInstance::set($this->instance);

        $this->user = $this->makeRootSuperAdmin($this->instance);
        TeamContext::set(0);

        foreach (['eshop.inventory.view', 'eshop.inventory.manage'] as $perm) {
            Permission::findOrCreate($perm);
        }
    }

    public function test_warehouse_index_loads(): void
    {
        Warehouse::create([
            'instance_id' => $this->instance->id,
            'name' => 'WH Main', 'code' => 'WH-MAIN', 'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('eshop360.warehouses.index', $this->instance->slug));

        $response->assertOk();
        $response->assertSee('WH Main');
    }

    public function test_warehouse_store(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('eshop360.warehouses.store', $this->instance->slug), [
                'name' => 'Nouvel Entrepot',
                'code' => 'WH-NEW',
                'city' => 'Abidjan',
                'is_active' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('eshop_warehouses', [
            'instance_id' => $this->instance->id,
            'name' => 'Nouvel Entrepot',
            'code' => 'WH-NEW',
        ]);
    }

    public function test_warehouse_store_with_nested_stores(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('eshop360.warehouses.store', $this->instance->slug), [
                'name' => 'WH Stores',
                'code' => 'WH-ST',
                'is_active' => true,
                'stores' => [
                    ['name' => 'Comptoir A', 'code' => 'ST-A'],
                    ['name' => 'Comptoir B', 'code' => 'ST-B'],
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('eshop_warehouses', ['code' => 'WH-ST']);
        $this->assertDatabaseHas('eshop_stores', ['code' => 'ST-A']);
        $this->assertDatabaseHas('eshop_stores', ['code' => 'ST-B']);
    }

    public function test_warehouse_update(): void
    {
        $warehouse = Warehouse::create([
            'instance_id' => $this->instance->id,
            'name' => 'Old Name', 'code' => 'WH-OLD', 'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('eshop360.warehouses.update', [$this->instance->slug, $warehouse->id]), [
                'name' => 'New Name',
                'code' => 'WH-OLD',
                'is_active' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('eshop_warehouses', ['id' => $warehouse->id, 'name' => 'New Name']);
    }

    public function test_warehouse_destroy_empty(): void
    {
        $warehouse = Warehouse::create([
            'instance_id' => $this->instance->id,
            'name' => 'Empty WH', 'code' => 'WH-EMPTY', 'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('eshop360.warehouses.destroy', [$this->instance->slug, $warehouse->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('eshop_warehouses', ['id' => $warehouse->id]);
    }

    public function test_warehouse_destroy_blocked_with_stock(): void
    {
        $warehouse = Warehouse::create([
            'instance_id' => $this->instance->id,
            'name' => 'Stocked WH', 'code' => 'WH-STOCK', 'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $this->instance->id,
            'name' => 'Stocked Product', 'slug' => 'stocked', 'sku' => 'SP-001',
            'price' => 100, 'cost_price' => 50, 'tax_rate' => 0,
            'discount_type' => 'none', 'discount_value' => 0,
            'unit' => 'pc', 'min_quantity' => 0, 'alert_quantity' => 0, 'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('eshop360.warehouses.destroy', [$this->instance->slug, $warehouse->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('eshop_warehouses', ['id' => $warehouse->id]);
    }

    public function test_warehouse_code_unique_validation(): void
    {
        Warehouse::create([
            'instance_id' => $this->instance->id,
            'name' => 'Existing', 'code' => 'WH-EXIST', 'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('eshop360.warehouses.store', $this->instance->slug), [
                'name' => 'Duplicate',
                'code' => 'WH-EXIST',
                'is_active' => true,
            ]);

        $response->assertSessionHasErrors('code');
    }
}

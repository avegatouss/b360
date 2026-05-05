<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\StockMovement;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class StockAdjustmentControllerTest extends TestCase
{
    private $instance;

    private $user;

    private Warehouse $warehouse;

    private Product $product;

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

        $this->warehouse = Warehouse::create([
            'instance_id' => $this->instance->id,
            'name' => 'WH Adjust', 'code' => 'WH-ADJ', 'is_active' => true,
        ]);

        $this->product = Product::create([
            'instance_id' => $this->instance->id,
            'name' => 'Produit Adjust', 'slug' => 'produit-adjust', 'sku' => 'ADJ-001',
            'price' => 1000, 'cost_price' => 500, 'tax_rate' => 0,
            'discount_type' => 'none', 'discount_value' => 0,
            'unit' => 'boite', 'min_quantity' => 1, 'alert_quantity' => 5, 'is_active' => true,
        ]);
    }

    public function test_adjustment_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('eshop360.stock-adjustments.index', $this->instance->slug));

        $response->assertOk();
    }

    public function test_store_positive_adjustment(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('eshop360.stock-adjustments.store', $this->instance->slug), [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 25,
                'reason' => 'recount',
                'notes' => 'Found extra stock',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('eshop_stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 25,
        ]);
        $this->assertDatabaseHas('eshop_stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'adjustment',
            'quantity' => 25,
        ]);
    }

    public function test_store_negative_adjustment_with_existing_stock(): void
    {
        Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('eshop360.stock-adjustments.store', $this->instance->slug), [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => -10,
                'reason' => 'damaged',
                'notes' => '10 damaged boxes',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('eshop_stocks', [
            'product_id' => $this->product->id,
            'quantity' => 40,
        ]);
    }

    public function test_store_negative_adjustment_blocked_if_insufficient(): void
    {
        Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 5,
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('eshop360.stock-adjustments.store', $this->instance->slug), [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => -20,
                'reason' => 'lost',
            ]);

        // Should fail — stock would become -15
        $response->assertStatus(500); // RuntimeException from DB transaction
        $this->assertDatabaseHas('eshop_stocks', ['quantity' => 5]); // unchanged
    }

    public function test_store_validation_rejects_zero_quantity(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('eshop360.stock-adjustments.store', $this->instance->slug), [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 0,
                'reason' => 'correction',
            ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_destroy_reverses_adjustment(): void
    {
        Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 30,
            'reserved_quantity' => 0,
        ]);

        $movement = StockMovement::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'adjustment',
            'quantity' => 10,
            'performed_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('eshop360.stock-adjustments.destroy', [$this->instance->slug, $movement->id]));

        $response->assertRedirect();
        $this->assertDatabaseHas('eshop_stocks', ['quantity' => 20]); // 30 - 10
        $this->assertDatabaseMissing('eshop_stock_movements', ['id' => $movement->id]);
    }

    public function test_destroy_blocks_non_adjustment_movement(): void
    {
        $movement = StockMovement::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'in',
            'quantity' => 10,
            'performed_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('eshop360.stock-adjustments.destroy', [$this->instance->slug, $movement->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('eshop_stock_movements', ['id' => $movement->id]);
    }
}

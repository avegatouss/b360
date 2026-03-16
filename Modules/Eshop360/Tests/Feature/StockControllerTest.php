<?php

namespace Modules\Eshop360\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\StockMovement;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class StockControllerTest extends TestCase
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

        // Create eshop permissions
        foreach (['eshop.inventory.view', 'eshop.inventory.manage'] as $perm) {
            Permission::findOrCreate($perm);
        }

        $this->warehouse = Warehouse::create([
            'instance_id' => $this->instance->id,
            'name' => 'Entrepot Test',
            'code' => 'WH-TEST',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'instance_id' => $this->instance->id,
            'name' => 'Test Produit',
            'slug' => 'test-produit',
            'sku' => 'TP-001',
            'price' => 1000,
            'cost_price' => 500,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'boite',
            'min_quantity' => 1,
            'alert_quantity' => 10,
            'is_active' => true,
        ]);
    }

    // === STOCK INDEX ===

    public function test_stock_index_loads(): void
    {
        Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('eshop360.stocks.index', $this->instance->slug));

        $response->assertOk();
        $response->assertSee('Test Produit');
    }

    public function test_stock_index_filters_by_warehouse(): void
    {
        Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('eshop360.stocks.index', $this->instance->slug) . '?warehouse_id=' . $this->warehouse->id);

        $response->assertOk();
    }

    // === STOCK UPDATE ===

    public function test_stock_update_changes_quantity_and_records_movement(): void
    {
        $stock = Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('eshop360.stocks.update', [$this->instance->slug, $stock->id]), [
                'quantity' => 45,
                'reason' => 'Correction inventaire',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('eshop_stocks', ['id' => $stock->id, 'quantity' => 45]);
        $this->assertDatabaseHas('eshop_stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'adjustment',
            'quantity' => -5,
        ]);
    }

    public function test_stock_delete_only_empty(): void
    {
        $stock = Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('eshop360.stocks.destroy', [$this->instance->slug, $stock->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error'); // Can't delete non-empty
        $this->assertDatabaseHas('eshop_stocks', ['id' => $stock->id]);
    }

    public function test_stock_delete_empty_works(): void
    {
        $stock = Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 0,
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('eshop360.stocks.destroy', [$this->instance->slug, $stock->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('eshop_stocks', ['id' => $stock->id]);
    }

    // === LOW STOCK ===

    public function test_low_stock_page_shows_products_below_alert(): void
    {
        Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 5, // below alert_quantity=10
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('eshop360.stocks.low', $this->instance->slug));

        $response->assertOk();
        $response->assertSee('Test Produit');
    }

    // === EXPIRED ===

    public function test_expired_page_shows_expired_products(): void
    {
        $this->product->update(['expiry_date' => now()->subDays(5)]);

        $response = $this->actingAs($this->user)
            ->get(route('eshop360.stocks.expired', $this->instance->slug));

        $response->assertOk();
        $response->assertSee('Test Produit');
    }

    // === EXPIRY REPORT ===

    public function test_expiry_report_loads(): void
    {
        $this->product->update(['expiry_date' => now()->addDays(15)]);

        $response = $this->actingAs($this->user)
            ->get(route('eshop360.stocks.expiry-report', $this->instance->slug) . '?days=30');

        $response->assertOk();
    }
}

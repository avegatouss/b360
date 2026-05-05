<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\StockTransfer;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class StockTransferControllerTest extends TestCase
{
    private $instance;

    private $user;

    private Warehouse $warehouseA;

    private Warehouse $warehouseB;

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

        $this->warehouseA = Warehouse::create([
            'instance_id' => $this->instance->id, 'name' => 'Source', 'code' => 'WH-SRC', 'is_active' => true,
        ]);
        $this->warehouseB = Warehouse::create([
            'instance_id' => $this->instance->id, 'name' => 'Destination', 'code' => 'WH-DST', 'is_active' => true,
        ]);

        $this->product = Product::create([
            'instance_id' => $this->instance->id,
            'name' => 'Produit Transfer', 'slug' => 'produit-transfer', 'sku' => 'TRF-001',
            'price' => 2000, 'cost_price' => 1000, 'tax_rate' => 0,
            'discount_type' => 'none', 'discount_value' => 0,
            'unit' => 'boite', 'min_quantity' => 1, 'alert_quantity' => 5, 'is_active' => true,
        ]);

        // Put stock in source warehouse
        Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'quantity' => 100,
            'reserved_quantity' => 0,
        ]);
    }

    public function test_transfer_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('eshop360.stock-transfers.index', $this->instance->slug));

        $response->assertOk();
    }

    public function test_create_transfer_reserves_source_stock(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('eshop360.stock-transfers.store', $this->instance->slug), [
                'from_warehouse_id' => $this->warehouseA->id,
                'to_warehouse_id' => $this->warehouseB->id,
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 30],
                ],
            ]);

        $response->assertRedirect();

        $sourceStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseA->id)->first();

        $this->assertSame(30, $sourceStock->reserved_quantity);
        $this->assertSame(100, $sourceStock->quantity); // Not yet deducted

        $this->assertDatabaseHas('eshop_stock_transfers', [
            'from_warehouse_id' => $this->warehouseA->id,
            'to_warehouse_id' => $this->warehouseB->id,
            'status' => 'pending',
        ]);
    }

    public function test_create_transfer_fails_on_insufficient_stock(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('eshop360.stock-transfers.store', $this->instance->slug), [
                'from_warehouse_id' => $this->warehouseA->id,
                'to_warehouse_id' => $this->warehouseB->id,
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 200], // Only 100 available
                ],
            ]);

        $response->assertStatus(500); // RuntimeException
    }

    public function test_create_transfer_rejects_same_warehouse(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('eshop360.stock-transfers.store', $this->instance->slug), [
                'from_warehouse_id' => $this->warehouseA->id,
                'to_warehouse_id' => $this->warehouseA->id,
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 10],
                ],
            ]);

        $response->assertSessionHasErrors('to_warehouse_id');
    }

    public function test_complete_transfer_moves_stock(): void
    {
        $transfer = StockTransfer::create([
            'instance_id' => $this->instance->id,
            'from_warehouse_id' => $this->warehouseA->id,
            'to_warehouse_id' => $this->warehouseB->id,
            'reference_number' => 'TRF-TEST001',
            'status' => 'pending',
            'transferred_by' => $this->user->id,
        ]);

        $transfer->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 25,
        ]);

        // Reserve stock
        Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseA->id)
            ->update(['reserved_quantity' => 25]);

        $response = $this->actingAs($this->user)
            ->post(route('eshop360.stock-transfers.complete', [$this->instance->slug, $transfer->id]));

        $response->assertRedirect();

        $sourceStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseA->id)->first();
        $destStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseB->id)->first();

        $this->assertSame(75, $sourceStock->quantity);  // 100 - 25
        $this->assertSame(0, $sourceStock->reserved_quantity); // Released
        $this->assertNotNull($destStock);
        $this->assertSame(25, $destStock->quantity);

        $transfer->refresh();
        $this->assertSame('completed', $transfer->status);
        $this->assertNotNull($transfer->completed_at);

        // Verify movements created (2 per item: out from source, in to dest)
        $this->assertDatabaseHas('eshop_stock_movements', [
            'warehouse_id' => $this->warehouseA->id,
            'type' => 'transfer',
            'quantity' => -25,
        ]);
        $this->assertDatabaseHas('eshop_stock_movements', [
            'warehouse_id' => $this->warehouseB->id,
            'type' => 'transfer',
            'quantity' => 25,
        ]);
    }

    public function test_cancel_transfer_releases_reserved(): void
    {
        $transfer = StockTransfer::create([
            'instance_id' => $this->instance->id,
            'from_warehouse_id' => $this->warehouseA->id,
            'to_warehouse_id' => $this->warehouseB->id,
            'reference_number' => 'TRF-CANCEL',
            'status' => 'pending',
            'transferred_by' => $this->user->id,
        ]);

        $transfer->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 15,
        ]);

        Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseA->id)
            ->update(['reserved_quantity' => 15]);

        $response = $this->actingAs($this->user)
            ->post(route('eshop360.stock-transfers.cancel', [$this->instance->slug, $transfer->id]));

        $response->assertRedirect();

        $sourceStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouseA->id)->first();

        $this->assertSame(0, $sourceStock->reserved_quantity);
        $this->assertSame(100, $sourceStock->quantity); // Unchanged

        $transfer->refresh();
        $this->assertSame('cancelled', $transfer->status);
    }

    public function test_complete_rejects_already_completed(): void
    {
        $transfer = StockTransfer::create([
            'instance_id' => $this->instance->id,
            'from_warehouse_id' => $this->warehouseA->id,
            'to_warehouse_id' => $this->warehouseB->id,
            'reference_number' => 'TRF-DONE',
            'status' => 'completed',
            'transferred_by' => $this->user->id,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('eshop360.stock-transfers.complete', [$this->instance->slug, $transfer->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}

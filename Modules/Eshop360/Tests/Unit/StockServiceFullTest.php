<?php

namespace Modules\Eshop360\Tests\Unit;

use InvalidArgumentException;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\StockMovement;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;
use Modules\Eshop360\Services\StockService;
use Modules\Eshop360\Tests\TestCase;

final class StockServiceFullTest extends TestCase
{
    private StockService $service;

    private $instance;

    private Warehouse $warehouse;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->instance] = $this->setUpInstanceWithAdmin();

        $this->warehouse = Warehouse::create([
            'instance_id' => $this->instance->id,
            'name' => 'Entrepot Central',
            'code' => 'WH-001',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'instance_id' => $this->instance->id,
            'name' => 'Paracetamol 500mg',
            'slug' => 'paracetamol-500',
            'sku' => 'PARA-500',
            'price' => 1500,
            'cost_price' => 850,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'boite',
            'min_quantity' => 1,
            'alert_quantity' => 10,
            'is_active' => true,
        ]);

        $this->service = app(StockService::class);
    }

    // === ADJUST STOCK ===

    public function test_adjust_stock_in_creates_stock_and_movement(): void
    {
        $movement = $this->service->adjustStock($this->product, $this->warehouse->id, 50, 'in', 'Initial stock');

        $stock = Stock::where('product_id', $this->product->id)->where('warehouse_id', $this->warehouse->id)->first();

        $this->assertNotNull($stock);
        $this->assertSame(50, $stock->quantity);
        $this->assertSame('in', $movement->type);
        $this->assertSame(50, $movement->quantity);
    }

    public function test_adjust_stock_out_decrements(): void
    {
        $this->service->adjustStock($this->product, $this->warehouse->id, 100, 'in');
        $this->service->adjustStock($this->product, $this->warehouse->id, 30, 'out', 'Sale');

        $stock = Stock::where('product_id', $this->product->id)->where('warehouse_id', $this->warehouse->id)->first();
        $this->assertSame(70, $stock->quantity);
    }

    public function test_adjust_stock_out_throws_on_insufficient(): void
    {
        $this->service->adjustStock($this->product, $this->warehouse->id, 10, 'in');

        $this->expectException(InvalidArgumentException::class);
        $this->service->adjustStock($this->product, $this->warehouse->id, 20, 'out');
    }

    public function test_adjust_stock_adjustment_positive(): void
    {
        $this->service->adjustStock($this->product, $this->warehouse->id, 50, 'in');
        $movement = $this->service->adjustStock($this->product, $this->warehouse->id, 5, 'adjustment', 'Recount +5');

        $stock = Stock::where('product_id', $this->product->id)->first();
        $this->assertSame(55, $stock->quantity);
        $this->assertSame('adjustment', $movement->type);
    }

    public function test_adjust_stock_adjustment_negative(): void
    {
        $this->service->adjustStock($this->product, $this->warehouse->id, 50, 'in');
        $this->service->adjustStock($this->product, $this->warehouse->id, -3, 'adjustment', 'Damaged items');

        $stock = Stock::where('product_id', $this->product->id)->first();
        $this->assertSame(47, $stock->quantity);
    }

    public function test_adjust_stock_return_adds_back(): void
    {
        $this->service->adjustStock($this->product, $this->warehouse->id, 100, 'in');
        $this->service->adjustStock($this->product, $this->warehouse->id, 20, 'out', 'Sold');
        $movement = $this->service->adjustStock($this->product, $this->warehouse->id, 5, 'return', 'Customer return');

        $stock = Stock::where('product_id', $this->product->id)->first();
        $this->assertSame(85, $stock->quantity);
        $this->assertSame('return', $movement->type);
    }

    public function test_adjust_stock_normalizes_legacy_purchase(): void
    {
        $movement = $this->service->adjustStock($this->product, $this->warehouse->id, 20, 'purchase');
        $this->assertSame('in', $movement->type);
        $this->assertSame(20, $movement->quantity);
    }

    public function test_adjust_stock_normalizes_legacy_sale(): void
    {
        $this->service->adjustStock($this->product, $this->warehouse->id, 50, 'in');
        $movement = $this->service->adjustStock($this->product, $this->warehouse->id, 10, 'sale');

        $this->assertSame('out', $movement->type);
        $this->assertSame(-10, $movement->quantity);
    }

    public function test_adjust_stock_invalid_type_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->adjustStock($this->product, $this->warehouse->id, 10, 'invalid_type');
    }

    public function test_adjust_stock_records_reference(): void
    {
        $movement = $this->service->adjustStock(
            $this->product, $this->warehouse->id, 10, 'in',
            'Import received', null, 'App\\Models\\ImportOrder', 42
        );

        $this->assertSame('App\\Models\\ImportOrder', $movement->reference_type);
        $this->assertSame(42, $movement->reference_id);
    }

    // === WAREHOUSE RESOLUTION ===

    public function test_resolve_warehouse_uses_provided_id(): void
    {
        $wh2 = Warehouse::create([
            'instance_id' => $this->instance->id, 'name' => 'WH2', 'code' => 'WH-002', 'is_active' => true,
        ]);

        $this->service->adjustStock($this->product, $this->warehouse->id, 50, 'in');
        $this->service->adjustStock($this->product, $wh2->id, 10, 'in');

        $stock1 = Stock::where('warehouse_id', $this->warehouse->id)->where('product_id', $this->product->id)->first();
        $stock2 = Stock::where('warehouse_id', $wh2->id)->where('product_id', $this->product->id)->first();

        $this->assertSame(50, $stock1->quantity);
        $this->assertSame(10, $stock2->quantity);
    }

    public function test_resolve_warehouse_auto_finds_existing(): void
    {
        $this->service->adjustStock($this->product, $this->warehouse->id, 30, 'in');

        // null warehouse → should resolve to existing warehouse with max stock
        $this->service->adjustStock($this->product, null, 5, 'out');

        $stock = Stock::where('product_id', $this->product->id)->first();
        $this->assertSame(25, $stock->quantity);
        $this->assertSame($this->warehouse->id, $stock->warehouse_id);
    }

    public function test_resolve_warehouse_fallback_to_first_active(): void
    {
        // No stock exists yet, null warehouse → should fallback to first active warehouse
        $movement = $this->service->adjustStock($this->product, null, 20, 'in');
        $this->assertSame($this->warehouse->id, $movement->warehouse_id);
    }

    public function test_resolve_warehouse_throws_if_none(): void
    {
        $this->warehouse->update(['is_active' => false]);
        // Deactivate and create product with no stock yet
        $product2 = Product::create([
            'instance_id' => $this->instance->id,
            'name' => 'NoWarehouse', 'slug' => 'nowarehouse', 'sku' => 'NW-001',
            'price' => 100, 'cost_price' => 50, 'tax_rate' => 0,
            'discount_type' => 'none', 'discount_value' => 0,
            'unit' => 'pc', 'min_quantity' => 0, 'alert_quantity' => 0, 'is_active' => true,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->adjustStock($product2, null, 10, 'in');
    }

    // === TRANSFER STOCK ===

    public function test_transfer_stock_creates_transfer_with_items(): void
    {
        $wh2 = Warehouse::create([
            'instance_id' => $this->instance->id, 'name' => 'WH2', 'code' => 'WH-002', 'is_active' => true,
        ]);

        $transfer = $this->service->transferStock(
            [['product_id' => $this->product->id, 'quantity' => 10]],
            $this->warehouse->id,
            $wh2->id,
        );

        $this->assertSame('pending', $transfer->status);
        $this->assertCount(1, $transfer->items);
        $this->assertSame(10, $transfer->items->first()->quantity);
        $this->assertStringStartsWith('TRF-', $transfer->reference_number);
    }

    // === AVAILABLE QUANTITY ===

    public function test_available_quantity_subtracts_reserved(): void
    {
        Stock::create([
            'instance_id' => $this->instance->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
            'reserved_quantity' => 15,
        ]);

        $available = $this->service->getAvailableQuantity($this->product, $this->warehouse->id);
        $this->assertSame(85, $available);
    }

    public function test_available_quantity_sums_all_warehouses(): void
    {
        $wh2 = Warehouse::create([
            'instance_id' => $this->instance->id, 'name' => 'WH2', 'code' => 'WH-002', 'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $this->instance->id, 'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id, 'quantity' => 50, 'reserved_quantity' => 5,
        ]);
        Stock::create([
            'instance_id' => $this->instance->id, 'product_id' => $this->product->id,
            'warehouse_id' => $wh2->id, 'quantity' => 30, 'reserved_quantity' => 0,
        ]);

        $total = $this->service->getAvailableQuantity($this->product);
        $this->assertSame(75, $total); // (50-5) + (30-0)
    }

    // === LOW STOCK & ALERTS ===

    public function test_check_low_stock_returns_below_alert(): void
    {
        // alert_quantity = 10, stock = 8 → low stock
        Stock::create([
            'instance_id' => $this->instance->id, 'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id, 'quantity' => 8, 'reserved_quantity' => 0,
        ]);

        $lowStock = $this->service->checkLowStock();
        $this->assertTrue($lowStock->contains('id', $this->product->id));
    }

    public function test_check_low_stock_excludes_sufficient(): void
    {
        // alert_quantity = 10, stock = 50 → not low
        Stock::create([
            'instance_id' => $this->instance->id, 'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id, 'quantity' => 50, 'reserved_quantity' => 0,
        ]);

        $lowStock = $this->service->checkLowStock();
        $this->assertFalse($lowStock->contains('id', $this->product->id));
    }

    public function test_check_expired_products(): void
    {
        $this->product->update(['expiry_date' => now()->subDay()]);

        $expired = $this->service->checkExpiredProducts();
        $this->assertTrue($expired->contains('id', $this->product->id));
    }

    // === MOVEMENT AUDIT TRAIL ===

    public function test_all_movements_are_recorded(): void
    {
        $this->service->adjustStock($this->product, $this->warehouse->id, 100, 'in', 'Restock');
        $this->service->adjustStock($this->product, $this->warehouse->id, 20, 'out', 'Sale');
        $this->service->adjustStock($this->product, $this->warehouse->id, -3, 'adjustment', 'Damaged');
        $this->service->adjustStock($this->product, $this->warehouse->id, 2, 'return', 'Return');

        $movements = StockMovement::where('product_id', $this->product->id)->get();

        $this->assertCount(4, $movements);
        $this->assertSame(['in', 'out', 'adjustment', 'return'], $movements->pluck('type')->all());
        $this->assertSame([100, -20, -3, 2], $movements->pluck('quantity')->all());

        // Final stock: 100 - 20 - 3 + 2 = 79
        $stock = Stock::where('product_id', $this->product->id)->first();
        $this->assertSame(79, $stock->quantity);
    }
}

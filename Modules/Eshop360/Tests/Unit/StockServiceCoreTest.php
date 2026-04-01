<?php

namespace Modules\Eshop360\Tests\Unit;

use InvalidArgumentException;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Services\StockService;
use Modules\Eshop360\Tests\TestCase;

final class StockServiceCoreTest extends TestCase
{
    private function makeWarehouse(int $instanceId, string $name = 'Central', string $code = 'WH-C'): Warehouse
    {
        return Warehouse::create([
            'instance_id' => $instanceId,
            'name' => $name,
            'code' => $code,
            'is_active' => true,
        ]);
    }

    private function makeProduct(int $instanceId, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'instance_id' => $instanceId,
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'sku' => 'TST-' . uniqid(),
            'price' => 100,
            'cost_price' => 60,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ], $overrides));
    }

    public function test_stock_entry_increases_quantity(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $warehouse = $this->makeWarehouse($instance->id);
        $product = $this->makeProduct($instance->id);
        $service = app(StockService::class);

        $movement = $service->adjustStock($product, $warehouse->id, 10, 'purchase', 'Initial stock');

        $stock = Stock::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->firstOrFail();

        $this->assertSame('in', $movement->type);
        $this->assertSame(10, $movement->quantity);
        $this->assertSame(10, $stock->quantity);
    }

    public function test_stock_exit_decreases_quantity(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $warehouse = $this->makeWarehouse($instance->id);
        $product = $this->makeProduct($instance->id);
        $service = app(StockService::class);

        $service->adjustStock($product, $warehouse->id, 20, 'purchase');
        $movement = $service->adjustStock($product, $warehouse->id, 5, 'sale', 'Customer sale');

        $stock = Stock::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->firstOrFail();

        $this->assertSame('out', $movement->type);
        $this->assertSame(-5, $movement->quantity);
        $this->assertSame(15, $stock->quantity);
    }

    public function test_stock_transfer_moves_between_warehouses(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $warehouseA = $this->makeWarehouse($instance->id, 'Depot A', 'WH-A');
        $warehouseB = $this->makeWarehouse($instance->id, 'Depot B', 'WH-B');
        $product = $this->makeProduct($instance->id);
        $service = app(StockService::class);

        $service->adjustStock($product, $warehouseA->id, 30, 'purchase');

        $transfer = $service->transferStock(
            [['product_id' => $product->id, 'quantity' => 10]],
            $warehouseA->id,
            $warehouseB->id,
        );

        $this->assertSame('pending', $transfer->status);
        $this->assertSame($warehouseA->id, $transfer->from_warehouse_id);
        $this->assertSame($warehouseB->id, $transfer->to_warehouse_id);
        $this->assertCount(1, $transfer->items);
        $this->assertSame(10, $transfer->items->first()->quantity);
    }

    public function test_stock_adjustment_updates_quantity(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $warehouse = $this->makeWarehouse($instance->id);
        $product = $this->makeProduct($instance->id);
        $service = app(StockService::class);

        $service->adjustStock($product, $warehouse->id, 50, 'purchase');
        $movement = $service->adjustStock($product, $warehouse->id, -5, 'adjustment', 'Inventory correction');

        $stock = Stock::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->firstOrFail();

        $this->assertSame('adjustment', $movement->type);
        $this->assertSame(-5, $movement->quantity);
        $this->assertSame(45, $stock->quantity);
    }

    public function test_low_stock_detection(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $warehouse = $this->makeWarehouse($instance->id);
        $service = app(StockService::class);

        $lowProduct = $this->makeProduct($instance->id, [
            'name' => 'Low Stock Item',
            'alert_quantity' => 10,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $lowProduct->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 3,
            'reserved_quantity' => 0,
        ]);

        $okProduct = $this->makeProduct($instance->id, [
            'name' => 'OK Stock Item',
            'alert_quantity' => 5,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $okProduct->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        $lowStockProducts = $service->checkLowStock($warehouse->id);

        $this->assertTrue($lowStockProducts->contains('id', $lowProduct->id));
        $this->assertFalse($lowStockProducts->contains('id', $okProduct->id));
    }
}

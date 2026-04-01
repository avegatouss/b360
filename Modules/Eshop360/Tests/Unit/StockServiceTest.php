<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Services\StockService;
use Modules\Eshop360\Tests\TestCase;

final class StockServiceTest extends TestCase
{
    public function test_adjust_stock_normalizes_legacy_types_and_resolves_existing_warehouse(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Central',
            'code' => 'WH-CENTRAL',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Ibuprofen',
            'slug' => 'ibuprofen',
            'sku' => 'IBU-001',
            'price' => 10,
            'cost_price' => 6,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'box',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        $service = app(StockService::class);

        $movementIn = $service->adjustStock($product, $warehouse->id, 5, 'purchase', 'Initial restock');
        $movementOut = $service->adjustStock($product, null, 2, 'sale', 'Customer sale');

        $stock = Stock::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->firstOrFail();

        $this->assertSame('in', $movementIn->type);
        $this->assertSame(5, $movementIn->quantity);
        $this->assertSame('out', $movementOut->type);
        $this->assertSame(-2, $movementOut->quantity);
        $this->assertSame($warehouse->id, $movementOut->warehouse_id);
        $this->assertSame(3, $stock->quantity);
    }
}

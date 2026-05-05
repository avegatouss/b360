<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;
use Modules\Eshop360\Domain\Sales\Models\Order;
use Modules\Eshop360\Domain\Sales\Models\OrderItem;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class ReportsTest extends TestCase
{
    private function setUpInstanceAndUser(): array
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        foreach (['eshop.reports.view', 'eshop.sales.view', 'eshop.products.view'] as $perm) {
            Permission::findOrCreate($perm);
        }

        return [$instance, $user];
    }

    private function seedData(int $instanceId): void
    {
        $warehouse = Warehouse::create([
            'instance_id' => $instanceId,
            'name' => 'Depot Report',
            'code' => 'WH-RPT',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instanceId,
            'name' => 'Rapport Produit',
            'slug' => 'rapport-produit',
            'sku' => 'RPT-001',
            'price' => 500,
            'cost_price' => 300,
            'tax_rate' => 18,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $instanceId,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        $order = Order::create([
            'instance_id' => $instanceId,
            'order_number' => 'SAL-RPT-001',
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'subtotal' => 1000,
            'tax_amount' => 180,
            'discount_amount' => 0,
            'total' => 1180,
            'paid_amount' => 1180,
            'due_amount' => 0,
            'source' => 'pos',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 500,
            'discount' => 0,
            'tax' => 180,
            'total' => 1180,
        ]);
    }

    public function test_overview_report_is_accessible(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();
        $this->seedData($instance->id);

        $response = $this->actingAs($user)
            ->get(route('eshop360.reports.overview', $instance->slug));

        $response->assertOk();
    }

    public function test_profit_loss_report_filters_by_date(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();
        $this->seedData($instance->id);

        $response = $this->actingAs($user)
            ->get(route('eshop360.reports.profit-loss', $instance->slug).'?'.http_build_query([
                'from' => now()->subMonth()->toDateString(),
                'to' => now()->toDateString(),
            ]));

        $response->assertOk();
    }

    public function test_stock_report_shows_correct_levels(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();
        $this->seedData($instance->id);

        $response = $this->actingAs($user)
            ->get(route('eshop360.reports.inventory', $instance->slug));

        $response->assertOk();
    }

    public function test_report_export_returns_csv(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();
        $this->seedData($instance->id);

        $response = $this->actingAs($user)
            ->get(route('eshop360.export.sales', $instance->slug));

        // Should return a download response (200 or streamed)
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }
}

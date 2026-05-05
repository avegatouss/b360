<?php

namespace Modules\Eshop360\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Tests\TestCase;

final class ApiEndpointsTest extends TestCase
{
    private function setUpApiContext(): array
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);

        return [$instance, $user];
    }

    private function apiHeaders(Instance $instance): array
    {
        return [
            'Accept' => 'application/json',
            'X-Instance-Id' => $instance->id,
        ];
    }

    public function test_api_products_list(): void
    {
        [$instance, $user] = $this->setUpApiContext();

        Product::create([
            'instance_id' => $instance->id,
            'name' => 'API Product',
            'slug' => 'api-product',
            'sku' => 'API-001',
            'price' => 500,
            'cost_price' => 300,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/eshop360/v1/products?'.http_build_query([
                'instance_id' => $instance->id,
            ]), $this->apiHeaders($instance));

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_api_create_product(): void
    {
        [$instance, $user] = $this->setUpApiContext();

        $response = $this->actingAs($user)
            ->postJson('/api/eshop360/v1/products', [
                'instance_id' => $instance->id,
                'name' => 'New API Product',
                'sku' => 'NAPI-001',
                'price' => 1000,
            ], $this->apiHeaders($instance));

        $response->assertCreated();
        $response->assertJsonFragment(['name' => 'New API Product']);

        $this->assertDatabaseHas('eshop_products', [
            'sku' => 'NAPI-001',
            'instance_id' => $instance->id,
        ]);
    }

    public function test_api_stock_movement(): void
    {
        [$instance, $user] = $this->setUpApiContext();

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'API Depot',
            'code' => 'WH-API',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Stock API Product',
            'slug' => 'stock-api-product',
            'sku' => 'SAPI-001',
            'price' => 200,
            'cost_price' => 120,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/eshop360/v1/stock/movement', [
                'instance_id' => $instance->id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 25,
                'type' => 'in',
                'notes' => 'API stock entry',
            ], $this->apiHeaders($instance));

        $response->assertOk();

        $this->assertDatabaseHas('eshop_stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 25,
        ]);
    }

    public function test_api_create_sale(): void
    {
        [$instance, $user] = $this->setUpApiContext();

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Sale Depot',
            'code' => 'WH-SALE',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Sale API Product',
            'slug' => 'sale-api-product',
            'sku' => 'SALEAPI-001',
            'price' => 300,
            'cost_price' => 180,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/eshop360/v1/sales', [
                'instance_id' => $instance->id,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'unit_price' => 300,
                    ],
                ],
                'payment_method' => 'cash',
                'paid_amount' => 600,
            ], $this->apiHeaders($instance));

        $response->assertOk();
    }

    public function test_api_rate_limiting(): void
    {
        [$instance, $user] = $this->setUpApiContext();

        // The API has throttle:60,1 middleware. We verify the endpoint works
        // and returns rate-limit headers.
        $response = $this->actingAs($user)
            ->getJson('/api/eshop360/v1/products', $this->apiHeaders($instance));

        $response->assertOk();

        // Rate limit headers should be present
        $this->assertTrue(
            $response->headers->has('X-RateLimit-Limit') || $response->headers->has('x-ratelimit-limit'),
            'Rate limit headers should be present'
        );
    }

    public function test_api_unauthenticated_returns_401(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        // Without acting as any user
        $response = $this->getJson('/api/eshop360/v1/products', $this->apiHeaders($instance));

        $response->assertStatus(401);
    }
}

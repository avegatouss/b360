<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class ProductCrudTest extends TestCase
{
    private function setUpInstanceAndUser(): array
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        foreach (['eshop.products.view', 'eshop.products.manage'] as $perm) {
            Permission::findOrCreate($perm);
        }

        return [$instance, $user];
    }

    private function makeProduct(int $instanceId, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'instance_id' => $instanceId,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TST-001',
            'price' => 500,
            'cost_price' => 300,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ], $overrides));
    }

    public function test_product_list_is_accessible(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();

        $response = $this->actingAs($user)
            ->get(route('eshop360.products.index', $instance->slug));

        $response->assertOk();
    }

    public function test_product_can_be_created(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();

        $response = $this->actingAs($user)
            ->post(route('eshop360.products.store', $instance->slug), [
                'name' => 'Nouveau Produit',
                'sku' => 'NP-001',
                'price' => 1500,
                'cost_price' => 900,
                'tax_rate' => 18,
                'discount_type' => 'none',
                'discount_value' => 0,
                'unit' => 'boite',
                'min_quantity' => 0,
                'alert_quantity' => 10,
                'is_active' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_products', [
            'sku' => 'NP-001',
            'name' => 'Nouveau Produit',
            'instance_id' => $instance->id,
        ]);
    }

    public function test_product_can_be_updated(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();

        $product = $this->makeProduct($instance->id);

        $response = $this->actingAs($user)
            ->put(route('eshop360.products.update', [$instance->slug, $product->id]), [
                'name' => 'Updated Product Name',
                'sku' => $product->sku,
                'price' => 750,
                'cost_price' => 400,
                'tax_rate' => 0,
                'discount_type' => 'none',
                'discount_value' => 0,
                'unit' => 'unit',
                'min_quantity' => 0,
                'alert_quantity' => 5,
                'is_active' => true,
            ]);

        $response->assertRedirect();

        $product->refresh();
        $this->assertSame('Updated Product Name', $product->name);
        $this->assertSame(750.0, (float) $product->price);
    }

    public function test_product_can_be_deleted(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();

        $product = $this->makeProduct($instance->id);

        $response = $this->actingAs($user)
            ->delete(route('eshop360.products.destroy', [$instance->slug, $product->id]));

        $response->assertRedirect();

        // Product uses SoftDeletes
        $this->assertSoftDeleted('eshop_products', ['id' => $product->id]);
    }

    public function test_product_search_works(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();

        $this->makeProduct($instance->id, ['name' => 'Paracetamol 500mg', 'slug' => 'paracetamol', 'sku' => 'PARA-500']);
        $this->makeProduct($instance->id, ['name' => 'Amoxicilline 250mg', 'slug' => 'amoxicilline', 'sku' => 'AMOX-250']);

        $response = $this->actingAs($user)
            ->get(route('eshop360.products.search', $instance->slug).'?q=Paracetamol');

        $response->assertOk();
    }
}

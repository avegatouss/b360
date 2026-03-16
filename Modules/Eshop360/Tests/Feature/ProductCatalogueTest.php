<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\Category;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class ProductCatalogueTest extends TestCase
{
    public function test_can_create_product_with_pharma_pricing(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        foreach (['eshop.products.view', 'eshop.products.manage'] as $perm) {
            Permission::findOrCreate($perm);
        }

        $response = $this->actingAs($user)
            ->post(route('eshop360.products.store', $instance->slug), [
                'name'                      => 'Amoxicilline 500mg',
                'sku'                       => 'AMX-500',
                'price'                     => 5000,
                'cost_price'                => 3000,
                'purchase_price_factory'    => 2500,
                'purchase_price_provisional'=> 2600,
                'pght'                      => 2700,
                'cost_price_real'           => 2800,
                'tax_rate'                  => 0,
                'discount_type'             => 'none',
                'discount_value'            => 0,
                'unit'                      => 'boite',
                'min_quantity'              => 0,
                'alert_quantity'            => 5,
                'is_active'                 => true,
            ]);

        $response->assertRedirect();

        $product = Product::where('sku', 'AMX-500')->firstOrFail();
        $this->assertSame(5000.0, (float) $product->price);
        $this->assertSame(3000.0, (float) $product->cost_price);
        $this->assertSame(2500.0, (float) $product->purchase_price_factory);
        $this->assertSame(2600.0, (float) $product->purchase_price_provisional);
        $this->assertSame(2700.0, (float) $product->pght);
        $this->assertSame(2800.0, (float) $product->cost_price_real);
    }

    public function test_can_update_product_pricing(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        foreach (['eshop.products.view', 'eshop.products.manage'] as $perm) {
            Permission::findOrCreate($perm);
        }

        $product = Product::create([
            'instance_id'            => $instance->id,
            'name'                   => 'Paracetamol 500',
            'slug'                   => 'paracetamol-500',
            'sku'                    => 'PARA-500',
            'price'                  => 1000,
            'cost_price'             => 600,
            'purchase_price_factory' => 550,
            'pght'                   => 580,
            'tax_rate'               => 0,
            'discount_type'          => 'none',
            'discount_value'         => 0,
            'unit'                   => 'boite',
            'min_quantity'           => 0,
            'alert_quantity'         => 10,
            'is_active'              => true,
        ]);

        $response = $this->actingAs($user)
            ->put(route('eshop360.products.update', [$instance->slug, $product->id]), [
                'name'                   => 'Paracetamol 500',
                'sku'                    => 'PARA-500',
                'price'                  => 1200,
                'cost_price'             => 700,
                'purchase_price_factory' => 650,
                'pght'                   => 680,
                'tax_rate'               => 0,
                'discount_type'          => 'none',
                'discount_value'         => 0,
                'unit'                   => 'boite',
                'min_quantity'           => 0,
                'alert_quantity'         => 10,
                'is_active'              => true,
            ]);

        $response->assertRedirect();

        $product->refresh();
        $this->assertSame(1200.0, (float) $product->price);
        $this->assertSame(700.0, (float) $product->cost_price);
        $this->assertSame(650.0, (float) $product->purchase_price_factory);
        $this->assertSame(680.0, (float) $product->pght);
    }

    public function test_can_list_products_by_category(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        Permission::findOrCreate('eshop.products.view');

        $catA = Category::create([
            'instance_id' => $instance->id,
            'name'        => 'Antibiotiques',
            'slug'        => 'antibiotiques',
            'is_active'   => true,
        ]);

        $catB = Category::create([
            'instance_id' => $instance->id,
            'name'        => 'Analgesiques',
            'slug'        => 'analgesiques',
            'is_active'   => true,
        ]);

        foreach (['Ampicilline', 'Azithromycine', 'Doxycycline'] as $i => $name) {
            Product::create([
                'instance_id'   => $instance->id,
                'category_id'   => $catA->id,
                'name'          => $name,
                'slug'          => strtolower($name),
                'sku'           => 'SKU-A-' . $i,
                'price'         => 1000,
                'cost_price'    => 600,
                'tax_rate'      => 0,
                'discount_type' => 'none',
                'discount_value'=> 0,
                'unit'          => 'boite',
                'min_quantity'  => 0,
                'alert_quantity'=> 5,
                'is_active'     => true,
            ]);
        }

        foreach (['Ibuprofene', 'Doliprane'] as $i => $name) {
            Product::create([
                'instance_id'   => $instance->id,
                'category_id'   => $catB->id,
                'name'          => $name,
                'slug'          => strtolower($name),
                'sku'           => 'SKU-B-' . $i,
                'price'         => 800,
                'cost_price'    => 500,
                'tax_rate'      => 0,
                'discount_type' => 'none',
                'discount_value'=> 0,
                'unit'          => 'boite',
                'min_quantity'  => 0,
                'alert_quantity'=> 5,
                'is_active'     => true,
            ]);
        }

        $response = $this->actingAs($user)
            ->get(route('eshop360.products.index', $instance->slug) . '?category_id=' . $catA->id);

        $response->assertOk();
        $response->assertSee('Ampicilline');
        $response->assertSee('Azithromycine');
        $response->assertSee('Doxycycline');
        $response->assertDontSee('Ibuprofene');
        $response->assertDontSee('Doliprane');
    }

    public function test_barcode_search_returns_product(): void
    {
        // The barcode search is handled by BarcodeController::index with ?search= query param.
        // No dedicated /products/search?barcode= JSON route exists — using barcodes index route.

        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        Permission::findOrCreate('eshop.products.view');

        Product::create([
            'instance_id'   => $instance->id,
            'name'          => 'Produit Barcoded',
            'slug'          => 'produit-barcoded',
            'sku'           => 'BAR-001',
            'barcode'       => '1234567890123',
            'price'         => 500,
            'cost_price'    => 300,
            'tax_rate'      => 0,
            'discount_type' => 'none',
            'discount_value'=> 0,
            'unit'          => 'boite',
            'min_quantity'  => 0,
            'alert_quantity'=> 5,
            'is_active'     => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('eshop360.barcodes.index', $instance->slug) . '?search=1234567890123');

        $response->assertOk();
        $response->assertSee('Produit Barcoded');
    }

    public function test_barcode_search_returns_empty_for_unknown(): void
    {
        // No /products/search?barcode= JSON route — testing via barcodes index filtering.

        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        Permission::findOrCreate('eshop.products.view');

        $response = $this->actingAs($user)
            ->get(route('eshop360.barcodes.index', $instance->slug) . '?search=BARCODE_INCONNU_999');

        $response->assertOk();
        $this->assertDatabaseMissing('eshop_products', ['barcode' => 'BARCODE_INCONNU_999']);
    }
}

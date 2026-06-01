<?php

declare(strict_types=1);

namespace Modules\Eshop360\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Modules\Eshop360\Adapters\Eloquent\EloquentCatalogReader;
use Modules\Eshop360\Adapters\Eloquent\EloquentCustomerReader;
use Modules\Eshop360\Adapters\Eloquent\EloquentPricingResolver;
use Modules\Eshop360\Contracts\Catalog\CatalogReader;
use Modules\Eshop360\Contracts\Catalog\ProductDto;
use Modules\Eshop360\Contracts\Customer\CustomerDto;
use Modules\Eshop360\Contracts\Customer\CustomerReader;
use Modules\Eshop360\Contracts\Pricing\PricingRequestDto;
use Modules\Eshop360\Contracts\Pricing\PricingResolver;
use Modules\Eshop360\Contracts\Pricing\PricingResultDto;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Tests\TestCase;

/**
 * Vérifie l'intégrité du wiring DI des 3 contrats publics ADR-021 §1
 * (P0-3bis lot) : CatalogReader, CustomerReader, PricingResolver.
 *
 * - DI binding: chaque interface résout vers son adapter Eloquent par défaut
 * - Round-trip: lecture via le contrat retourne un DTO immuable correctement
 *   mappé depuis le modèle Eloquent
 * - Multi-tenant: le scope `instance_id` est respecté dans les 3 adapters
 */
final class EshopContractsBindingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    // ─── DI bindings (smoke tests) ───────────────────────────────

    public function test_catalog_reader_binding_resolves_to_eloquent_adapter(): void
    {
        $resolved = app(CatalogReader::class);

        $this->assertInstanceOf(EloquentCatalogReader::class, $resolved);
    }

    public function test_customer_reader_binding_resolves_to_eloquent_adapter(): void
    {
        $resolved = app(CustomerReader::class);

        $this->assertInstanceOf(EloquentCustomerReader::class, $resolved);
    }

    public function test_pricing_resolver_binding_resolves_to_eloquent_adapter(): void
    {
        $resolved = app(PricingResolver::class);

        $this->assertInstanceOf(EloquentPricingResolver::class, $resolved);
    }

    // ─── CatalogReader round-trip ────────────────────────────────

    public function test_catalog_reader_finds_product_by_id_within_instance(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $product = Product::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'sku' => 'PARA-001',
            'name' => 'Paracetamol',
            'slug' => 'paracetamol',
            'price' => 1000,
            'cost_price' => 600,
            'tax_rate' => 18,
            'tax_inclusive' => false,
            'is_active' => true,
        ]);

        /** @var CatalogReader $reader */
        $reader = app(CatalogReader::class);
        $dto = $reader->findProduct($instance->id, $product->getKey());

        $this->assertInstanceOf(ProductDto::class, $dto);
        $this->assertSame((int) $product->getKey(), $dto->id);
        $this->assertSame($instance->id, $dto->instanceId);
        $this->assertSame('PARA-001', $dto->sku);
        $this->assertSame('Paracetamol', $dto->name);
        $this->assertSame(1000.0, $dto->price);
        $this->assertSame(600.0, $dto->costPrice);
        $this->assertSame(18.0, $dto->taxRate);
        $this->assertFalse($dto->taxInclusive);
        $this->assertTrue($dto->isActive);
    }

    public function test_catalog_reader_isolates_by_instance(): void
    {
        [$instanceA] = $this->setUpInstanceWithAdmin();
        $instanceB = \App\Instances\Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-'.uniqid(),
            'is_active' => true,
            'meta' => [],
        ]);

        $productA = Product::withoutGlobalScopes()->create([
            'instance_id' => $instanceA->id,
            'sku' => 'INSTANCE-A-SKU',
            'name' => 'Instance A Product',
            'slug' => 'instance-a-product',
            'price' => 500,
            'cost_price' => 300,
            'tax_rate' => 18,
            'tax_inclusive' => false,
            'is_active' => true,
        ]);

        /** @var CatalogReader $reader */
        $reader = app(CatalogReader::class);

        // Lookup with the right instance ID returns the DTO
        $this->assertNotNull($reader->findProduct($instanceA->id, $productA->getKey()));
        // Lookup with the wrong instance ID returns null (no leak)
        $this->assertNull($reader->findProduct($instanceB->id, $productA->getKey()));
        $this->assertFalse($reader->productExists($instanceB->id, $productA->getKey()));
    }

    public function test_catalog_reader_finds_product_by_sku(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        Product::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'sku' => 'UNIQUE-SKU-001',
            'name' => 'Unique Product',
            'slug' => 'unique-product',
            'price' => 100,
            'cost_price' => 50,
            'tax_rate' => 18,
            'tax_inclusive' => false,
            'is_active' => true,
        ]);

        /** @var CatalogReader $reader */
        $reader = app(CatalogReader::class);
        $dto = $reader->findProductBySku($instance->id, 'UNIQUE-SKU-001');

        $this->assertInstanceOf(ProductDto::class, $dto);
        $this->assertSame('UNIQUE-SKU-001', $dto->sku);
    }

    // ─── CustomerReader round-trip ───────────────────────────────

    public function test_customer_reader_finds_customer_by_id_within_instance(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $customer = Customer::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'code' => 'CDF-001',
            'name' => 'Client Codifarm',
            'email' => 'cdf@example.com',
            'wallet_balance' => 250.50,
            'credit_limit' => 1000,
            'is_active' => true,
        ]);

        /** @var CustomerReader $reader */
        $reader = app(CustomerReader::class);
        $dto = $reader->findCustomer($instance->id, $customer->getKey());

        $this->assertInstanceOf(CustomerDto::class, $dto);
        $this->assertSame((int) $customer->getKey(), $dto->id);
        $this->assertSame('CDF-001', $dto->code);
        $this->assertSame('Client Codifarm', $dto->name);
        $this->assertSame('cdf@example.com', $dto->email);
        $this->assertSame(250.50, $dto->walletBalance);
        $this->assertSame(1000.0, $dto->creditLimit);
        $this->assertTrue($dto->isActive);
    }

    public function test_customer_reader_isolates_by_instance(): void
    {
        [$instanceA] = $this->setUpInstanceWithAdmin();
        $instanceB = \App\Instances\Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-'.uniqid(),
            'is_active' => true,
            'meta' => [],
        ]);

        $customer = Customer::withoutGlobalScopes()->create([
            'instance_id' => $instanceA->id,
            'code' => 'A-001',
            'name' => 'Customer A',
            'is_active' => true,
        ]);

        /** @var CustomerReader $reader */
        $reader = app(CustomerReader::class);

        $this->assertNotNull($reader->findCustomer($instanceA->id, $customer->getKey()));
        $this->assertNull($reader->findCustomer($instanceB->id, $customer->getKey()));
        $this->assertFalse($reader->customerExists($instanceB->id, $customer->getKey()));
    }

    public function test_customer_reader_finds_by_code(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        Customer::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'code' => 'LOOKUP-CODE',
            'name' => 'Lookup Customer',
            'is_active' => true,
        ]);

        /** @var CustomerReader $reader */
        $reader = app(CustomerReader::class);
        $dto = $reader->findCustomerByCode($instance->id, 'LOOKUP-CODE');

        $this->assertInstanceOf(CustomerDto::class, $dto);
        $this->assertSame('LOOKUP-CODE', $dto->code);
    }

    // ─── PricingResolver round-trip ──────────────────────────────

    public function test_pricing_resolver_returns_result_dto_for_product(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $product = Product::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'sku' => 'PRICED-001',
            'name' => 'Priced Product',
            'slug' => 'priced-product',
            'price' => 1000,
            'cost_price' => 600,
            'pght' => 800,
            'wholesale_price' => 900,
            'tax_rate' => 18,
            'tax_inclusive' => false,
            'is_active' => true,
        ]);

        /** @var PricingResolver $resolver */
        $resolver = app(PricingResolver::class);
        $result = $resolver->resolveForProduct(new PricingRequestDto(
            instanceId: $instance->id,
            productId: (int) $product->getKey(),
            quantity: 1,
        ));

        $this->assertInstanceOf(PricingResultDto::class, $result);
        $this->assertSame((int) $product->getKey(), $result->productId);
        $this->assertSame(1, $result->quantity);
        // Channel-margin internals must NOT be exposed in the public DTO
        $this->assertObjectNotHasProperty('marginTotal', $result);
        $this->assertObjectNotHasProperty('partOwner', $result);
    }

    public function test_pricing_resolver_throws_for_unknown_product(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        /** @var PricingResolver $resolver */
        $resolver = app(PricingResolver::class);

        $this->expectException(\DomainException::class);
        $resolver->resolveForProduct(new PricingRequestDto(
            instanceId: $instance->id,
            productId: 999999,
        ));
    }

    public function test_pricing_resolver_isolates_by_instance(): void
    {
        [$instanceA] = $this->setUpInstanceWithAdmin();
        $instanceB = \App\Instances\Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-'.uniqid(),
            'is_active' => true,
            'meta' => [],
        ]);

        $product = Product::withoutGlobalScopes()->create([
            'instance_id' => $instanceA->id,
            'sku' => 'ISO-001',
            'name' => 'Isolated Product',
            'slug' => 'isolated-product',
            'price' => 500,
            'cost_price' => 300,
            'pght' => 400,
            'wholesale_price' => 450,
            'tax_rate' => 18,
            'tax_inclusive' => false,
            'is_active' => true,
        ]);

        /** @var PricingResolver $resolver */
        $resolver = app(PricingResolver::class);

        // Asking for instance B should NOT find the product → throws
        $this->expectException(\DomainException::class);
        $resolver->resolveForProduct(new PricingRequestDto(
            instanceId: $instanceB->id,
            productId: (int) $product->getKey(),
        ));
    }

    // ─── Structural assertion ────────────────────────────────────

    public function test_dto_classes_are_readonly(): void
    {
        $reflections = [
            new \ReflectionClass(ProductDto::class),
            new \ReflectionClass(CustomerDto::class),
            new \ReflectionClass(PricingRequestDto::class),
            new \ReflectionClass(PricingResultDto::class),
        ];

        foreach ($reflections as $r) {
            $this->assertTrue(
                $r->isReadOnly(),
                "{$r->getName()} must be `readonly` (ADR-021 §contraintes 4)."
            );
        }
    }
}

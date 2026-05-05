<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Services\InvoiceService;
use Modules\Eshop360\Tests\TestCase;

final class InvoiceServiceTest extends TestCase
{
    private InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceService = new InvoiceService;
    }

    public function test_invoice_sequential_numbering(): void
    {
        $number1 = $this->invoiceService->generateInvoiceNumber();
        $number2 = $this->invoiceService->generateInvoiceNumber();

        $this->assertStringStartsWith('INV-', $number1);
        $this->assertStringStartsWith('INV-', $number2);
        $this->assertNotSame($number1, $number2);
    }

    public function test_invoice_tax_calculation_inclusive(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        $this->actingAs($user);
        CurrentInstance::set($instance);

        // Create invoice with items that have explicit tax values (inclusive scenario)
        // unit_price=100, qty=2, tax=36 (18% of 200), total=236
        $invoice = $this->invoiceService->createFromItems([
            [
                'description' => 'Product A',
                'quantity' => 2,
                'unit_price' => 100,
                'discount' => 0,
                'tax' => 36,
                'total' => 236,
            ],
        ], [
            'instance_id' => $instance->id,
            'status' => 'unpaid',
        ]);

        $this->assertSame(200.0, (float) $invoice->subtotal);
        $this->assertSame(36.0, (float) $invoice->tax_amount);
        $this->assertSame(236.0, (float) $invoice->total);
    }

    public function test_invoice_tax_calculation_exclusive(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        $this->actingAs($user);
        CurrentInstance::set($instance);

        // Create a product with tax_rate = 20%
        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Produit Taxe',
            'slug' => 'produit-taxe',
            'sku' => 'PT-001',
            'price' => 500,
            'cost_price' => 300,
            'tax_rate' => 20,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 0,
            'is_active' => true,
        ]);

        // When tax is NOT provided, normalizeItem uses tax_rate from product
        // unit_price=500, qty=1, discount=0, tax = (500-0) * 20/100 = 100
        $invoice = $this->invoiceService->createFromItems([
            [
                'product_id' => $product->id,
                'description' => 'Produit Taxe',
                'quantity' => 1,
                'unit_price' => 500,
                'discount' => 0,
            ],
        ], [
            'instance_id' => $instance->id,
            'status' => 'unpaid',
        ]);

        $this->assertSame(500.0, (float) $invoice->subtotal);
        $this->assertSame(100.0, (float) $invoice->tax_amount);
        $this->assertSame(600.0, (float) $invoice->total);
    }

    public function test_invoice_total_with_discount(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        $this->actingAs($user);
        CurrentInstance::set($instance);

        // Line items: qty=3, unit_price=200, line_discount=50, tax=0
        // subtotal = 600, line_discount = 50, order_discount = 100
        // total = 600 + 0 - (50 + 100) = 450
        $invoice = $this->invoiceService->createFromItems([
            [
                'description' => 'Item with discount',
                'quantity' => 3,
                'unit_price' => 200,
                'discount' => 50,
                'tax' => 0,
                'total' => 550,
            ],
        ], [
            'instance_id' => $instance->id,
            'status' => 'unpaid',
            'discount_amount' => 100,
        ]);

        $this->assertSame(600.0, (float) $invoice->subtotal);
        $this->assertSame(150.0, (float) $invoice->discount_amount); // 50 line + 100 order
        $this->assertSame(450.0, (float) $invoice->total);
    }

    public function test_mark_as_paid_updates_status(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        $this->actingAs($user);
        CurrentInstance::set($instance);

        $invoice = $this->invoiceService->createFromItems([
            [
                'description' => 'Service',
                'quantity' => 1,
                'unit_price' => 1000,
                'discount' => 0,
                'tax' => 0,
                'total' => 1000,
            ],
        ], [
            'instance_id' => $instance->id,
            'status' => 'unpaid',
        ]);

        $this->assertSame('unpaid', $invoice->status);

        $this->invoiceService->markAsPaid($invoice, 1000, 'cash');

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame(1000.0, (float) $invoice->paid_amount);
        $this->assertSame(0.0, (float) $invoice->due_amount);
    }
}

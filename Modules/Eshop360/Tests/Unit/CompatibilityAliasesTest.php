<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Eshop360\Models\InstallmentPayment;
use Modules\Eshop360\Models\InstallmentPlan;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\InvoiceItem;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Tests\TestCase;

final class CompatibilityAliasesTest extends TestCase
{
    public function test_order_and_invoice_reference_aliases_follow_number_fields(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $order = Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'ORD-20260315-TEST01',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 100,
            'tax_amount' => 10,
            'discount_amount' => 0,
            'shipping_amount' => 5,
            'total' => 115,
            'paid_amount' => 0,
            'due_amount' => 115,
            'source' => 'manual',
        ]);

        $invoice = Invoice::create([
            'instance_id' => $instance->id,
            'order_id' => $order->id,
            'invoice_number' => 'INV-20260315-TEST01',
            'status' => 'unpaid',
            'subtotal' => 100,
            'tax_amount' => 10,
            'discount_amount' => 0,
            'total' => 115,
            'paid_amount' => 0,
            'due_amount' => 115,
        ]);

        $this->assertSame('ORD-20260315-TEST01', $order->reference);
        $this->assertSame('INV-20260315-TEST01', $invoice->reference);
        $this->assertSame(5.0, $invoice->shipping_amount);
    }

    public function test_installment_plan_compatibility_accessors_compute_expected_state(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $order = Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'ORD-20260315-PLAN01',
            'status' => 'pending',
            'payment_status' => 'partial',
            'subtotal' => 60,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total' => 60,
            'paid_amount' => 20,
            'due_amount' => 40,
            'source' => 'manual',
        ]);

        $plan = InstallmentPlan::create([
            'instance_id' => $instance->id,
            'order_id' => $order->id,
            'total' => 60,
            'installments_count' => 3,
            'frequency' => 'monthly',
            'status' => 'active',
        ]);

        $paidPayment = InstallmentPayment::create([
            'plan_id' => $plan->id,
            'due_date' => now()->subDays(5),
            'amount' => 20,
            'paid_at' => now()->subDays(2),
            'status' => 'paid',
        ]);

        InstallmentPayment::create([
            'plan_id' => $plan->id,
            'due_date' => now()->addDays(2),
            'amount' => 20,
            'status' => 'pending',
        ]);

        InstallmentPayment::create([
            'plan_id' => $plan->id,
            'due_date' => now()->addDays(10),
            'amount' => 20,
            'status' => 'pending',
        ]);

        $plan->load('payments');

        $this->assertSame(sprintf('INST-%06d', $plan->id), $plan->reference);
        $this->assertSame(60.0, $plan->total_amount);
        $this->assertSame(20.0, $plan->paid_amount);
        $this->assertSame(40.0, $plan->remaining_amount);
        $this->assertSame(1, $plan->paid_installments_count);
        $this->assertSame(3, $plan->total_installments_count);
        $this->assertCount(3, $plan->installments);
        $this->assertTrue($paidPayment->installmentPlan->is($plan));
        $this->assertSame(20.0, $paidPayment->paid_amount);
        $this->assertSame(now()->addDays(2)->format('Y-m-d'), $plan->next_due_date?->format('Y-m-d'));
    }

    public function test_item_accessors_expose_description_and_tax_rate_compatibility(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $order = Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'ORD-20260315-ITEM01',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 100,
            'tax_amount' => 20,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total' => 120,
            'paid_amount' => 0,
            'due_amount' => 120,
            'source' => 'manual',
        ]);

        $invoice = Invoice::create([
            'instance_id' => $instance->id,
            'order_id' => $order->id,
            'invoice_number' => 'INV-20260315-ITEM01',
            'status' => 'unpaid',
            'subtotal' => 100,
            'tax_amount' => 20,
            'discount_amount' => 0,
            'total' => 120,
            'paid_amount' => 0,
            'due_amount' => 120,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Paracetamol',
            'slug' => 'paracetamol',
            'sku' => 'PARA-001',
            'price' => 50,
            'cost_price' => 40,
            'tax_rate' => 20,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'box',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Paracetamol',
            'quantity' => 2,
            'unit_price' => 50,
            'discount' => 0,
            'tax' => 20,
            'total' => 120,
        ]);

        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'description' => 'Paracetamol',
            'quantity' => 2,
            'unit_price' => 50,
            'discount' => 0,
            'tax' => 20,
            'total' => 120,
        ]);

        $this->assertSame('Paracetamol', $orderItem->description);
        $this->assertSame(20.0, $orderItem->tax_rate);
        $this->assertSame(20.0, $invoiceItem->tax_rate);
    }
}

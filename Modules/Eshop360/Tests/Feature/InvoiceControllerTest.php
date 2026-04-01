<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Tests\TestCase;

final class InvoiceControllerTest extends TestCase
{
    public function test_store_creates_invoice_and_update_records_payment_delta(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'CUS-000001',
            'name' => 'Clinique Sainte Marie',
            'email' => 'client@example.test',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Kit diagnostic',
            'slug' => 'kit-diagnostic',
            'sku' => 'KIT-DIAG',
            'price' => 25,
            'cost_price' => 10,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'piece',
            'min_quantity' => 0,
            'alert_quantity' => 1,
            'is_active' => true,
        ]);

        $storeResponse = $this->actingAs($user)->post(route('eshop360.invoices.store', [
            'slug' => $instance->slug,
        ]), [
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(15)->toDateString(),
            'notes' => 'Facture test',
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => 'Kit diagnostic complet',
                    'quantity' => 2,
                    'unit_price' => 25,
                    'discount' => 5,
                    'tax' => 2.5,
                ],
            ],
        ]);

        $invoice = Invoice::with('payments')->firstOrFail();

        $storeResponse->assertRedirect(route('eshop360.invoices.show', [
            'slug' => $instance->slug,
            'invoice' => $invoice,
        ]));

        $this->assertSame('draft', $invoice->status);
        $this->assertSame(50.0, (float) $invoice->subtotal);
        $this->assertSame(2.5, (float) $invoice->tax_amount);
        $this->assertSame(5.0, (float) $invoice->discount_amount);
        $this->assertSame(47.5, (float) $invoice->total);
        $this->assertSame(47.5, (float) $invoice->due_amount);

        $updateResponse = $this->actingAs($user)->put(route('eshop360.invoices.update', [
            'slug' => $instance->slug,
            'invoice' => $invoice,
        ]), [
            'paid_amount' => 20,
            'notes' => 'Acompte recu',
        ]);

        $updateResponse->assertRedirect(route('eshop360.invoices.show', [
            'slug' => $instance->slug,
            'invoice' => $invoice,
        ]));

        $invoice->refresh();

        $this->assertSame(20.0, (float) $invoice->paid_amount);
        $this->assertSame(27.5, (float) $invoice->due_amount);
        $this->assertSame('partial', $invoice->status);

        $this->assertDatabaseHas('eshop_payments', [
            'payable_type' => Invoice::class,
            'payable_id' => $invoice->id,
            'amount' => 20.00,
            'method' => 'cash',
        ]);
    }
}

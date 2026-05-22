<?php

namespace Modules\Billing\Tests\Feature;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Tests\TestCase;

final class InvoiceControllerTest extends TestCase
{
    public function test_index_lists_invoices(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        Invoice::create([
            'instance_id' => $root->id,
            'number' => 'B360-INV-2026-00001',
            'amount' => 29.99,
            'tax' => 0,
            'total' => 29.99,
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/billing/invoices")
            ->assertOk()
            ->assertSee('B360-INV-2026-00001');
    }

    public function test_show_invoice(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $invoice = Invoice::create([
            'instance_id' => $root->id,
            'number' => 'B360-INV-2026-00002',
            'amount' => 49.99,
            'tax' => 0,
            'total' => 49.99,
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/billing/invoices/{$invoice->id}")
            ->assertOk()
            ->assertSee('B360-INV-2026-00002');
    }

    public function test_pay_records_payment(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $invoice = Invoice::create([
            'instance_id' => $root->id,
            'number' => 'B360-INV-2026-00003',
            'amount' => 10,
            'tax' => 0,
            'total' => 10,
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $this->actingAs($user)
            ->post("/i/{$root->slug}/billing/invoices/{$invoice->id}/pay", [
                'method' => 'manual',
                'reference' => 'TEST-REF',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'method' => 'manual',
            'reference' => 'TEST-REF',
            'status' => 'completed',
        ]);

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
    }

    public function test_pay_requires_valid_method(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $invoice = Invoice::create([
            'instance_id' => $root->id,
            'number' => 'B360-INV-2026-00004',
            'amount' => 10,
            'tax' => 0,
            'total' => 10,
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $this->actingAs($user)
            ->post("/i/{$root->slug}/billing/invoices/{$invoice->id}/pay", [
                'method' => 'bitcoin',
            ])
            ->assertSessionHasErrors('method');
    }
}

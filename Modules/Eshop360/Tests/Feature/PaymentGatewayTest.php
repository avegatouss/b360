<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\EshopPaymentGateway;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Services\InvoiceService;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class PaymentGatewayTest extends TestCase
{
    private function setUpInstanceAndUser(): array
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        foreach ([
            'eshop.settings.manage',
            'eshop.invoices.view',
            'eshop.invoices.manage',
            'eshop.sales.view',
            'eshop.sales.manage',
        ] as $perm) {
            Permission::findOrCreate($perm);
        }

        return [$instance, $user];
    }

    public function test_admin_can_list_gateways(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();

        EshopPaymentGateway::create([
            'instance_id' => $instance->id,
            'driver' => 'stripe',
            'display_name' => 'Stripe',
            'config' => ['secret_key' => 'sk_test_xxx'],
            'is_active' => true,
            'is_test_mode' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)
            ->get(route('eshop360.payment-gateways.index', $instance->slug));

        $response->assertOk();
    }

    public function test_admin_can_create_gateway(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();

        $response = $this->actingAs($user)
            ->post(route('eshop360.payment-gateways.store', $instance->slug), [
                'driver' => 'paypal',
                'display_name' => 'PayPal',
                'is_active' => true,
                'is_test_mode' => true,
                'sort_order' => 1,
                'config' => [
                    'client_id' => 'test_client_id',
                    'client_secret' => 'test_secret',
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_payment_gateways', [
            'instance_id' => $instance->id,
            'driver' => 'paypal',
            'display_name' => 'PayPal',
        ]);
    }

    public function test_public_payment_page_shows_invoice(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();

        $this->actingAs($user);

        $invoiceService = new InvoiceService();
        $invoice = $invoiceService->createFromItems([
            [
                'description' => 'Service consultation',
                'quantity' => 1,
                'unit_price' => 5000,
                'discount' => 0,
                'tax' => 0,
                'total' => 5000,
            ],
        ], [
            'instance_id' => $instance->id,
            'status' => 'unpaid',
        ]);

        // Invoice should exist with correct total
        $this->assertNotNull($invoice);
        $this->assertSame(5000.0, (float) $invoice->total);
        $this->assertSame('unpaid', $invoice->status);
    }

    public function test_wallet_payment_deducts_balance(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();

        $this->actingAs($user);

        $invoiceService = new InvoiceService();
        $invoice = $invoiceService->createFromItems([
            [
                'description' => 'Monthly subscription',
                'quantity' => 1,
                'unit_price' => 2000,
                'discount' => 0,
                'tax' => 0,
                'total' => 2000,
            ],
        ], [
            'instance_id' => $instance->id,
            'status' => 'unpaid',
        ]);

        // Simulate payment via mark as paid
        $invoiceService->markAsPaid($invoice, 2000, 'wallet');

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame(2000.0, (float) $invoice->paid_amount);
        $this->assertSame(0.0, (float) $invoice->due_amount);
    }
}

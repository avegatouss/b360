<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature\Workflow;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Menuiserie360\Domain\Finance\Actions\RecordPaymentAction;
use Modules\Menuiserie360\Domain\Finance\Enums\MethodePaiement;
use Modules\Menuiserie360\Domain\Finance\Enums\StatutFacture;
use Modules\Menuiserie360\Domain\Finance\Enums\TypeFacture;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiseriePayment;
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P3-2 — Tests RecordPaymentAction + endpoint HTTP /factures/{}/payments.
 */
final class RecordPaymentTest extends TestCase
{
    private Instance $instance;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->instance = $this->makeRootInstance();
        $this->superAdmin = $this->makeRootSuperAdmin($this->instance);
        CurrentInstance::set($this->instance);
        TeamContext::set(0);

        DB::connection('system')->table('instance_user')->updateOrInsert(
            ['instance_id' => $this->instance->id, 'user_id' => $this->superAdmin->id],
            ['status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        );
    }

    public function test_partial_payment_marks_invoice_paid_partial(): void
    {
        $invoice = $this->makeInvoice(amountTtc: 1_000_000);

        app(RecordPaymentAction::class)->execute($invoice, [
            'amount' => 400_000,
            'method' => MethodePaiement::ESPECES->value,
        ]);

        $invoice->refresh();
        $this->assertSame('400000.00', (string) $invoice->getAttribute('paid_amount'));
        $this->assertSame(StatutFacture::PAID_PARTIAL->value, $invoice->getAttribute('status'));
    }

    public function test_full_payment_marks_invoice_paid_full(): void
    {
        $invoice = $this->makeInvoice(amountTtc: 500_000);

        app(RecordPaymentAction::class)->execute($invoice, [
            'amount' => 500_000,
            'method' => MethodePaiement::MOBILE_MONEY->value,
            'gateway' => 'cinetpay',
            'transaction_ref' => 'CP-XYZ-123',
        ]);

        $invoice->refresh();
        $this->assertSame(StatutFacture::PAID_FULL->value, $invoice->getAttribute('status'));
    }

    public function test_idempotent_webhook_replay_does_not_double_credit(): void
    {
        $invoice = $this->makeInvoice(amountTtc: 1_000_000);
        $key = 'wh-cinetpay-evt-9876';

        $action = app(RecordPaymentAction::class);
        $first = $action->execute($invoice, [
            'amount' => 250_000,
            'method' => MethodePaiement::MOBILE_MONEY->value,
            'gateway' => 'cinetpay',
            'idempotency_key' => $key,
        ]);

        $second = $action->execute($invoice, [
            'amount' => 250_000,
            'method' => MethodePaiement::MOBILE_MONEY->value,
            'gateway' => 'cinetpay',
            'idempotency_key' => $key,
        ]);

        $this->assertSame($first->getKey(), $second->getKey());
        $invoice->refresh();
        $this->assertSame('250000.00', (string) $invoice->getAttribute('paid_amount'));
        $this->assertSame(1, MenuiseriePayment::query()->where('payable_id', $invoice->getKey())->count());
    }

    public function test_super_admin_can_record_payment_via_http_endpoint(): void
    {
        $invoice = $this->makeInvoice(amountTtc: 200_000);

        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.factures.payments.store', [
                'slug' => $this->instance->getAttribute('slug'),
                'invoice' => $invoice->getKey(),
            ]), [
                'amount' => 200_000,
                'method' => MethodePaiement::MOBILE_MONEY->value,
                'gateway' => 'orange_money',
                'transaction_ref' => 'OM-1234',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame(StatutFacture::PAID_FULL->value, $invoice->getAttribute('status'));
    }

    private function makeInvoice(float $amountTtc): MenuiserieInvoice
    {
        return MenuiserieInvoice::create([
            'instance_id' => $this->instance->id,
            'invoice_number' => 'MNU-FAC-P3-PAY-'.uniqid(),
            'client_id' => 1,
            'type' => TypeFacture::ACOMPTE->value,
            'amount_ht' => round($amountTtc / 1.18, 2),
            'tax_rate' => 0.18,
            'amount_tva' => round($amountTtc - ($amountTtc / 1.18), 2),
            'amount_ttc' => $amountTtc,
            'paid_amount' => 0,
            'status' => StatutFacture::ISSUED->value,
            'issued_at' => now(),
        ]);
    }
}

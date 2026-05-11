<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature\Reporting;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Menuiserie360\Domain\Finance\Enums\StatutFacture;
use Modules\Menuiserie360\Domain\Finance\Enums\TypeFacture;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiseriePayment;
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P3-4 — Tests journal ventes & paiements (filtrage période + KPIs).
 */
final class JournalVentesTest extends TestCase
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

    public function test_journal_lists_invoices_and_payments_with_period_filter(): void
    {
        $inv = MenuiserieInvoice::create([
            'instance_id' => $this->instance->id,
            'invoice_number' => 'INV-JOURNAL-1',
            'client_id' => 1,
            'type' => TypeFacture::ACOMPTE->value,
            'amount_ht' => 100_000,
            'tax_rate' => 0.18,
            'amount_tva' => 18_000,
            'amount_ttc' => 118_000,
            'paid_amount' => 50_000,
            'status' => StatutFacture::PAID_PARTIAL->value,
            'issued_at' => '2026-04-15 10:00:00',
        ]);

        MenuiseriePayment::create([
            'instance_id' => $this->instance->id,
            'payable_type' => 'mnu.invoice',
            'payable_id' => $inv->getKey(),
            'amount' => 50_000,
            'method' => 'mobile_money',
            'gateway' => 'cinetpay',
            'status' => 'succeeded',
            'paid_at' => '2026-04-16 14:30:00',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.reporting.journal', [
                'slug' => $this->instance->getAttribute('slug'),
                'from' => '2026-04-01',
                'to' => '2026-04-30',
            ]));

        $response->assertOk()
            ->assertSee('INV-JOURNAL-1')
            ->assertSee('cinetpay');
    }

    public function test_journal_excludes_records_outside_period(): void
    {
        MenuiserieInvoice::create([
            'instance_id' => $this->instance->id,
            'invoice_number' => 'INV-OUT-SCOPE',
            'client_id' => 1,
            'type' => TypeFacture::ACOMPTE->value,
            'amount_ht' => 100_000,
            'tax_rate' => 0.18,
            'amount_tva' => 18_000,
            'amount_ttc' => 118_000,
            'paid_amount' => 0,
            'status' => StatutFacture::ISSUED->value,
            'issued_at' => '2026-01-15 10:00:00',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.reporting.journal', [
                'slug' => $this->instance->getAttribute('slug'),
                'from' => '2026-04-01',
                'to' => '2026-04-30',
            ]))
            ->assertOk()
            ->assertDontSee('INV-OUT-SCOPE');
    }
}

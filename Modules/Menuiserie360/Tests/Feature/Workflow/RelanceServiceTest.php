<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature\Workflow;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Menuiserie360\Domain\Finance\Enums\StatutFacture;
use Modules\Menuiserie360\Domain\Finance\Enums\TypeFacture;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Finance\Services\RelanceService;
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P3-7 — Tests RelanceService (filtrage, cooldown, mise à jour).
 */
final class RelanceServiceTest extends TestCase
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

    public function test_relance_targets_overdue_unpaid_invoices(): void
    {
        $overdue = $this->makeInvoice(
            issuedAt: now()->subDays(45)->toDateTimeString(),
            status: StatutFacture::ISSUED,
        );
        $this->makeInvoice(
            issuedAt: now()->subDays(10)->toDateTimeString(),
            status: StatutFacture::ISSUED,
        );
        $this->makeInvoice(
            issuedAt: now()->subDays(60)->toDateTimeString(),
            status: StatutFacture::PAID_FULL,
        );

        $relanced = app(RelanceService::class)->relancerFacturesImpayees(
            instanceId: $this->instance->id,
        );

        $this->assertSame([$overdue->getKey()], $relanced);

        $overdue->refresh();
        $this->assertSame(1, $overdue->getAttribute('relance_count'));
        $this->assertNotNull($overdue->getAttribute('last_relance_at'));
    }

    public function test_relance_respects_cooldown(): void
    {
        $invoice = $this->makeInvoice(
            issuedAt: now()->subDays(45)->toDateTimeString(),
            status: StatutFacture::PAID_PARTIAL,
        );
        $invoice->setAttribute('last_relance_at', now()->subDays(3))->save();

        $relanced = app(RelanceService::class)->relancerFacturesImpayees(
            instanceId: $this->instance->id,
        );

        $this->assertSame([], $relanced);
    }

    public function test_relance_runs_again_after_cooldown(): void
    {
        $invoice = $this->makeInvoice(
            issuedAt: now()->subDays(45)->toDateTimeString(),
            status: StatutFacture::PAID_PARTIAL,
        );
        $invoice->setAttribute('relance_count', 1)->save();
        $invoice->setAttribute('last_relance_at', now()->subDays(8))->save();

        $relanced = app(RelanceService::class)->relancerFacturesImpayees(
            instanceId: $this->instance->id,
        );

        $this->assertSame([$invoice->getKey()], $relanced);
        $invoice->refresh();
        $this->assertSame(2, $invoice->getAttribute('relance_count'));
    }

    private function makeInvoice(string $issuedAt, StatutFacture $status): MenuiserieInvoice
    {
        return MenuiserieInvoice::create([
            'instance_id' => $this->instance->id,
            'invoice_number' => 'MNU-FAC-RLC-'.uniqid(),
            'client_id' => 1,
            'type' => TypeFacture::ACOMPTE->value,
            'amount_ht' => 100_000,
            'tax_rate' => 0.18,
            'amount_tva' => 18_000,
            'amount_ttc' => 118_000,
            'paid_amount' => $status === StatutFacture::PAID_FULL ? 118_000 : 0,
            'status' => $status->value,
            'issued_at' => $issuedAt,
        ]);
    }
}

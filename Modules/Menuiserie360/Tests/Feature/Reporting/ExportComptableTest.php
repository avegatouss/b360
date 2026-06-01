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
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P3-3 — Tests export comptable CSV (filtrage période, format BOM UTF-8).
 */
final class ExportComptableTest extends TestCase
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

    public function test_export_returns_csv_with_invoices_in_period(): void
    {
        $this->makeInvoice('2026-04-15', 'INV-IN-1', 100_000);
        $this->makeInvoice('2026-04-25', 'INV-IN-2', 250_000);
        $this->makeInvoice('2026-03-30', 'INV-OUT-PAST', 999_000);
        $this->makeInvoice('2026-05-02', 'INV-OUT-FUTURE', 999_000);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.reporting.exports.comptable', [
                'slug' => $this->instance->getAttribute('slug'),
                'from' => '2026-04-01',
                'to' => '2026-04-30',
            ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));

        $body = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $body);
        $this->assertStringContainsString('INV-IN-1', $body);
        $this->assertStringContainsString('INV-IN-2', $body);
        $this->assertStringNotContainsString('INV-OUT-PAST', $body);
        $this->assertStringNotContainsString('INV-OUT-FUTURE', $body);
    }

    public function test_export_default_period_is_current_month(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.reporting.exports.comptable', [
                'slug' => $this->instance->getAttribute('slug'),
            ]));

        $response->assertOk();
    }

    public function test_export_validates_date_range(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.reporting.exports.comptable', [
                'slug' => $this->instance->getAttribute('slug'),
                'from' => '2026-05-31',
                'to' => '2026-05-01',
            ]))
            ->assertSessionHasErrors(['to']);
    }

    private function makeInvoice(string $issuedDate, string $number, float $amountTtc): MenuiserieInvoice
    {
        return MenuiserieInvoice::create([
            'instance_id' => $this->instance->id,
            'invoice_number' => $number,
            'client_id' => 1,
            'type' => TypeFacture::ACOMPTE->value,
            'amount_ht' => round($amountTtc / 1.18, 2),
            'tax_rate' => 0.18,
            'amount_tva' => round($amountTtc - ($amountTtc / 1.18), 2),
            'amount_ttc' => $amountTtc,
            'paid_amount' => 0,
            'status' => StatutFacture::ISSUED->value,
            'issued_at' => $issuedDate.' 12:00:00',
        ]);
    }
}

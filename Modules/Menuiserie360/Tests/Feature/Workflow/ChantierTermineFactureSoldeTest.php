<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature\Workflow;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Menuiserie360\Domain\Chantier\Enums\StatutChantier;
use Modules\Menuiserie360\Domain\Chantier\Models\Chantier;
use Modules\Menuiserie360\Domain\Commercial\Enums\StatutDevis;
use Modules\Menuiserie360\Domain\Commercial\Models\Devis;
use Modules\Menuiserie360\Domain\Finance\Enums\TypeFacture;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P3-1 — Workflow ChantierTermine → CreateSoldeOnChantierTermine listener.
 */
final class ChantierTermineFactureSoldeTest extends TestCase
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

    private function slug(): string
    {
        return (string) $this->instance->getAttribute('slug');
    }

    public function test_terminer_chantier_creates_facture_solde_minus_acompte(): void
    {
        [$bc, $chantier] = $this->makeBcAndChantier(montantTtc: 1_180_000, acomptePct: 30);
        $this->makeAcompteInvoice($bc, amountTtc: 354_000); // 30% of 1.18M

        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.chantiers.terminer', [
                'slug' => $this->slug(),
                'chantier' => $chantier->getKey(),
            ]))
            ->assertRedirect();

        $chantier->refresh();
        $this->assertSame(StatutChantier::TERMINE->value, $chantier->getAttribute('statut'));
        $this->assertNotNull($chantier->getAttribute('date_fin_reelle'));

        $solde = MenuiserieInvoice::query()
            ->where('bc_id', $bc->getKey())
            ->where('type', TypeFacture::SOLDE->value)
            ->firstOrFail();

        $this->assertSame('826000.00', (string) $solde->getAttribute('amount_ttc'));
    }

    public function test_terminer_chantier_idempotent_no_duplicate_solde(): void
    {
        [$bc, $chantier] = $this->makeBcAndChantier(montantTtc: 500_000, acomptePct: 50);

        // First terminer call
        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.chantiers.terminer', [
                'slug' => $this->slug(),
                'chantier' => $chantier->getKey(),
            ]));

        $chantier->refresh();
        $firstFinAt = $chantier->getAttribute('date_fin_reelle');

        // Second call: chantier déjà TERMINE → no-op
        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.chantiers.terminer', [
                'slug' => $this->slug(),
                'chantier' => $chantier->getKey(),
            ]))
            ->assertRedirect();

        $chantier->refresh();
        $this->assertEquals($firstFinAt, $chantier->getAttribute('date_fin_reelle'));

        $count = MenuiserieInvoice::query()
            ->where('bc_id', $bc->getKey())
            ->where('type', TypeFacture::SOLDE->value)
            ->count();
        $this->assertSame(1, $count);
    }

    /**
     * @return array{0: BonCommande, 1: Chantier}
     */
    private function makeBcAndChantier(float $montantTtc, int $acomptePct): array
    {
        $customer = Customer::withoutGlobalScopes()->create([
            'instance_id' => $this->instance->id,
            'code' => 'CUS-P3-1',
            'name' => 'Client P3',
            'is_active' => true,
        ]);

        $devis = Devis::create([
            'instance_id' => $this->instance->id,
            'numero' => 'DEV-P3-001',
            'client_id' => $customer->getKey(),
            'statut' => StatutDevis::ACCEPTE->value,
            'taux_tva' => 0.18,
            'montant_ht' => round($montantTtc / 1.18, 2),
            'montant_tva' => round($montantTtc - ($montantTtc / 1.18), 2),
            'montant_ttc' => $montantTtc,
            'validite_jours' => 30,
            'marge_minimum' => 0.15,
        ]);

        $bc = BonCommande::create([
            'instance_id' => $this->instance->id,
            'numero' => 'BC-P3-001',
            'devis_id' => $devis->getKey(),
            'client_id' => $customer->getKey(),
            'statut' => 'cree',
            'montant_ht' => round($montantTtc / 1.18, 2),
            'taux_tva' => 0.18,
            'montant_tva' => round($montantTtc - ($montantTtc / 1.18), 2),
            'montant_ttc' => $montantTtc,
            'acompte_pct' => $acomptePct,
        ]);

        $chantier = Chantier::create([
            'instance_id' => $this->instance->id,
            'numero' => 'CH-P3-001',
            'bc_id' => $bc->getKey(),
            'client_id' => $customer->getKey(),
            'statut' => StatutChantier::EN_COURS->value,
        ]);

        return [$bc, $chantier];
    }

    private function makeAcompteInvoice(BonCommande $bc, float $amountTtc): MenuiserieInvoice
    {
        return MenuiserieInvoice::create([
            'instance_id' => $this->instance->id,
            'invoice_number' => 'MNU-FAC-P3-AC-001',
            'client_id' => $bc->getAttribute('client_id'),
            'bc_id' => $bc->getKey(),
            'type' => TypeFacture::ACOMPTE->value,
            'amount_ht' => round($amountTtc / 1.18, 2),
            'tax_rate' => 0.18,
            'amount_tva' => round($amountTtc - ($amountTtc / 1.18), 2),
            'amount_ttc' => $amountTtc,
            'paid_amount' => $amountTtc,
            'status' => 'paid',
            'issued_at' => now(),
        ]);
    }
}

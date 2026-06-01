<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Commercial\Enums\StatutDevis;
use Modules\Menuiserie360\Domain\Commercial\Events\DevisAccepte;
use Modules\Menuiserie360\Domain\Commercial\Models\Devis;
use Modules\Menuiserie360\Domain\Commercial\Models\LigneDevis;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Production\Actions\TransformBcToOfAction;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabrication;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabricationLigne;
use Modules\Menuiserie360\Domain\Sales\Actions\TransformDevisToBcAction;
use Modules\Menuiserie360\Domain\Sales\Events\BonCommandeCreee;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommandeItem;
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P2-A — Tests workflow bout-en-bout.
 *
 * Vérifie l'enchaînement Devis → BC → OF + Facture acompte (via listener).
 * Couvre l'orchestration cross-BC (Commercial / Sales / Production / Finance).
 */
final class WorkflowDevisToInvoiceTest extends TestCase
{
    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $instance = $this->makeRootInstance();
        $this->instanceId = $instance->id;
        CurrentInstance::set($instance);
    }

    public function test_transforming_an_accepted_devis_creates_bc_with_items(): void
    {
        $devis = $this->makeDevisAvecLignes(2);

        $bc = (new TransformDevisToBcAction)->execute($devis, acomptePct: 30.0);

        $this->assertInstanceOf(BonCommande::class, $bc);
        $this->assertSame((int) $devis->getKey(), (int) $bc->getAttribute('devis_id'));
        $this->assertSame('cree', $bc->getAttribute('statut'));
        $this->assertStringStartsWith('BC-', (string) $bc->getAttribute('numero'));

        // Items snapshotés
        $items = BonCommandeItem::where('bc_id', $bc->getKey())->get();
        $this->assertCount(2, $items);
    }

    public function test_devis_is_marked_transforme_after_action(): void
    {
        $devis = $this->makeDevisAvecLignes();

        (new TransformDevisToBcAction)->execute($devis);

        $devis->refresh();
        $this->assertSame(StatutDevis::TRANSFORME->value, $devis->getAttribute('statut'));
    }

    public function test_action_is_idempotent_returns_existing_bc(): void
    {
        $devis = $this->makeDevisAvecLignes();
        $action = new TransformDevisToBcAction;

        $bc1 = $action->execute($devis);
        $bc2 = $action->execute($devis);

        $this->assertSame((int) $bc1->getKey(), (int) $bc2->getKey());
    }

    public function test_dispatches_devis_accepte_and_bc_creee_events(): void
    {
        Event::fake([DevisAccepte::class, BonCommandeCreee::class]);

        $devis = $this->makeDevisAvecLignes();

        (new TransformDevisToBcAction)->execute($devis);

        Event::assertDispatched(DevisAccepte::class);
        Event::assertDispatched(BonCommandeCreee::class);
    }

    public function test_listener_creates_acompte_invoice_when_devis_accepte_fires(): void
    {
        // Le listener réel est branché — pas de Event::fake() ici.
        $devis = $this->makeDevisAvecLignes();

        $bc = (new TransformDevisToBcAction)->execute($devis, acomptePct: 30.0);

        // Une facture acompte doit avoir été créée par le listener
        $invoice = MenuiserieInvoice::where('bc_id', $bc->getKey())
            ->where('type', 'acompte')
            ->first();

        $this->assertNotNull($invoice);
        $this->assertStringStartsWith('MNU-FAC-', (string) $invoice->getAttribute('invoice_number'));
        // Acompte = 30% du TTC du BC
        $bcTtc = (float) $bc->getAttribute('montant_ttc');
        $this->assertSame(round($bcTtc * 0.30, 2), (float) $invoice->getAttribute('amount_ttc'));

        // Le BC est lié à la facture
        $bc->refresh();
        $this->assertSame((int) $invoice->getKey(), (int) $bc->getAttribute('facture_acompte_id'));
    }

    public function test_transform_bc_to_of_creates_of_with_lignes(): void
    {
        $devis = $this->makeDevisAvecLignes(3);
        $bc = (new TransformDevisToBcAction)->execute($devis);

        $of = (new TransformBcToOfAction)->execute($bc);

        $this->assertInstanceOf(OrdreFabrication::class, $of);
        $this->assertSame((int) $bc->getKey(), (int) $of->getAttribute('bc_id'));
        $this->assertStringStartsWith('OF-', (string) $of->getAttribute('numero'));

        $lignes = OrdreFabricationLigne::where('of_id', $of->getKey())->get();
        $this->assertCount(3, $lignes);
    }

    public function test_transform_bc_to_of_is_idempotent(): void
    {
        $devis = $this->makeDevisAvecLignes();
        $bc = (new TransformDevisToBcAction)->execute($devis);

        $action = new TransformBcToOfAction;
        $of1 = $action->execute($bc);
        $of2 = $action->execute($bc);

        $this->assertSame((int) $of1->getKey(), (int) $of2->getKey());
    }

    public function test_full_workflow_devis_to_bc_to_of_with_invoice(): void
    {
        // Scénario bout-en-bout : devis → BC → OF + facture acompte
        $devis = $this->makeDevisAvecLignes(2);

        $bc = (new TransformDevisToBcAction)->execute($devis, acomptePct: 30.0);
        $of = (new TransformBcToOfAction)->execute($bc);

        // Devis transformé
        $devis->refresh();
        $this->assertSame(StatutDevis::TRANSFORME->value, $devis->getAttribute('statut'));

        // BC créé + lié au devis
        $this->assertSame((int) $devis->getKey(), (int) $bc->getAttribute('devis_id'));

        // OF créé + lié au BC
        $this->assertSame((int) $bc->getKey(), (int) $of->getAttribute('bc_id'));

        // Facture acompte créée + liée au BC (via listener)
        $invoice = MenuiserieInvoice::where('bc_id', $bc->getKey())->where('type', 'acompte')->first();
        $this->assertNotNull($invoice);
        $bc->refresh();
        $this->assertSame((int) $invoice->getKey(), (int) $bc->getAttribute('facture_acompte_id'));
    }

    // ─── Helper ─────────────────────────────────────────────────

    private function makeDevisAvecLignes(int $nbLignes = 1): Devis
    {
        $year = (int) date('Y');
        $devis = Devis::create([
            'instance_id' => $this->instanceId,
            'numero' => "DEV-{$year}-".str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
            'client_id' => 1,
            'statut' => StatutDevis::ACCEPTE->value,
            'montant_ht' => 1000 * $nbLignes,
            'taux_tva' => 0.18,
            'montant_tva' => 180 * $nbLignes,
            'montant_ttc' => 1180 * $nbLignes,
        ]);

        for ($i = 1; $i <= $nbLignes; $i++) {
            LigneDevis::create([
                'instance_id' => $this->instanceId,
                'devis_id' => $devis->getKey(),
                'designation' => "Fenêtre 1500x1200 #{$i}",
                'quantite' => 1,
                'largeur_mm' => 1500,
                'hauteur_mm' => 1200,
                'prix_unitaire_ht' => 1000,
                'montant_ht' => 1000,
                'cout_revient' => 600,
                'ordre' => $i,
            ]);
        }

        return $devis;
    }
}

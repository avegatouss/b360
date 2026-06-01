<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Stock\Contracts\StockContract;
use Modules\Menuiserie360\Domain\Stock\Exceptions\StockInsuffisantException;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Menuiserie360\Domain\Stock\Models\MouvementStock;
use Modules\Menuiserie360\Domain\Stock\Models\StockMatiere;
use Modules\Menuiserie360\Domain\Stock\Services\StockMatiereService;
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P1-9 — Tests unitaires StockMatiereService.
 *
 * Couvre les invariants critiques :
 *   - DI binding : StockContract → StockMatiereService
 *   - Réception (entrée), réservation, consommation, libération
 *   - Multi-tenant isolation
 *   - Idempotence par référence
 *   - Stock insuffisant lève StockInsuffisantException
 *   - Audit trail (mouvements enregistrés)
 *   - Quantités négatives ou nulles refusées
 */
final class StockMatiereServiceTest extends TestCase
{
    private StockMatiereService $service;

    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $instance = $this->makeRootInstance();
        $this->instanceId = $instance->id;
        CurrentInstance::set($instance);

        $this->service = new StockMatiereService;
    }

    // ─── DI binding ─────────────────────────────────────────────

    public function test_stock_contract_is_bound_to_stock_matiere_service(): void
    {
        $resolved = app(StockContract::class);

        $this->assertInstanceOf(StockMatiereService::class, $resolved);
    }

    // ─── Helpers ────────────────────────────────────────────────

    private function makeMatiere(string $code = 'PROFIL-50x50'): MatierePremiere
    {
        return MatierePremiere::create([
            'instance_id' => $this->instanceId,
            'code' => $code,
            'designation' => 'Profil aluminium 50x50',
            'categorie' => 'profile_alu',
            'unite' => 'm_lineaire',
            'prix_unitaire' => 2500,
            'seuil_alerte' => 50,
            'is_active' => true,
        ]);
    }

    // ─── Réception (entrée) ─────────────────────────────────────

    public function test_recevoir_increases_quantite_actuelle(): void
    {
        $matiere = $this->makeMatiere();

        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100.5, 'PO-2026-001');

        $stock = StockMatiere::where('instance_id', $this->instanceId)
            ->where('matiere_id', $matiere->getKey())
            ->first();

        $this->assertNotNull($stock);
        $this->assertSame('100.5000', (string) $stock->getAttribute('quantite_actuelle'));
        $this->assertSame('0.0000', (string) $stock->getAttribute('quantite_reservee'));
    }

    public function test_recevoir_records_audit_movement(): void
    {
        $matiere = $this->makeMatiere();

        $this->service->recevoir($this->instanceId, $matiere->getKey(), 50, 'PO-2026-002');

        $mvt = MouvementStock::where('instance_id', $this->instanceId)
            ->where('reference', 'PO-2026-002')
            ->first();

        $this->assertNotNull($mvt);
        $this->assertSame('entree', $mvt->getAttribute('type'));
        $this->assertSame('50.0000', (string) $mvt->getAttribute('quantite'));
        $this->assertSame('50.0000', (string) $mvt->getAttribute('quantite_apres'));
    }

    public function test_recevoir_is_idempotent_by_reference(): void
    {
        $matiere = $this->makeMatiere();

        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100, 'PO-IDEM-001');
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100, 'PO-IDEM-001');

        $stock = StockMatiere::where('matiere_id', $matiere->getKey())->first();
        $this->assertNotNull($stock);
        // Re-jeu silencieux : quantité reste 100, pas 200.
        $this->assertSame('100.0000', (string) $stock->getAttribute('quantite_actuelle'));
    }

    public function test_recevoir_refuses_negative_quantity(): void
    {
        $matiere = $this->makeMatiere();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->recevoir($this->instanceId, $matiere->getKey(), -10, 'PO-NEG');
    }

    public function test_recevoir_refuses_zero_quantity(): void
    {
        $matiere = $this->makeMatiere();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 0, 'PO-ZERO');
    }

    // ─── isAvailable ────────────────────────────────────────────

    public function test_is_available_returns_true_when_stock_sufficient(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100, 'PO-A1');

        $this->assertTrue($this->service->isAvailable($this->instanceId, $matiere->getKey(), 50));
        $this->assertTrue($this->service->isAvailable($this->instanceId, $matiere->getKey(), 100));
    }

    public function test_is_available_returns_false_when_stock_insufficient(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100, 'PO-A2');

        $this->assertFalse($this->service->isAvailable($this->instanceId, $matiere->getKey(), 101));
    }

    public function test_is_available_accounts_for_reserved_quantity(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100, 'PO-A3');
        $this->service->reserve($this->instanceId, $matiere->getKey(), 60, 'OF-A3-RESV');

        // Disponible = 100 - 60 = 40
        $this->assertTrue($this->service->isAvailable($this->instanceId, $matiere->getKey(), 40));
        $this->assertFalse($this->service->isAvailable($this->instanceId, $matiere->getKey(), 41));
    }

    // ─── reserve ────────────────────────────────────────────────

    public function test_reserve_increases_quantite_reservee(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 200, 'PO-R1');

        $this->service->reserve($this->instanceId, $matiere->getKey(), 75, 'OF-R1');

        $stock = StockMatiere::where('matiere_id', $matiere->getKey())->first();
        $this->assertNotNull($stock);
        $this->assertSame('200.0000', (string) $stock->getAttribute('quantite_actuelle'));
        $this->assertSame('75.0000', (string) $stock->getAttribute('quantite_reservee'));
    }

    public function test_reserve_throws_when_insufficient_disponible(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 50, 'PO-R2');

        $this->expectException(StockInsuffisantException::class);
        $this->service->reserve($this->instanceId, $matiere->getKey(), 51, 'OF-R2');
    }

    public function test_reserve_is_idempotent_by_reference(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 200, 'PO-R3');

        $this->service->reserve($this->instanceId, $matiere->getKey(), 30, 'OF-R3-IDEM');
        $this->service->reserve($this->instanceId, $matiere->getKey(), 30, 'OF-R3-IDEM');

        $stock = StockMatiere::where('matiere_id', $matiere->getKey())->first();
        $this->assertNotNull($stock);
        $this->assertSame('30.0000', (string) $stock->getAttribute('quantite_reservee'));
    }

    // ─── consume ────────────────────────────────────────────────

    public function test_consume_decreases_quantite_actuelle(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100, 'PO-C1');

        $this->service->consume($this->instanceId, $matiere->getKey(), 40, 'OF-C1-CONS');

        $stock = StockMatiere::where('matiere_id', $matiere->getKey())->first();
        $this->assertNotNull($stock);
        $this->assertSame('60.0000', (string) $stock->getAttribute('quantite_actuelle'));
    }

    public function test_consume_throws_when_stock_insufficient(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 30, 'PO-C2');

        $this->expectException(StockInsuffisantException::class);
        $this->service->consume($this->instanceId, $matiere->getKey(), 40, 'OF-C2');
    }

    public function test_consume_releases_associated_reservation(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100, 'PO-C3');
        $this->service->reserve($this->instanceId, $matiere->getKey(), 30, 'OF-C3');
        // Même référence pour consume → libère la réservation associée
        $this->service->consume($this->instanceId, $matiere->getKey(), 30, 'OF-C3');

        $stock = StockMatiere::where('matiere_id', $matiere->getKey())->first();
        $this->assertNotNull($stock);
        $this->assertSame('70.0000', (string) $stock->getAttribute('quantite_actuelle'));
        $this->assertSame('0.0000', (string) $stock->getAttribute('quantite_reservee'));
    }

    public function test_consume_is_idempotent_by_reference(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100, 'PO-C4');

        $this->service->consume($this->instanceId, $matiere->getKey(), 25, 'OF-C4-IDEM');
        $this->service->consume($this->instanceId, $matiere->getKey(), 25, 'OF-C4-IDEM');

        $stock = StockMatiere::where('matiere_id', $matiere->getKey())->first();
        $this->assertNotNull($stock);
        $this->assertSame('75.0000', (string) $stock->getAttribute('quantite_actuelle'));
    }

    // ─── release ────────────────────────────────────────────────

    public function test_release_returns_reserved_quantity_to_disponible(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100, 'PO-L1');
        $this->service->reserve($this->instanceId, $matiere->getKey(), 40, 'OF-L1');

        $this->service->release($this->instanceId, $matiere->getKey(), 'OF-L1');

        $stock = StockMatiere::where('matiere_id', $matiere->getKey())->first();
        $this->assertNotNull($stock);
        $this->assertSame('100.0000', (string) $stock->getAttribute('quantite_actuelle'));
        $this->assertSame('0.0000', (string) $stock->getAttribute('quantite_reservee'));
    }

    public function test_release_no_op_if_no_reservation_exists(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100, 'PO-L2');

        $this->service->release($this->instanceId, $matiere->getKey(), 'NON-EXISTANT-REF');

        $stock = StockMatiere::where('matiere_id', $matiere->getKey())->first();
        $this->assertNotNull($stock);
        $this->assertSame('0.0000', (string) $stock->getAttribute('quantite_reservee'));
    }

    public function test_release_is_idempotent_by_reference(): void
    {
        $matiere = $this->makeMatiere();
        $this->service->recevoir($this->instanceId, $matiere->getKey(), 100, 'PO-L3');
        $this->service->reserve($this->instanceId, $matiere->getKey(), 40, 'OF-L3');

        $this->service->release($this->instanceId, $matiere->getKey(), 'OF-L3');
        $this->service->release($this->instanceId, $matiere->getKey(), 'OF-L3');

        $stock = StockMatiere::where('matiere_id', $matiere->getKey())->first();
        $this->assertNotNull($stock);
        $this->assertSame('0.0000', (string) $stock->getAttribute('quantite_reservee'));
    }

    // ─── Multi-tenant isolation ─────────────────────────────────

    public function test_stock_is_isolated_by_instance(): void
    {
        $matiereA = $this->makeMatiere('PROFIL-A');
        $this->service->recevoir($this->instanceId, $matiereA->getKey(), 100, 'PO-A');

        // Bascule sur instance B
        $instanceB = \App\Instances\Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-'.uniqid(),
            'is_active' => true,
            'meta' => [],
        ]);
        CurrentInstance::set($instanceB);

        // Même matière_id côté instance B → stock séparé.
        $availableInB = $this->service->isAvailable($instanceB->id, $matiereA->getKey(), 50);
        $this->assertFalse(
            $availableInB,
            "Le stock de l'instance A ne doit pas être visible depuis l'instance B."
        );
    }
}

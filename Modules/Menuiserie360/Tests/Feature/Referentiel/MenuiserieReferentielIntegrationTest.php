<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature\Referentiel;

use App\Instances\Instance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;
use Modules\Menuiserie360\Domain\Purchasing\Enums\CategorieFournisseur;
use Modules\Menuiserie360\Domain\Purchasing\Models\Fournisseur;
use Modules\Menuiserie360\Integration\Referentiel\MenuiserieClientPartySource;
use Modules\Menuiserie360\Integration\Referentiel\MenuiseriePartyMapper;
use Modules\Menuiserie360\Tests\TestCase;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyWriter;

/**
 * Lot 1.a (ADR-030) — Intégration Menuiserie360 → Referentiel360.
 *
 * Couvre : push observer post-commit (client + fournisseur), best-effort
 * (PartyWriter qui throw ne casse pas la création), et PartySource backfill.
 *
 * Note RefreshDatabase + afterCommit : on installe un transaction manager de
 * test ({@see ImmediateAfterCommitTransactionsManager}) qui exécute les
 * callbacks afterCommit au commit de la transaction métier imbriquée (niveau 1).
 */
final class MenuiserieReferentielIntegrationTest extends TestCase
{
    private Instance $instance;

    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->instance = $this->makeRootInstance();
        $this->instanceId = (int) $this->instance->id;
        CurrentInstance::set($this->instance);
        TeamContext::set(0);

        // Force l'exécution des afterCommit au commit vers le niveau 1 (cf. classe).
        $manager = new ImmediateAfterCommitTransactionsManager;
        foreach (['sqlite', 'system'] as $name) {
            DB::connection($name)->setTransactionManager($manager);
        }
        $this->app->instance('db.transactions', $manager);
    }

    public function test_creating_client_pushes_one_party_and_one_link(): void
    {
        $client = DB::transaction(fn (): ClientMenuiserie => ClientMenuiserie::create([
            'instance_id' => $this->instanceId,
            'code' => 'CLI-REF-001',
            'type' => 'entreprise',
            'raison_sociale' => 'KHOGA SARL',
            'email' => 'contact@khoga.ci',
            'telephone_principal' => '+225 07 00 00 00 00',
            'statut' => 'lead',
            'is_active' => true,
        ]));

        $this->assertSame(1, DB::table('ref_parties')->where('instance_id', $this->instanceId)->count());

        $link = DB::table('ref_party_links')
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'mnu.client')
            ->where('linkable_id', $client->getKey())
            ->first();

        $this->assertNotNull($link);
    }

    public function test_creating_fournisseur_pushes_one_party_and_supplier_link(): void
    {
        $fournisseur = DB::transaction(fn (): Fournisseur => Fournisseur::create([
            'instance_id' => $this->instanceId,
            'code' => 'FRN-REF-001',
            'nom_commercial' => 'Alu CI SA',
            'raison_sociale' => 'Alu Cote Ivoire SA',
            'categorie' => CategorieFournisseur::PROFILE_ALU->value,
            'email' => 'ventes@aluci.ci',
            'telephone_principal' => '+225 27 00 00 00 00',
            'devise' => 'XOF',
            'is_active' => true,
        ]));

        $this->assertSame(1, DB::table('ref_parties')->where('instance_id', $this->instanceId)->count());

        $link = DB::table('ref_party_links')
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'mnu.supplier')
            ->where('linkable_id', $fournisseur->getKey())
            ->first();

        $this->assertNotNull($link);
    }

    public function test_push_is_best_effort_when_party_writer_throws(): void
    {
        // PartyWriter qui throw : la création client doit réussir, l'exception avalée.
        $this->app->instance(PartyWriter::class, new class implements PartyWriter
        {
            public function upsertFromModule(int $instanceId, string $linkType, PartyAttributesDto $attrs): \Modules\Referentiel360\Contracts\Party\PartyDto
            {
                throw new \RuntimeException('Referentiel indisponible');
            }

            public function link(int $instanceId, int $partyId, string $linkType, int $localId): void
            {
                throw new \RuntimeException('Referentiel indisponible');
            }
        });

        // Réattacher l'observer avec le writer mocké (boot a câblé l'ancien).
        ClientMenuiserie::observe($this->app->make(
            \Modules\Menuiserie360\Integration\Referentiel\ClientReferentielObserver::class
        ));

        $client = DB::transaction(fn (): ClientMenuiserie => ClientMenuiserie::create([
            'instance_id' => $this->instanceId,
            'code' => 'CLI-BE-001',
            'type' => 'particulier',
            'nom' => 'Survivor',
            'statut' => 'lead',
            'is_active' => true,
        ]));

        // Le client existe malgré l'échec du référentiel.
        $this->assertDatabaseHas('mnu_clients_menuiserie', [
            'id' => $client->getKey(),
            'nom' => 'Survivor',
        ]);
        // Aucun party créé (writer a échoué silencieusement).
        $this->assertSame(0, DB::table('ref_parties')->where('instance_id', $this->instanceId)->count());
    }

    public function test_client_party_source_yields_expected_dto(): void
    {
        ClientMenuiserie::create([
            'instance_id' => $this->instanceId,
            'code' => 'CLI-SRC-001',
            'type' => 'entreprise',
            'raison_sociale' => 'Source SARL',
            'prenom' => 'Jean',
            'nom' => 'Source',
            'email' => 'src@x.ci',
            'telephone_principal' => '0102030405',
            'statut' => 'lead',
            'is_active' => true,
        ]);

        $source = new MenuiserieClientPartySource(new MenuiseriePartyMapper);

        $this->assertSame('mnu.client', $source->linkType());

        $dtos = iterator_to_array($source->each($this->instanceId));
        $this->assertCount(1, $dtos);

        /** @var PartyAttributesDto $dto */
        $dto = $dtos[0];
        $this->assertTrue($dto->isCustomer);
        $this->assertSame('entreprise', $dto->personType);
        $this->assertSame('Source SARL', $dto->displayName);
        $this->assertSame('Source SARL', $dto->legalName);
        $this->assertSame('src@x.ci', $dto->email);
        $this->assertSame('menuiserie', $dto->sourceModule);
    }
}

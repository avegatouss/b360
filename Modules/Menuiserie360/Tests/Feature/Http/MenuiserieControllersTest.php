<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature\Http;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Core\Tests\Concerns\RequiresEshop360Schema;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Menuiserie360\Domain\Chantier\Models\Chantier;
use Modules\Menuiserie360\Domain\Chantier\Models\EtapeChantier;
use Modules\Menuiserie360\Domain\Commercial\Enums\StatutDevis;
use Modules\Menuiserie360\Domain\Commercial\Models\Devis;
use Modules\Menuiserie360\Domain\Commercial\Models\LigneDevis;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Production\Enums\StatutOrdreFabrication;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabrication;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Menuiserie360\Domain\Stock\Models\MouvementStock;
use Modules\Menuiserie360\Domain\Stock\Models\StockMatiere;
use Modules\Menuiserie360\Domain\Stock\Services\StockMatiereService;
use Modules\Menuiserie360\Tests\TestCase;
use Spatie\Permission\Models\Role;

/**
 * P2-B-2 — Tests HTTP des Controllers Menuiserie360.
 *
 * Couvre auth, permissions, scoping multi-tenant et happy paths
 * pour les 8 controllers. Stub setUp construit une instance + user
 * super-admin globalement utilisable (les routes super-admin bypassent
 * les `can:` middleware via Gate::before défini dans CoreAuthServiceProvider).
 */
final class MenuiserieControllersTest extends TestCase
{
    use RequiresEshop360Schema;

    private Instance $instance;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requireEshop360Schema();
        Cache::flush();

        $this->instance = $this->makeRootInstance();
        $this->superAdmin = $this->makeRootSuperAdmin($this->instance);
        CurrentInstance::set($this->instance);
        TeamContext::set(0);

        // Ensure user is active member of the instance (instance.membership middleware).
        DB::connection('system')->table('instance_user')->updateOrInsert(
            ['instance_id' => $this->instance->id, 'user_id' => $this->superAdmin->id],
            ['status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        );
    }

    private function slug(): string
    {
        return (string) $this->instance->getAttribute('slug');
    }

    // ─── Auth / unauthenticated redirect ───────────────────────────

    public function test_unauthenticated_user_is_redirected_from_devis_index(): void
    {
        $this->get(route('menuiserie.devis.index', ['slug' => $this->slug()]))
            ->assertRedirect();
    }

    // ─── Index pages return 200 for authenticated super-admin ──────

    public function test_super_admin_can_access_devis_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.devis.index', ['slug' => $this->slug()]))
            ->assertOk();
    }

    public function test_super_admin_can_access_devis_create_form_with_matieres(): void
    {
        $this->makeMatiere(); // 'TEST-PROFIL'

        $response = $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.devis.create', ['slug' => $this->slug()]))
            ->assertOk();

        // La vue charge la liste des matières actives et la sérialise pour Alpine.
        $response->assertSee('TEST-PROFIL', escape: false);
        $response->assertSee('devisForm(', escape: false);
    }

    // ─── Client search (M-UI-4) ────────────────────────────────────

    public function test_client_search_returns_matching_customers(): void
    {
        $this->makeCustomer(); // crée 'TEST-CUS-001' / 'Test Client'
        Customer::withoutGlobalScopes()->create([
            'instance_id' => $this->instance->id,
            'code' => 'CUST-OTHER',
            'name' => 'Karim Coulibaly',
            'phone' => '+225 07 50 80 90 00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson(route('menuiserie.clients.search', ['slug' => $this->slug()]).'?q=Karim');

        $response->assertOk();
        $data = $response->json();
        $this->assertIsArray($data['results']);
        $this->assertCount(1, $data['results']);
        $this->assertSame('CUST-OTHER', $data['results'][0]['code']);
        $this->assertSame('Karim Coulibaly', $data['results'][0]['name']);
    }

    public function test_client_search_returns_empty_when_query_blank_or_short(): void
    {
        $this->makeCustomer();

        $this->actingAs($this->superAdmin)
            ->getJson(route('menuiserie.clients.search', ['slug' => $this->slug()]).'?q=')
            ->assertOk()
            ->assertJsonPath('results', []);
    }

    public function test_client_search_filters_by_email_phone_and_code(): void
    {
        Customer::withoutGlobalScopes()->create([
            'instance_id' => $this->instance->id,
            'code' => 'PHO-001',
            'name' => 'Joindre par téléphone',
            'phone' => '+225 27 22 99 11 22',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson(route('menuiserie.clients.search', ['slug' => $this->slug()]).'?q=22%2099');

        $response->assertOk();
        $this->assertCount(1, $response->json('results'));
        $this->assertSame('PHO-001', $response->json('results.0.code'));
    }

    public function test_super_admin_can_access_bc_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.bc.index', ['slug' => $this->slug()]))
            ->assertOk();
    }

    public function test_super_admin_can_access_of_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.production.index', ['slug' => $this->slug()]))
            ->assertOk();
    }

    public function test_super_admin_can_access_chantier_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.chantiers.index', ['slug' => $this->slug()]))
            ->assertOk();
    }

    public function test_super_admin_can_access_stock_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.stocks.index', ['slug' => $this->slug()]))
            ->assertOk();
    }

    public function test_super_admin_can_access_factures_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.factures.index', ['slug' => $this->slug()]))
            ->assertOk();
    }

    public function test_super_admin_can_access_clients_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.clients.index', ['slug' => $this->slug()]))
            ->assertOk();
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.reporting.index', ['slug' => $this->slug()]))
            ->assertOk();
    }

    // ─── Devis happy path : create + accept ────────────────────────

    public function test_super_admin_can_store_devis_and_show_it(): void
    {
        $customer = $this->makeCustomer();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.devis.store', ['slug' => $this->slug()]), [
                'client_id' => $customer->getKey(),
                'taux_tva' => 0.18,
                'validite_jours' => 30,
                'marge_minimum' => 0.15,
                'lignes' => [
                    [
                        'designation' => 'Fenêtre alu 1500x1200',
                        'quantite' => 2,
                        'prix_unitaire_ht' => 150000,
                        'cout_revient' => 100000,
                        'largeur_mm' => 1500,
                        'hauteur_mm' => 1200,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $devis = Devis::query()
            ->where('instance_id', $this->instance->id)
            ->orderByDesc('id')
            ->first();

        $this->assertNotNull($devis);
        $this->assertSame('300000.00', (string) $devis->getAttribute('montant_ht'));
        $this->assertSame('54000.00', (string) $devis->getAttribute('montant_tva'));
        $this->assertSame('354000.00', (string) $devis->getAttribute('montant_ttc'));

        // Show page returns 200
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.devis.show', ['slug' => $this->slug(), 'devis' => $devis->getKey()]))
            ->assertOk()
            ->assertSee($devis->getAttribute('numero'));
    }

    public function test_accepter_devis_creates_bc_and_acompte_invoice_via_listener(): void
    {
        $customer = $this->makeCustomer();
        $devis = $this->makeDevis($customer->getKey());

        // Devis must be at statut soumis/valide/accepte for transformation
        $devis->setAttribute('statut', StatutDevis::VALIDE->value);
        $devis->save();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.devis.accepter', ['slug' => $this->slug(), 'devis' => $devis->getKey()]), [
                'acompte_pct' => 40,
            ]);

        $response->assertRedirect();

        $bc = BonCommande::query()
            ->where('instance_id', $this->instance->id)
            ->where('devis_id', $devis->getKey())
            ->first();

        $this->assertNotNull($bc, 'BonCommande should be created by TransformDevisToBcAction.');
        $this->assertSame('40.00', (string) $bc->getAttribute('acompte_pct'));

        // Facture acompte créée par le listener
        $invoice = MenuiserieInvoice::query()
            ->where('instance_id', $this->instance->id)
            ->where('bc_id', $bc->getKey())
            ->where('type', 'acompte')
            ->first();

        $this->assertNotNull($invoice, 'Facture acompte should be created by CreateAcompteOnDevisAccepte listener.');

        // Le BC pointe vers la facture
        $this->assertSame($invoice->getKey(), $bc->fresh()->getAttribute('facture_acompte_id'));
    }

    // ─── Devis PDF returns binary PDF via DomPDF (P2-C step 3) ─────

    public function test_super_admin_can_download_devis_pdf(): void
    {
        $customer = $this->makeCustomer();
        $devis = $this->makeDevis($customer->getKey());

        $response = $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.devis.pdf', ['slug' => $this->slug(), 'devis' => $devis->getKey()]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', (string) $response->getContent());
    }

    // ─── Stock recevoir (entrée fournisseur) ───────────────────────

    public function test_super_admin_can_receive_stock(): void
    {
        $matiere = $this->makeMatiere();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.stocks.recevoir', ['slug' => $this->slug(), 'matiere' => $matiere->getKey()]), [
                'quantite' => 50.5,
                'reference' => 'PO-HTTP-001',
            ]);

        $response->assertRedirect();

        $stock = StockMatiere::query()
            ->where('instance_id', $this->instance->id)
            ->where('matiere_id', $matiere->getKey())
            ->first();

        $this->assertNotNull($stock);
        $this->assertSame('50.5000', (string) $stock->getAttribute('quantite_actuelle'));

        $mvt = MouvementStock::query()
            ->where('instance_id', $this->instance->id)
            ->where('reference', 'PO-HTTP-001')
            ->first();

        $this->assertNotNull($mvt);
        $this->assertSame('entree', $mvt->getAttribute('type'));
    }

    // ─── OF lancer + terminer workflow ─────────────────────────────

    public function test_super_admin_can_lancer_and_terminer_of(): void
    {
        $customer = $this->makeCustomer();
        $devis = $this->makeDevis($customer->getKey());

        // Crée BC + OF via workflow direct
        $bc = BonCommande::create([
            'instance_id' => $this->instance->id,
            'numero' => 'BC-TEST-001',
            'devis_id' => $devis->getKey(),
            'client_id' => $customer->getKey(),
            'statut' => 'cree',
            'montant_ht' => 100000,
            'taux_tva' => 0.18,
            'montant_tva' => 18000,
            'montant_ttc' => 118000,
            'acompte_pct' => 30,
        ]);

        $of = OrdreFabrication::create([
            'instance_id' => $this->instance->id,
            'numero' => 'OF-TEST-001',
            'bc_id' => $bc->getKey(),
            'statut' => StatutOrdreFabrication::EN_ATTENTE->value,
        ]);

        // Lancer
        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.production.lancer', ['slug' => $this->slug(), 'of' => $of->getKey()]))
            ->assertRedirect();

        $this->assertSame(StatutOrdreFabrication::EN_COURS->value, $of->fresh()->getAttribute('statut'));
        $this->assertNotNull($of->fresh()->getAttribute('date_demarrage'));

        // Terminer
        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.production.terminer', ['slug' => $this->slug(), 'of' => $of->getKey()]))
            ->assertRedirect();

        $this->assertSame(StatutOrdreFabrication::TERMINE->value, $of->fresh()->getAttribute('statut'));
        $this->assertNotNull($of->fresh()->getAttribute('date_fin_reelle'));
    }

    // ─── Chantier avancer étape ────────────────────────────────────

    public function test_super_admin_can_update_chantier_etape_avancement(): void
    {
        $customer = $this->makeCustomer();
        $devis = $this->makeDevis($customer->getKey());

        $bc = BonCommande::create([
            'instance_id' => $this->instance->id,
            'numero' => 'BC-CH-001',
            'devis_id' => $devis->getKey(),
            'client_id' => $customer->getKey(),
            'statut' => 'cree',
            'montant_ht' => 100000,
            'taux_tva' => 0.18,
            'montant_tva' => 18000,
            'montant_ttc' => 118000,
            'acompte_pct' => 30,
        ]);

        $chantier = Chantier::create([
            'instance_id' => $this->instance->id,
            'numero' => 'CH-TEST-001',
            'bc_id' => $bc->getKey(),
            'client_id' => $customer->getKey(),
            'statut' => 'en_cours',
        ]);

        $etape = EtapeChantier::create([
            'instance_id' => $this->instance->id,
            'chantier_id' => $chantier->getKey(),
            'nom' => 'Pose menuiseries',
            'ordre' => 1,
            'avancement_pct' => 0,
            'statut' => 'a_faire',
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.chantiers.avancer', ['slug' => $this->slug(), 'chantier' => $chantier->getKey()]), [
                'etape_id' => $etape->getKey(),
                'avancement_pct' => 50,
                'notes' => 'Premier tiers posé',
            ])
            ->assertRedirect();

        $etape->refresh();
        $this->assertSame(50, $etape->getAttribute('avancement_pct'));
        $this->assertSame('en_cours', $etape->getAttribute('statut'));
        $this->assertNotNull($etape->getAttribute('demarree_at'));
    }

    public function test_chantier_etape_at_100_pct_is_marked_fait(): void
    {
        $customer = $this->makeCustomer();
        $devis = $this->makeDevis($customer->getKey());

        $bc = BonCommande::create([
            'instance_id' => $this->instance->id,
            'numero' => 'BC-CH-100',
            'devis_id' => $devis->getKey(),
            'client_id' => $customer->getKey(),
            'statut' => 'cree',
            'montant_ht' => 100000,
            'taux_tva' => 0.18,
            'montant_tva' => 18000,
            'montant_ttc' => 118000,
            'acompte_pct' => 30,
        ]);

        $chantier = Chantier::create([
            'instance_id' => $this->instance->id,
            'numero' => 'CH-TEST-100',
            'bc_id' => $bc->getKey(),
            'client_id' => $customer->getKey(),
            'statut' => 'en_cours',
        ]);

        $etape = EtapeChantier::create([
            'instance_id' => $this->instance->id,
            'chantier_id' => $chantier->getKey(),
            'nom' => 'Finitions',
            'ordre' => 2,
            'avancement_pct' => 50,
            'statut' => 'en_cours',
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.chantiers.avancer', ['slug' => $this->slug(), 'chantier' => $chantier->getKey()]), [
                'etape_id' => $etape->getKey(),
                'avancement_pct' => 100,
            ])
            ->assertRedirect();

        $etape->refresh();
        $this->assertSame(100, $etape->getAttribute('avancement_pct'));
        $this->assertSame('fait', $etape->getAttribute('statut'));
        $this->assertNotNull($etape->getAttribute('terminee_at'));
    }

    // ─── Validation ────────────────────────────────────────────────

    public function test_devis_store_fails_validation_when_lignes_missing(): void
    {
        $customer = $this->makeCustomer();

        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.devis.store', ['slug' => $this->slug()]), [
                'client_id' => $customer->getKey(),
                'lignes' => [],
            ])
            ->assertSessionHasErrors(['lignes']);
    }

    public function test_stock_recevoir_refuses_zero_quantity(): void
    {
        $matiere = $this->makeMatiere();

        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.stocks.recevoir', ['slug' => $this->slug(), 'matiere' => $matiere->getKey()]), [
                'quantite' => 0,
                'reference' => 'PO-ZERO',
            ])
            ->assertSessionHasErrors(['quantite']);
    }

    // ─── Matière CRUD (M-UI-2) ────────────────────────────────────

    public function test_super_admin_can_view_matiere_create_form(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.stocks.create', ['slug' => $this->slug()]))
            ->assertOk();
    }

    public function test_super_admin_can_store_new_matiere(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.stocks.store', ['slug' => $this->slug()]), [
                'code' => 'ALU-NEW-01',
                'designation' => 'Profil neuf de test',
                'categorie' => 'profile_alu',
                'unite' => 'm_lineaire',
                'prix_unitaire' => 2500,
                'seuil_alerte' => 10,
                'fournisseur_principal' => 'Fournisseur A',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('mnu_matieres_premieres', [
            'instance_id' => $this->instance->id,
            'code' => 'ALU-NEW-01',
            'designation' => 'Profil neuf de test',
        ]);
    }

    public function test_matiere_store_validates_required_fields(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.stocks.store', ['slug' => $this->slug()]), [])
            ->assertSessionHasErrors(['code', 'designation', 'categorie', 'unite', 'prix_unitaire', 'seuil_alerte']);
    }

    public function test_matiere_store_rejects_duplicate_code_in_same_instance(): void
    {
        $this->makeMatiere(); // crée 'TEST-PROFIL'

        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.stocks.store', ['slug' => $this->slug()]), [
                'code' => 'TEST-PROFIL', // doublon
                'designation' => 'Doublon',
                'categorie' => 'profile_alu',
                'unite' => 'm_lineaire',
                'prix_unitaire' => 1000,
                'seuil_alerte' => 5,
            ])
            ->assertSessionHasErrors(['code']);
    }

    public function test_super_admin_can_view_matiere_edit_form(): void
    {
        $matiere = $this->makeMatiere();

        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.stocks.edit', ['slug' => $this->slug(), 'matiere' => $matiere->getKey()]))
            ->assertOk();
    }

    public function test_super_admin_can_update_matiere(): void
    {
        $matiere = $this->makeMatiere();

        $this->actingAs($this->superAdmin)
            ->put(route('menuiserie.stocks.update', ['slug' => $this->slug(), 'matiere' => $matiere->getKey()]), [
                'code' => $matiere->getAttribute('code'),
                'designation' => 'Désignation mise à jour',
                'categorie' => 'profile_alu',
                'unite' => 'm_lineaire',
                'prix_unitaire' => 3000,
                'seuil_alerte' => 25,
                'is_active' => '0',
            ])
            ->assertRedirect();

        $matiere->refresh();
        $this->assertSame('Désignation mise à jour', $matiere->getAttribute('designation'));
        $this->assertEqualsWithDelta(3000, (float) $matiere->getAttribute('prix_unitaire'), 0.0001);
        $this->assertFalse((bool) $matiere->getAttribute('is_active'));
    }

    public function test_super_admin_can_destroy_unused_matiere(): void
    {
        $matiere = $this->makeMatiere();

        $this->actingAs($this->superAdmin)
            ->delete(route('menuiserie.stocks.destroy', ['slug' => $this->slug(), 'matiere' => $matiere->getKey()]))
            ->assertRedirect(route('menuiserie.stocks.index', ['slug' => $this->slug()]));

        $this->assertSoftDeleted('mnu_matieres_premieres', ['id' => $matiere->getKey()]);
    }

    public function test_matiere_destroy_blocked_when_stock_not_zero(): void
    {
        $matiere = $this->makeMatiere();

        // Réception fournisseur → stock > 0
        app(StockMatiereService::class)->recevoir(
            (int) $this->instance->id,
            (int) $matiere->getKey(),
            50.0,
            'PO-001',
        );

        $this->actingAs($this->superAdmin)
            ->delete(route('menuiserie.stocks.destroy', ['slug' => $this->slug(), 'matiere' => $matiere->getKey()]))
            ->assertRedirect();

        $this->assertDatabaseHas('mnu_matieres_premieres', [
            'id' => $matiere->getKey(),
            'deleted_at' => null,
        ]);
    }

    public function test_matiere_destroy_blocked_when_referenced_in_devis(): void
    {
        $matiere = $this->makeMatiere();
        $customer = $this->makeCustomer();
        $devis = $this->makeDevis($customer->getKey());

        // Lie une ligne de devis à la matière
        LigneDevis::create([
            'instance_id' => $this->instance->id,
            'devis_id' => $devis->getKey(),
            'designation' => 'Ligne référençant la matière',
            'quantite' => 1,
            'prix_unitaire_ht' => 50000,
            'montant_ht' => 50000,
            'cout_revient' => 30000,
            'matiere_id' => $matiere->getKey(),
            'ordre' => 2,
        ]);

        $this->actingAs($this->superAdmin)
            ->delete(route('menuiserie.stocks.destroy', ['slug' => $this->slug(), 'matiere' => $matiere->getKey()]))
            ->assertRedirect();

        $this->assertDatabaseHas('mnu_matieres_premieres', [
            'id' => $matiere->getKey(),
            'deleted_at' => null,
        ]);
    }

    // ─── Helpers ───────────────────────────────────────────────────

    private function makeCustomer(): Customer
    {
        // S'assurer que les permissions sont initialisées (instance-admin context)
        TeamContext::set(0);
        Role::findOrCreate('agent');

        return Customer::withoutGlobalScopes()->create([
            'instance_id' => $this->instance->id,
            'code' => 'TEST-CUS-001',
            'name' => 'Test Client',
            'is_active' => true,
        ]);
    }

    private function makeDevis(int $customerId): Devis
    {
        $devis = Devis::create([
            'instance_id' => $this->instance->id,
            'numero' => 'DEV-TEST-001',
            'client_id' => $customerId,
            'statut' => StatutDevis::BROUILLON->value,
            'taux_tva' => 0.18,
            'montant_ht' => 100000,
            'montant_tva' => 18000,
            'montant_ttc' => 118000,
            'validite_jours' => 30,
            'marge_minimum' => 0.15,
        ]);

        LigneDevis::create([
            'instance_id' => $this->instance->id,
            'devis_id' => $devis->getKey(),
            'designation' => 'Fenêtre 1200x1500',
            'quantite' => 1,
            'prix_unitaire_ht' => 100000,
            'montant_ht' => 100000,
            'cout_revient' => 70000,
            'ordre' => 1,
        ]);

        return $devis;
    }

    private function makeMatiere(): MatierePremiere
    {
        return MatierePremiere::create([
            'instance_id' => $this->instance->id,
            'code' => 'TEST-PROFIL',
            'designation' => 'Profil test',
            'categorie' => 'profile_alu',
            'unite' => 'm_lineaire',
            'prix_unitaire' => 2000,
            'seuil_alerte' => 20,
            'is_active' => true,
        ]);
    }
}

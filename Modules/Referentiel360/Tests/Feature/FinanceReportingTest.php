<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocument;
use Modules\Referentiel360\Domain\Party\Models\Party;
use Modules\Referentiel360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

/**
 * ADR-031 — Page de reporting financier consolidé (lecture seule).
 *
 * Vérifie : accès super-admin (200 + données par devise/tiers/module/statut),
 * accès via permission granulaire `referentiel.finance.view` (200), refus sans
 * permission (403), et isolation multi-tenant (les montants d'une autre instance
 * n'apparaissent pas).
 */
final class FinanceReportingTest extends TestCase
{
    private Instance $instance;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->instance = $this->makeRootInstance();
        CurrentInstance::set($this->instance);
        TeamContext::set(0);
    }

    private function slug(): string
    {
        return (string) $this->instance->getAttribute('slug');
    }

    private function makeUserInInstance(Instance $instance, string $email): User
    {
        $user = User::create([
            'full_name' => 'Tester',
            'email' => $email,
            'password' => 'password',
        ]);

        DB::connection('system')->table('instance_user')->updateOrInsert(
            ['instance_id' => $instance->id, 'user_id' => $user->id],
            ['status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        );

        return $user;
    }

    /**
     * Crée un tiers + un document financier miroir dans l'instance courante.
     */
    private function makeFinanceDocument(
        Instance $instance,
        string $documentNumber,
        string $currency,
        string $ttc,
        string $paid,
        ?int $partyId = null,
        string $sourceModule = 'menuiserie',
        string $docType = 'invoice',
        string $status = 'issued',
    ): FinanceDocument {
        $due = number_format(max(0, (float) $ttc - (float) $paid), 2, '.', '');

        return FinanceDocument::create([
            'instance_id' => $instance->id,
            'party_id' => $partyId,
            'doc_type' => $docType,
            'document_number' => $documentNumber,
            'currency' => $currency,
            'amount_ht' => $ttc,
            'amount_tax' => '0',
            'amount_ttc' => $ttc,
            'paid_amount' => $paid,
            'due_amount' => $due,
            'status_normalized' => $status,
            'is_cancelled' => false,
            'source_module' => $sourceModule,
        ]);
    }

    private function makeParty(Instance $instance, string $name): Party
    {
        return Party::create([
            'instance_id' => $instance->id,
            'is_customer' => true,
            'is_supplier' => false,
            'person_type' => 'company',
            'display_name' => $name,
            'country' => 'CI',
            'is_active' => true,
        ]);
    }

    public function test_super_admin_sees_reporting_with_totals(): void
    {
        $superAdmin = $this->makeRootSuperAdmin($this->instance);
        CurrentInstance::set($this->instance);
        TeamContext::set(0);

        $party = $this->makeParty($this->instance, 'Client Alpha');
        $this->makeFinanceDocument($this->instance, 'INV-001', 'XOF', '100000.00', '40000.00', $party->getKey());
        $this->makeFinanceDocument($this->instance, 'INV-002', 'XOF', '50000.00', '50000.00', null, 'eshop', 'invoice', 'paid');

        $response = $this->actingAs($superAdmin)
            ->get(route('referentiel.finance.reporting', ['slug' => $this->slug()]));

        $response->assertOk();
        $response->assertSee('Finance consolidée', escape: false);
        $response->assertSee('Client Alpha', escape: false);
        $response->assertSee('(non rattaché)', escape: false);
        $response->assertSee('XOF', escape: false);
        // CA total XOF = 150 000,00 (formaté avec espace insécable du number_format).
        $response->assertViewHas('totalsByCurrency');
        $totals = $response->viewData('totalsByCurrency');
        $this->assertSame('150000.00', $totals['XOF']['ttc']);
        $this->assertSame('90000.00', $totals['XOF']['paid']);
        $this->assertSame('60000.00', $totals['XOF']['due']);

        // Lignes par tiers : 2 (Client Alpha + non rattaché), une devise chacune.
        $byParty = $response->viewData('byParty');
        $this->assertCount(2, $byParty);
    }

    public function test_user_with_permission_can_view_reporting(): void
    {
        $user = $this->makeUserInInstance($this->instance, 'viewer@ref.test');

        TeamContext::set($this->instance->id);
        Permission::findOrCreate('referentiel.finance.view');
        $user->givePermissionTo('referentiel.finance.view');

        $this->makeFinanceDocument($this->instance, 'INV-010', 'EUR', '200.00', '0.00');

        $response = $this->actingAs($user)
            ->get(route('referentiel.finance.reporting', ['slug' => $this->slug()]));

        $response->assertOk();
        $response->assertSee('EUR', escape: false);
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $user = $this->makeUserInInstance($this->instance, 'noperm@ref.test');

        $this->actingAs($user)
            ->get(route('referentiel.finance.reporting', ['slug' => $this->slug()]))
            ->assertForbidden();
    }

    public function test_tenant_isolation_other_instance_totals_not_visible(): void
    {
        $other = Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-'.uniqid(),
            'is_active' => true,
            'meta' => [],
        ]);

        // Document dans l'AUTRE instance (montant distinctif).
        CurrentInstance::set($other);
        $this->makeFinanceDocument($other, 'OTHER-001', 'XOF', '999999.00', '0.00');

        // Document dans l'instance courante.
        CurrentInstance::set($this->instance);
        $this->makeFinanceDocument($this->instance, 'INV-100', 'XOF', '12345.00', '0.00');

        $superAdmin = $this->makeRootSuperAdmin($this->instance);
        CurrentInstance::set($this->instance);
        TeamContext::set(0);

        $response = $this->actingAs($superAdmin)
            ->get(route('referentiel.finance.reporting', ['slug' => $this->slug()]));

        $response->assertOk();
        $totals = $response->viewData('totalsByCurrency');
        $this->assertSame('12345.00', $totals['XOF']['ttc']);
        $response->assertDontSee('999 999', escape: false);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature\Reporting;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Menuiserie360\Domain\Chantier\Enums\StatutChantier;
use Modules\Menuiserie360\Domain\Chantier\Models\Chantier;
use Modules\Menuiserie360\Domain\Production\Enums\StatutOrdreFabrication;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabrication;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Menuiserie360\Domain\Stock\Models\StockMatiere;
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P3-6 — Tests dashboard opérationnel (OFs, chantiers retard, stocks bas).
 */
final class DashboardOperationsTest extends TestCase
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

    public function test_operations_dashboard_lists_pending_ofs_and_late_chantiers(): void
    {
        OrdreFabrication::create([
            'instance_id' => $this->instance->id,
            'numero' => 'OF-WAITING',
            'bc_id' => 1,
            'statut' => StatutOrdreFabrication::EN_ATTENTE->value,
        ]);

        Chantier::create([
            'instance_id' => $this->instance->id,
            'numero' => 'CH-LATE',
            'bc_id' => 1,
            'client_id' => 1,
            'statut' => StatutChantier::EN_COURS->value,
            'date_fin_prevue' => now()->subDays(10)->toDateString(),
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.reporting.operations', ['slug' => $this->instance->getAttribute('slug')]))
            ->assertOk()
            ->assertSee('OF-WAITING')
            ->assertSee('CH-LATE');
    }

    public function test_operations_dashboard_shows_low_stock_below_threshold(): void
    {
        $matiere = MatierePremiere::create([
            'instance_id' => $this->instance->id,
            'code' => 'PROFIL-LOW',
            'designation' => 'Profil bas',
            'categorie' => 'profile_alu',
            'unite' => 'm_lineaire',
            'seuil_alerte' => 100,
        ]);

        StockMatiere::create([
            'instance_id' => $this->instance->id,
            'matiere_id' => $matiere->getKey(),
            'quantite_actuelle' => 25,
            'quantite_reservee' => 0,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.reporting.operations', ['slug' => $this->instance->getAttribute('slug')]))
            ->assertOk()
            ->assertSee('PROFIL-LOW');
    }

    public function test_operations_dashboard_hides_chantiers_not_late(): void
    {
        Chantier::create([
            'instance_id' => $this->instance->id,
            'numero' => 'CH-ON-TRACK',
            'bc_id' => 1,
            'client_id' => 1,
            'statut' => StatutChantier::EN_COURS->value,
            'date_fin_prevue' => now()->addDays(10)->toDateString(),
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.reporting.operations', ['slug' => $this->instance->getAttribute('slug')]))
            ->assertOk()
            ->assertDontSee('CH-ON-TRACK');
    }
}

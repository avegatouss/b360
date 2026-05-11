<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature\Http;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Menuiserie360\Domain\Commercial\Enums\CategorieProduit;
use Modules\Menuiserie360\Domain\Commercial\Models\TypeProduitMenuiserie;
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P2-C step 1 — Tests HTTP TypeProduitController (CRUD bibliothèque).
 */
final class TypeProduitControllerTest extends TestCase
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

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get(route('menuiserie.types-produits.index', ['slug' => $this->slug()]))
            ->assertRedirect();
    }

    public function test_super_admin_can_access_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('menuiserie.types-produits.index', ['slug' => $this->slug()]))
            ->assertOk();
    }

    public function test_super_admin_can_create_type_produit(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(
            route('menuiserie.types-produits.store', ['slug' => $this->slug()]),
            [
                'code' => 'FEN-COUL-001',
                'nom' => 'Fenêtre coulissante 1 vantail',
                'categorie' => CategorieProduit::FENETRE->value,
                'description' => 'Standard aluminium thermolaqué',
                'largeur_standard_mm' => 1200,
                'hauteur_standard_mm' => 1000,
                'prix_indicatif_ht' => 185000,
                'cout_indicatif' => 110000,
            ]
        );

        $type = TypeProduitMenuiserie::where('code', 'FEN-COUL-001')->firstOrFail();
        $response->assertRedirect(route('menuiserie.types-produits.show', [
            'slug' => $this->slug(),
            'type' => $type->getKey(),
        ]));

        $this->assertSame($this->instance->id, $type->getAttribute('instance_id'));
        $this->assertSame('Fenêtre coulissante 1 vantail', $type->getAttribute('nom'));
        $this->assertTrue((bool) $type->getAttribute('is_active'));
    }

    public function test_super_admin_can_update_type_produit(): void
    {
        $type = TypeProduitMenuiserie::create([
            'instance_id' => $this->instance->id,
            'code' => 'PRT-001',
            'nom' => 'Porte simple',
            'categorie' => CategorieProduit::PORTE->value,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->put(
            route('menuiserie.types-produits.update', [
                'slug' => $this->slug(),
                'type' => $type->getKey(),
            ]),
            [
                'nom' => 'Porte simple renommée',
                'categorie' => CategorieProduit::PORTE->value,
                'prix_indicatif_ht' => 220000,
                'is_active' => '1',
            ]
        );

        $response->assertRedirect();
        $type->refresh();
        $this->assertSame('Porte simple renommée', $type->getAttribute('nom'));
        $this->assertSame('220000.00', (string) $type->getAttribute('prix_indicatif_ht'));
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('menuiserie.types-produits.store', ['slug' => $this->slug()]), [])
            ->assertSessionHasErrors(['code', 'nom', 'categorie']);
    }

    public function test_super_admin_can_archive_type_produit(): void
    {
        $type = TypeProduitMenuiserie::create([
            'instance_id' => $this->instance->id,
            'code' => 'GC-001',
            'nom' => 'Garde-corps inox',
            'categorie' => CategorieProduit::GARDE_CORPS->value,
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->delete(route('menuiserie.types-produits.destroy', [
                'slug' => $this->slug(),
                'type' => $type->getKey(),
            ]))
            ->assertRedirect(route('menuiserie.types-produits.index', ['slug' => $this->slug()]));

        $this->assertSoftDeleted('mnu_types_produits_menuiserie', ['id' => $type->getKey()]);
    }
}

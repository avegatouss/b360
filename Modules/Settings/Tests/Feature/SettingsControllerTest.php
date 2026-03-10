<?php

namespace Modules\Settings\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\TeamContext;
use Modules\Settings\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class SettingsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.installed' => true]);
    }

    private function makeRootInstance(): Instance
    {
        return Instance::create([
            'name' => 'Root',
            'slug' => 'root',
            'is_active' => true,
            'meta' => ['is_root' => true],
        ]);
    }

    private function makeRootSuperAdmin(Instance $root): User
    {
        $user = User::create([
            'full_name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        TeamContext::clear();
        Role::findOrCreate('super-admin');
        $user->assignRole('super-admin');

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $root->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    public function test_index_redirects_to_first_settings_group(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/settings")
            ->assertRedirect("/i/{$root->slug}/settings/general");
    }

    public function test_group_page_is_accessible(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/settings/general")
            ->assertOk();
    }

    public function test_instances_group_is_visible_for_root_super_admin(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/settings/instances")
            ->assertOk();
    }

    public function test_update_group_persists_settings(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->put("/i/{$root->slug}/settings/general", [
                'settings' => ['site_name' => 'B360'],
                'types' => ['site_name' => 'string'],
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('settings', [
            'group' => 'general',
            'key' => 'site_name',
            'value' => 'B360',
        ]);
    }
}

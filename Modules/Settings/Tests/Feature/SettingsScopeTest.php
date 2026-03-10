<?php

namespace Modules\Settings\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\TeamContext;
use Modules\Settings\Services\SettingsManager;
use Modules\Settings\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class SettingsScopeTest extends TestCase
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

    private function makeInstance(string $slug): Instance
    {
        return Instance::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function makeSuperAdmin(Instance $instance): User
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
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function makeInstanceAdmin(Instance $instance): User
    {
        $user = User::create([
            'full_name' => 'Instance Admin',
            'email' => 'instance-admin@test.com',
            'password' => 'password',
        ]);

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    public function test_root_settings_are_saved_with_instance_id_zero(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeSuperAdmin($root);

        $this->actingAs($user)
            ->put("/i/{$root->slug}/settings/general", [
                'settings' => ['app_name' => 'B360 Global'],
                'types' => ['app_name' => 'string'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'instance_id' => 0,
            'group' => 'general',
            'key' => 'app_name',
            'value' => 'B360 Global',
        ]);
    }

    public function test_instance_settings_are_saved_with_instance_id(): void
    {
        // Settings routes require root + super-admin (EnsureRootSuperAdmin middleware).
        // Instance-scoped settings are managed via SettingsManager directly.
        $settings = app(SettingsManager::class);

        $instance = $this->makeInstance('acme');

        // Save setting scoped to instance
        $settings->set('general.app_name', 'Acme Corp', $instance->id, 'string');

        $this->assertDatabaseHas('settings', [
            'instance_id' => $instance->id,
            'group' => 'general',
            'key' => 'app_name',
            'value' => 'Acme Corp',
        ]);

        // Global should not be affected
        $this->assertDatabaseMissing('settings', [
            'instance_id' => 0,
            'group' => 'general',
            'key' => 'app_name',
            'value' => 'Acme Corp',
        ]);
    }

    public function test_settings_manager_cascades_instance_to_global(): void
    {
        $settings = app(SettingsManager::class);

        // Set global value
        $settings->set('general.app_name', 'Global', 0, 'string');

        // Without instance → returns global
        $this->assertSame('Global', $settings->get('general.app_name'));

        // With instance that has no override → returns global
        $this->assertSame('Global', $settings->get('general.app_name', null, 99));

        // Set instance override
        $settings->set('general.app_name', 'Instance Override', 99, 'string');

        // With instance → returns instance value
        $this->assertSame('Instance Override', $settings->get('general.app_name', null, 99));

        // Global still unchanged
        $this->assertSame('Global', $settings->get('general.app_name'));
    }

    public function test_currency_helper_cascades_correctly(): void
    {
        $settings = app(SettingsManager::class);

        // No setting → config fallback
        config(['billing.currency' => 'EUR']);
        $this->assertSame('EUR', currency());

        // Set global billing currency
        $settings->set('billing.currency', 'USD', 0, 'string');
        $this->assertSame('USD', currency());
    }
}

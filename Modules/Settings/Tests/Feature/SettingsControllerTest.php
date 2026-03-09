<?php

namespace Modules\Settings\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    public function test_branding_group_page_is_accessible(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/settings/branding")
            ->assertOk()
            ->assertSee('Informations de la plateforme')
            ->assertSee('Logos et images');
    }

    public function test_branding_update_persists_text_settings(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->put("/i/{$root->slug}/settings/branding", [
                'settings' => [
                    'platform_name' => 'My SaaS',
                    'platform_description' => 'The best platform',
                    'contact_email' => 'contact@mysaas.com',
                ],
                'types' => [
                    'platform_name' => 'string',
                    'platform_description' => 'string',
                    'contact_email' => 'string',
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'group' => 'branding',
            'key' => 'platform_name',
            'value' => 'My SaaS',
        ]);
        $this->assertDatabaseHas('settings', [
            'group' => 'branding',
            'key' => 'contact_email',
            'value' => 'contact@mysaas.com',
        ]);
    }

    public function test_branding_upload_logo_stores_file_and_saves_path(): void
    {
        Storage::fake('public');

        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $logo = UploadedFile::fake()->image('logo.png', 200, 60);

        $this->actingAs($user)
            ->put("/i/{$root->slug}/settings/branding", [
                'settings' => [],
                'types' => [],
                'files' => ['logo' => $logo],
            ])
            ->assertRedirect();

        // Verify file was stored
        $setting = DB::connection('system')->table('settings')
            ->where('group', 'branding')
            ->where('key', 'logo')
            ->first();

        $this->assertNotNull($setting);
        $this->assertStringStartsWith('branding/branding/', $setting->value);
        Storage::disk('public')->assertExists($setting->value);
    }

    public function test_branding_upload_favicon_stores_file(): void
    {
        Storage::fake('public');

        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $favicon = UploadedFile::fake()->image('favicon.png', 32, 32);

        $this->actingAs($user)
            ->put("/i/{$root->slug}/settings/branding", [
                'settings' => [],
                'types' => [],
                'files' => ['favicon' => $favicon],
            ])
            ->assertRedirect();

        $setting = DB::connection('system')->table('settings')
            ->where('group', 'branding')
            ->where('key', 'favicon')
            ->first();

        $this->assertNotNull($setting);
        Storage::disk('public')->assertExists($setting->value);
    }

    public function test_branding_upload_multiple_files_at_once(): void
    {
        Storage::fake('public');

        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $logo = UploadedFile::fake()->image('logo.png', 200, 60);
        $logoDark = UploadedFile::fake()->image('logo-dark.png', 200, 60);
        $loginCover = UploadedFile::fake()->image('cover.jpg', 1920, 1080);

        $this->actingAs($user)
            ->put("/i/{$root->slug}/settings/branding", [
                'settings' => ['platform_name' => 'Multi Upload'],
                'types' => ['platform_name' => 'string'],
                'files' => [
                    'logo' => $logo,
                    'logo_dark' => $logoDark,
                    'login_cover' => $loginCover,
                ],
            ])
            ->assertRedirect();

        // Text setting saved
        $this->assertDatabaseHas('settings', [
            'group' => 'branding',
            'key' => 'platform_name',
            'value' => 'Multi Upload',
        ]);

        // All three files saved
        foreach (['logo', 'logo_dark', 'login_cover'] as $key) {
            $setting = DB::connection('system')->table('settings')
                ->where('group', 'branding')
                ->where('key', $key)
                ->first();

            $this->assertNotNull($setting, "Setting branding.{$key} should exist");
            Storage::disk('public')->assertExists($setting->value);
        }
    }
}

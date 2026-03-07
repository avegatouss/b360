<?php

namespace Modules\Core\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\Core\Database\Seeders\CoreRbacSeeder;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Core\Tests\TestCase;

final class EnsureInstanceMembershipActiveMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'core.instance.member'])
            ->get('/__test/membership', fn () => response('ok'));
    }

    private function makeInstance(string $slug = 'acme'): Instance
    {
        return Instance::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function makeUser(string $email = 'member@test.com'): User
    {
        return User::create([
            'full_name' => 'Member',
            'email' => $email,
            'password' => 'password',
        ]);
    }

    public function test_allows_active_member(): void
    {
        $instance = $this->makeInstance();
        $user = $this->makeUser();

        CurrentInstance::set($instance);

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/__test/membership')
            ->assertOk()
            ->assertSee('ok');
    }

    public function test_blocks_inactive_member(): void
    {
        $instance = $this->makeInstance('blocked');
        $user = $this->makeUser('blocked@test.com');

        CurrentInstance::set($instance);

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'disabled',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/__test/membership')
            ->assertStatus(403);
    }

    public function test_super_admin_bypasses_membership(): void
    {
        $instance = $this->makeInstance('rooty');
        $user = $this->makeUser('sa@test.com');

        $this->seed(CoreRbacSeeder::class);

        TeamContext::clear();
        $user->assignRole('super-admin');

        CurrentInstance::set($instance);

        $this->actingAs($user)
            ->get('/__test/membership')
            ->assertOk();
    }
}

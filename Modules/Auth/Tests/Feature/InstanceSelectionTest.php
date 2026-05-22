<?php

namespace Modules\Auth\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Tests\TestCase;

final class InstanceSelectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.installed' => true]);
    }

    private function makeUser(string $email = 'user@test.com'): User
    {
        return User::create([
            'full_name' => 'User',
            'email' => $email,
            'password' => 'password',
        ]);
    }

    private function makeInstance(string $slug, bool $active = true): Instance
    {
        return Instance::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => $active,
        ]);
    }

    private function attachActiveMembership(User $user, Instance $instance): void
    {
        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_select_redirects_when_no_active_instances(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get('/instances/select')
            ->assertRedirect(route('instances.no_active'));
    }

    public function test_select_redirects_when_single_active_instance(): void
    {
        $user = $this->makeUser('one@test.com');
        $instance = $this->makeInstance('solo');
        $this->attachActiveMembership($user, $instance);

        $this->actingAs($user)
            ->get('/instances/select')
            ->assertRedirect('/i/solo');
    }

    public function test_select_shows_list_when_multiple_instances(): void
    {
        $user = $this->makeUser('multi@test.com');
        $first = $this->makeInstance('alpha');
        $second = $this->makeInstance('beta');
        $this->attachActiveMembership($user, $first);
        $this->attachActiveMembership($user, $second);

        $this->actingAs($user)
            ->get('/instances/select')
            ->assertOk()
            ->assertSee('alpha')
            ->assertSee('beta');
    }

    public function test_choose_denies_slug_not_owned(): void
    {
        $user = $this->makeUser('deny@test.com');
        $this->makeInstance('owned');
        $this->makeInstance('other');

        $this->actingAs($user)
            ->post('/instances/select', ['slug' => 'other'])
            ->assertStatus(403);
    }

    public function test_choose_redirects_when_slug_owned(): void
    {
        $user = $this->makeUser('ok@test.com');
        $instance = $this->makeInstance('owned');
        $this->attachActiveMembership($user, $instance);

        $this->actingAs($user)
            ->post('/instances/select', ['slug' => 'owned'])
            ->assertRedirect('/i/owned');
    }
}

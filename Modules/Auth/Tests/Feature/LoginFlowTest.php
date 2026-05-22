<?php

namespace Modules\Auth\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Tests\TestCase;

final class LoginFlowTest extends TestCase
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

    public function test_login_page_is_accessible_when_installed(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->makeUser('valid@test.com');

        $this->post('/login', [
            'email' => 'valid@test.com',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');
    }

    public function test_login_redirects_to_no_active_instances(): void
    {
        $user = $this->makeUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('instances.no_active'));
    }

    public function test_login_redirects_to_single_instance(): void
    {
        $user = $this->makeUser('one@test.com');
        $instance = $this->makeInstance('solo');
        $this->attachActiveMembership($user, $instance);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/i/solo');
    }

    public function test_login_redirects_to_instance_select_when_multiple(): void
    {
        $user = $this->makeUser('multi@test.com');
        $first = $this->makeInstance('first');
        $second = $this->makeInstance('second');

        $this->attachActiveMembership($user, $first);
        $this->attachActiveMembership($user, $second);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('instances.select'));
    }

    public function test_instance_login_404_when_instance_inactive(): void
    {
        $user = $this->makeUser('inactive@test.com');
        $this->makeInstance('inactive', false);

        $this->post('/i/inactive/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(404);
    }

    public function test_instance_login_blocks_when_user_not_member(): void
    {
        $user = $this->makeUser('nomember@test.com');
        $this->makeInstance('acme');

        $this->post('/i/acme/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_instance_login_allows_active_member(): void
    {
        $user = $this->makeUser('member@test.com');
        $instance = $this->makeInstance('acme2');
        $this->attachActiveMembership($user, $instance);

        $this->post('/i/acme2/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/i/acme2');
    }
}

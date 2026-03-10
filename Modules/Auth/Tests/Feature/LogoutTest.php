<?php

namespace Modules\Auth\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Modules\Auth\Tests\TestCase;

final class LogoutTest extends TestCase
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

    private function makeInstance(string $slug): Instance
    {
        return Instance::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    public function test_instance_logout_redirects_to_instance_login(): void
    {
        $user = $this->makeUser();
        $this->makeInstance('acme');

        $this->actingAs($user)
            ->get('/i/acme/logout')
            ->assertRedirect('/i/acme/login');

        $this->assertGuest();
    }

    public function test_instance_logout_returns_404_for_unknown_instance(): void
    {
        $user = $this->makeUser('unknown@test.com');

        $this->actingAs($user)
            ->get('/i/missing/logout')
            ->assertStatus(404);
    }

    public function test_global_logout_redirects_to_login(): void
    {
        $user = $this->makeUser('global@test.com');

        $this->actingAs($user)
            ->get('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}

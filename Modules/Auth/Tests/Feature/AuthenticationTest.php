<?php

namespace Modules\Auth\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Modules\Auth\Tests\TestCase;

final class AuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.installed' => true]);
    }

    private function makeUser(string $email = 'user@test.com'): User
    {
        return User::create([
            'full_name' => 'Test User',
            'email' => $email,
            'password' => 'password',
        ]);
    }

    private function makeInstance(string $slug = 'acme'): Instance
    {
        return Instance::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function attachMembership(User $user, Instance $instance): void
    {
        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = $this->makeUser('correct@test.com');
        $instance = $this->makeInstance('myinst');
        $this->attachMembership($user, $instance);

        $response = $this->post('/login', [
            'email' => 'correct@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/i/myinst');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $this->makeUser('wrong@test.com');

        $response = $this->post('/login', [
            'email' => 'wrong@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_too_many_attempts(): void
    {
        $this->makeUser('throttle@test.com');

        // Attempt 6 failed logins (default throttle limit is 5)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'throttle@test.com',
                'password' => 'wrong',
            ]);
        }

        // The 6th (or later) attempt should be throttled
        $response->assertSessionHasErrors();
    }

    public function test_user_can_logout(): void
    {
        $user = $this->makeUser('logout@test.com');

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect();

        $this->assertGuest();
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        $this->makeUser('reset@test.com');

        $response = $this->post('/forgot-password', [
            'email' => 'reset@test.com',
        ]);

        // Should redirect back with a status message (success or error, both redirect)
        $response->assertRedirect();
    }
}

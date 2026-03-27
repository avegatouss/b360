<?php

namespace Modules\Auth\Tests\Feature;

use App\Models\User;
use Modules\Auth\Tests\TestCase;

final class LockscreenTest extends TestCase
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

    public function test_user_can_lock_screen(): void
    {
        $user = $this->makeUser('lock@test.com');

        $response = $this->actingAs($user)
            ->post('/lockscreen/lock');

        $response->assertRedirect(route('lockscreen'));
        $this->assertTrue(session('screen_locked'));
    }

    public function test_locked_user_is_redirected_to_lockscreen(): void
    {
        $user = $this->makeUser('locked@test.com');

        $response = $this->actingAs($user)
            ->withSession(['screen_locked' => true])
            ->get('/lockscreen');

        $response->assertOk();
    }

    public function test_unlocked_user_redirected_away_from_lockscreen(): void
    {
        $user = $this->makeUser('unlocked@test.com');

        // Without screen_locked in session, show() redirects to /
        $response = $this->actingAs($user)
            ->get('/lockscreen');

        $response->assertRedirect('/');
    }

    public function test_user_can_unlock_with_correct_password(): void
    {
        $user = $this->makeUser('unlock@test.com');

        $response = $this->actingAs($user)
            ->withSession(['screen_locked' => true, 'locked_at' => now()])
            ->post('/lockscreen/unlock', [
                'password' => 'password',
            ]);

        $response->assertRedirect('/');
        $this->assertFalse(session()->has('screen_locked'));
    }

    public function test_unlock_fails_with_wrong_password(): void
    {
        $user = $this->makeUser('wrongpwd@test.com');

        $response = $this->actingAs($user)
            ->withSession(['screen_locked' => true])
            ->post('/lockscreen/unlock', [
                'password' => 'wrongpassword',
            ]);

        $response->assertSessionHasErrors('password');
    }
}

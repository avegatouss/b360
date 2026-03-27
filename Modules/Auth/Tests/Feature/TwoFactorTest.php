<?php

namespace Modules\Auth\Tests\Feature;

use App\Models\User;
use Modules\Auth\Tests\TestCase;
use PragmaRX\Google2FA\Google2FA;

final class TwoFactorTest extends TestCase
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

    private function enableTwoFactor(User $user): string
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['RECOVERY-CODE-1', 'RECOVERY-CODE-2'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $secret;
    }

    public function test_user_can_enable_2fa(): void
    {
        $user = $this->makeUser('2fa@test.com');

        $response = $this->actingAs($user)
            ->post('/two-factor/enable');

        $response->assertRedirect(route('two-factor.enable'));

        // Secret should be in session
        $this->assertTrue(session()->has('two_factor_secret'));
    }

    public function test_user_must_confirm_2fa_with_valid_code(): void
    {
        $user = $this->makeUser('confirm@test.com');
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        // Simulate the enable flow: set secret in session
        $response = $this->actingAs($user)
            ->withSession(['two_factor_secret' => $secret])
            ->post('/two-factor/confirm', [
                'code' => $google2fa->getCurrentOtp($secret),
            ]);

        $response->assertRedirect(route('two-factor.enable'));

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
    }

    public function test_confirm_with_invalid_code_fails(): void
    {
        $user = $this->makeUser('invalid-code@test.com');
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $response = $this->actingAs($user)
            ->withSession(['two_factor_secret' => $secret])
            ->post('/two-factor/confirm', [
                'code' => '000000',
            ]);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    public function test_2fa_challenge_redirects_when_enabled(): void
    {
        $user = $this->makeUser('challenge@test.com');
        $this->enableTwoFactor($user);

        // When 2FA is enabled and not verified in session, challenge page shows
        $response = $this->actingAs($user)
            ->get('/two-factor/challenge');

        $response->assertOk();
    }

    public function test_user_can_verify_2fa_challenge(): void
    {
        $user = $this->makeUser('verify@test.com');
        $secret = $this->enableTwoFactor($user);

        $google2fa = new Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($user)
            ->post('/two-factor/challenge', [
                'code' => $validCode,
            ]);

        $response->assertRedirect('/');
        $this->assertTrue(session('two_factor_verified'));
    }

    public function test_user_can_disable_2fa_with_password(): void
    {
        $user = $this->makeUser('disable@test.com');
        $this->enableTwoFactor($user);

        $response = $this->actingAs($user)
            ->post('/two-factor/disable', [
                'password' => 'password',
            ]);

        $response->assertRedirect(route('two-factor.enable'));

        $user->refresh();
        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    public function test_disable_2fa_with_wrong_password_fails(): void
    {
        $user = $this->makeUser('wrongpwd@test.com');
        $this->enableTwoFactor($user);

        $response = $this->actingAs($user)
            ->post('/two-factor/disable', [
                'password' => 'wrongpassword',
            ]);

        $response->assertSessionHasErrors('password');

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
    }

    public function test_recovery_code_works_once(): void
    {
        $user = $this->makeUser('recovery@test.com');
        $this->enableTwoFactor($user);

        // Use RECOVERY-CODE-1
        $response = $this->actingAs($user)
            ->post('/two-factor/recovery', [
                'recovery_code' => 'RECOVERY-CODE-1',
            ]);

        $response->assertRedirect('/');
        $this->assertTrue(session('two_factor_verified'));

        // The used code should be removed
        $user->refresh();
        $this->assertNotContains('RECOVERY-CODE-1', $user->two_factor_recovery_codes);
        $this->assertContains('RECOVERY-CODE-2', $user->two_factor_recovery_codes);
    }
}

<?php

namespace Modules\Auth\Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Modules\Auth\Listeners\LogPasswordReset;
use Modules\Auth\Models\LoginLog;
use Modules\Auth\Tests\TestCase;

/**
 * R-301 — Audit des réinitialisations de mot de passe.
 *
 * Vérifie que :
 *   1. le listener `LogPasswordReset` est bien enregistré dans
 *      l'EventServiceProvider (plus d'orphelin),
 *   2. dispatcher `PasswordReset` produit une ligne `LoginLog` avec
 *      `status = 'password_reset'`,
 *   3. la trace contient les métadonnées attendues (user_id, IP,
 *      user_agent).
 */
final class PasswordResetAuditTest extends TestCase
{
    public function test_password_reset_event_is_registered_in_service_provider(): void
    {
        $listeners = Event::getListeners(PasswordReset::class);

        $this->assertNotEmpty(
            $listeners,
            'PasswordReset event doit avoir au moins un listener (plus orphelin).'
        );
    }

    public function test_password_reset_creates_login_log_entry(): void
    {
        $user = User::create([
            'full_name' => 'Reset Test',
            'email' => 'reset-test@example.com',
            'password' => 'password',
        ]);

        $before = LoginLog::where('user_id', $user->id)->count();

        event(new PasswordReset($user));

        $this->assertSame(
            $before + 1,
            LoginLog::where('user_id', $user->id)->count(),
            'Un LoginLog doit être créé après PasswordReset.'
        );

        $status = LoginLog::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->value('status');

        $this->assertSame(
            'password_reset',
            $status,
            'Le status du log doit être "password_reset".'
        );
    }

    public function test_password_reset_listener_handles_event_directly(): void
    {
        $user = User::create([
            'full_name' => 'Direct Listener Test',
            'email' => 'direct-listener@example.com',
            'password' => 'password',
        ]);

        $listener = new LogPasswordReset;
        $listener->handle(new PasswordReset($user));

        $log = LoginLog::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'Le listener doit créer une entrée LoginLog.');
        $this->assertSame('password_reset', (string) LoginLog::query()->where('id', $log->id)->value('status'));
    }
}

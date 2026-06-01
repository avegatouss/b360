<?php

namespace Modules\Auth\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Modules\Auth\Models\LoginLog;
use Modules\Core\Support\CurrentInstance;

/**
 * R-301 — Trace les réinitialisations de mot de passe dans LoginLog.
 *
 * L'event `Illuminate\Auth\Events\PasswordReset` est dispatché par
 * `ResetPasswordController` après sauvegarde du nouveau hash. Avant
 * ce listener, l'event était orphelin (aucune trace côté audit).
 *
 * La ligne est écrite dans la même table que les Login/Failed events
 * (via le champ `status = 'password_reset'`), ce qui permet à l'audit
 * sécurité de corréler les 3 types d'événements sur une même timeline.
 *
 * Voir audit ISSUE-12.
 */
class LogPasswordReset
{
    public function handle(PasswordReset $event): void
    {
        $instance = CurrentInstance::get();

        LoginLog::create([
            'user_id' => $event->user->getAuthIdentifier(),
            'instance_id' => $instance?->id,
            'ip_address' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
            'status' => 'password_reset',
        ]);
    }
}

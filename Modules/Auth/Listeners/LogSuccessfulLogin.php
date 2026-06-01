<?php

namespace Modules\Auth\Listeners;

use Illuminate\Auth\Events\Login;
use Modules\Auth\Models\LoginLog;
use Modules\Core\Support\CurrentInstance;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $instance = CurrentInstance::get();

        LoginLog::create([
            'user_id'     => $event->user->id,
            'instance_id' => $instance?->id,
            'ip_address'  => request()->ip(),
            'user_agent'  => mb_substr((string) request()->userAgent(), 0, 500),
            'status'      => 'success',
        ]);
    }
}

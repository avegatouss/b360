<?php

namespace Modules\Auth\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Modules\Auth\Models\LoginLog;
use Modules\Core\Support\CurrentInstance;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        $instance = CurrentInstance::get();

        // Attempt to find the user by email from credentials
        $userId = null;
        if ($event->user) {
            $userId = $event->user->id;
        } elseif (!empty($event->credentials['email'])) {
            $user = User::on('system')
                ->where('email', $event->credentials['email'])
                ->first();
            $userId = $user?->id;
        }

        if ($userId) {
            LoginLog::create([
                'user_id'     => $userId,
                'instance_id' => $instance?->id,
                'ip_address'  => request()->ip(),
                'user_agent'  => mb_substr((string) request()->userAgent(), 0, 500),
                'status'      => 'failed',
            ]);
        }
    }
}

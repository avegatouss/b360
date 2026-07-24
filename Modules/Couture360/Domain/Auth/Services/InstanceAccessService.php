<?php

declare(strict_types=1);

namespace Modules\Couture360\Domain\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\TeamContext;

/**
 * Shared instance-access check for the Couture360 API pipeline
 * (AuthController::login + CoutureApiAuth middleware).
 *
 * A user can access an instance if either:
 *   - they are an active member (system.instance_user pivot), or
 *   - they hold the B360 global super-admin role, stored under the Spatie
 *     team-0 "cross-instance" sentinel (see TeamContext::isSuperAdmin(),
 *     which brackets the team context to 0 before checking hasRole() and
 *     always restores the previous context — mirroring
 *     CoreAuthServiceProvider's Gate::before bypass).
 */
final class InstanceAccessService
{
    public function canAccess(User $user, int $instanceId): bool
    {
        $isMember = DB::connection('system')->table('instance_user')
            ->where('user_id', $user->id)
            ->where('instance_id', $instanceId)
            ->where('status', 'active')
            ->exists();

        return $isMember || TeamContext::isSuperAdmin($user);
    }
}

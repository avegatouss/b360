<?php

namespace Modules\Users\Services;

use App\Models\User;
use Modules\Core\Support\TeamContext;

final class TeamRoleAssigner
{
    /**
     * @param array<int, string> $roles
     */
    public function syncRolesForInstance(User $user, int $instanceId, array $roles): void
    {
        TeamContext::set($instanceId);

        // Hard whitelist minimal (instance-level roles)
        $roles = array_values(array_unique(array_filter($roles, fn($r) => in_array($r, [
            'instance-admin', 'manager', 'agent', 'user'
        ], true))));

        if (empty($roles)) {
            $roles = ['user'];
        }

        $user->syncRoles($roles);
    }
}

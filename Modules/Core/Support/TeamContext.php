<?php

namespace Modules\Core\Support;

use Spatie\Permission\PermissionRegistrar;

final class TeamContext
{
    /**
     * Set Spatie team context (team_id = instance_id).
     * MUST be called before assigning roles/permissions to ensure pivot writes team_id correctly.
     */
    public static function set(?int $instanceId): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($instanceId);
    }

    public static function clear(): void
    {
        self::set(null);
    }
}

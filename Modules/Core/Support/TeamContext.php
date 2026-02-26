<?php

namespace Modules\Core\Support;

use Spatie\Permission\PermissionRegistrar;

final class TeamContext
{
    /**
     * Sentinelle globale : instance_id = 0 = rôle cross-instance (super-admin).
     * NULL est INTERDIT dans les colonnes PK MySQL.
     */
    public const GLOBAL_TEAM_ID = 0;

    /**
     * Set Spatie team context (team_id = instance_id).
     * MUST be called before assigning roles/permissions to ensure pivot writes team_id correctly.
     */
    public static function set(?int $instanceId): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($instanceId);
        $registrar->forgetCachedPermissions();
    }

    /**
     * Réinitialise au contexte global (instance_id = 0).
     * Ne met JAMAIS null car cela viole la contrainte PK de model_has_roles.
     */
    public static function clear(): void
    {
        self::set(self::GLOBAL_TEAM_ID);
    }

    /**
     * Retourne le team_id courant.
     */
    public static function current(): ?int
    {
        return app(PermissionRegistrar::class)->getPermissionsTeamId();
    }
}

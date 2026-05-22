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
     * Cache en mémoire pour la durée de la requête (PHP-FPM : meurt naturellement).
     */
    private static array $superAdminCache = [];

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

    /**
     * Vérifie si l'utilisateur a le rôle super-admin (stocké avec instance_id = 0).
     * Bascule temporairement le team_id à 0 puis restaure l'ancien contexte.
     * Le résultat est mis en cache pour la durée de la requête.
     */
    public static function isSuperAdmin($user): bool
    {
        if (!$user || !method_exists($user, 'hasRole')) {
            return false;
        }

        $userId = $user->getKey();

        if (array_key_exists($userId, self::$superAdminCache)) {
            return self::$superAdminCache[$userId];
        }

        $registrar = app(PermissionRegistrar::class);
        $previous  = $registrar->getPermissionsTeamId();

        try {
            $registrar->setPermissionsTeamId(self::GLOBAL_TEAM_ID);
            $user->unsetRelation('roles');
            $result = $user->hasRole('super-admin');
        } finally {
            $registrar->setPermissionsTeamId($previous);
            $user->unsetRelation('roles');
        }

        return self::$superAdminCache[$userId] = $result;
    }
}

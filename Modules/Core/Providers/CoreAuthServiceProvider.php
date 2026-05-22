<?php

namespace Modules\Core\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

final class CoreAuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /*
        |----------------------------------------------------------------------
        | Bypass global super-admin
        |----------------------------------------------------------------------
        |
        | Convention B360 : le rôle "super-admin" est stocké avec instance_id = 0
        | (sentinelle "global" — la PRIMARY KEY MySQL interdit NULL).
        |
        | On doit temporairement forcer team_id = 0 avant de vérifier le rôle,
        | car le middleware SetSpatieTeamContextFromInstance peut avoir défini
        | un team_id différent (celui de l'instance courante).
        |
        */
        Gate::before(function ($user, $ability) {
            if (!$user || !method_exists($user, 'hasRole')) {
                return null;
            }

            $registrar = app(PermissionRegistrar::class);

            // Sauvegarder le contexte courant
            $previousTeamId = $registrar->getPermissionsTeamId();

            try {
                // Vérifier avec le contexte global (instance_id = 0)
                $registrar->setPermissionsTeamId(0);
                $isSuperAdmin = $user->hasRole('super-admin');
            } finally {
                // Toujours restaurer — même en cas d'exception
                $registrar->setPermissionsTeamId($previousTeamId);
                $user->unsetRelation('roles')->unsetRelation('permissions');
            }

            return $isSuperAdmin ? true : null;
        });
    }
}

<?php

namespace Modules\Eshop360\Database\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Support\CurrentChannel;

/**
 * Scope d'isolation par channel (automatique — canonique Eshop360).
 *
 * Priorité de résolution :
 * 1. Console (hors tests) → pas de filtre
 * 2. Pas d'utilisateur authentifié → fail-closed (0 résultats)
 * 3. Channel explicite via CurrentChannel (middleware/session) → filtre sur ce channel
 * 4. Hub admin sans channel → voit tout
 * 5. Utilisateur avec channels accessibles → filtre sur ceux-ci
 * 6. Utilisateur sans channel accessible → fail-closed
 *
 * Utilisé via le trait BelongsToChannel.
 */
final class ChannelScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Console context (hors tests) : pas de filtrage
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        $user = auth()->user();

        // Pas d'utilisateur authentifié : fail-closed
        if (! $user) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $table = $model->getTable();
        $channelAccess = app(ChannelAccessService::class);
        $isHubAdmin = $channelAccess->isHubAdmin($user);

        // Priorité : si un channel est explicitement défini (via middleware ResolveChannel
        // ou navigation dans le menu), filtrer dessus pour TOUS les utilisateurs
        if (CurrentChannel::isScoped()) {
            $currentId = CurrentChannel::id();

            // Hub admin : toujours autorisé dans n'importe quel channel
            if ($isHubAdmin) {
                $builder->where("{$table}.channel_id", $currentId);
                return;
            }

            // Utilisateur non-admin : vérifier l'accès au channel
            $accessibleIds = $channelAccess->accessibleChannelIds($user);
            if ($accessibleIds && $accessibleIds->contains($currentId)) {
                $builder->where("{$table}.channel_id", $currentId);
                return;
            }

            // Channel explicite mais pas de membership ChannelUser :
            // Autoriser si l'utilisateur a un Customer lié à ce channel
            // (portail client canal sans ChannelUser)
            $builder->where("{$table}.channel_id", $currentId);
            return;
        }

        // Pas de channel explicite : logique par rôle
        if ($isHubAdmin) {
            // Hub admin sans channel sélectionné : voit tout
            return;
        }

        // Utilisateur non-admin sans channel explicite : restreindre aux channels autorisés
        $accessibleIds = $channelAccess->accessibleChannelIds($user);

        if (! $accessibleIds || $accessibleIds->isEmpty()) {
            Log::warning('ChannelScope: utilisateur sans channel accessible, résultats bloqués', [
                'user_id' => $user->id,
                'model' => get_class($model),
            ]);
            $builder->whereRaw('1 = 0');
            return;
        }

        $builder->whereIn("{$table}.channel_id", $accessibleIds->all());
    }
}

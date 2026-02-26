<?php

namespace Modules\Core\Database\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Log;
use Modules\Core\Support\CurrentInstance;

/**
 * Scope d'isolation par instance (unique — canonique B360).
 *
 * - En mode single : pas de filtrage (une seule instance)
 * - En mode multi + database-per-instance : pas de filtrage logique
 * - En mode multi + shared : filtre sur instance_id
 * - Si aucune instance résolue en mode shared : fail-closed (0 résultats)
 *
 * Utilisé via le trait BelongsToInstance (app/Models/Concerns/ ou Modules/Core/Database/Traits/).
 */
final class InstanceScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $mode = config('app.instance_mode', 'single');
        $strategy = config('app.instance_db_strategy', 'shared');

        // Mode single ou database-per-instance → pas de filtrage logique
        if ($mode === 'single' || $strategy !== 'shared') {
            return;
        }

        $instance = CurrentInstance::get();

        if (!$instance) {
            // Fail-closed : bloquer toutes les requêtes sans contexte d'instance
            Log::warning('InstanceScope: aucune instance résolue, résultats bloqués', [
                'model' => get_class($model),
            ]);
            $builder->whereRaw('1 = 0');
            return;
        }

        $builder->where($model->getTable() . '.instance_id', $instance->id);
    }
}

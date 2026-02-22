<?php

namespace App\Models\Concerns;

use App\Instances\Instance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Modules\Core\Support\CurrentInstance;

/**
 * Trait BelongsToInstance
 *
 * Ajoute un GlobalScope automatique filtrant sur instance_id.
 * À utiliser sur tous les modèles métiers en mode "shared".
 *
 * Usage :
 *   class Product extends Model {
 *       use BelongsToInstance;
 *   }
 *
 * Requêtes cross-instance (admin) :
 *   Product::withoutInstanceScope()->get();
 */
trait BelongsToInstance
{
    public static function bootBelongsToInstance(): void
    {
        static::addGlobalScope(new InstanceScope());

        // Auto-injecter instance_id à la création
        static::creating(function (Model $model) {
            if (empty($model->instance_id)) {
                $instance = CurrentInstance::get();
                if ($instance) {
                    $model->instance_id = $instance->id;
                }
            }
        });
    }

    /**
     * Requête sans filtre d'instance (cross-instance, admin uniquement).
     */
    public static function withoutInstanceScope(): Builder
    {
        return static::withoutGlobalScope(InstanceScope::class);
    }

    /**
     * Relation vers l'instance propriétaire.
     */
    public function instance()
    {
        return $this->belongsTo(Instance::class);
    }
}

/**
 * Scope d'isolation par instance.
 *
 * - En mode single : pas de filtrage (une seule instance)
 * - En mode multi shared : filtre sur instance_id
 * - Si aucune instance résolue : ne filtre pas (sécurité conservative)
 */
class InstanceScope implements Scope
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
            // Fail-open conservative : ne bloque pas mais ne filtre pas non plus.
            // Le middleware EnsureInstanceResolved doit rejeter avant d'arriver ici.
            return;
        }

        $builder->where($model->getTable() . '.instance_id', $instance->id);
    }
}

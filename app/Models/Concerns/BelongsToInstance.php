<?php

namespace App\Models\Concerns;

use App\Instances\Instance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Scopes\InstanceScope;
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
                if (!$instance) {
                    throw new \RuntimeException(
                        'Impossible de créer un model ' . get_class($model) . ' sans contexte d\'instance. '
                        . 'Vérifiez que le middleware core.instance.bind est appliqué.'
                    );
                }
                $model->instance_id = $instance->id;
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

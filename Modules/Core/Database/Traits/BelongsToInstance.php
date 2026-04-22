<?php

namespace Modules\Core\Database\Traits;

use App\Instances\Instance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Core\Support\CurrentInstance;

/**
 * Trait BelongsToInstance — isolation multi-tenant canonique.
 *
 * Applique un GlobalScope filtrant par instance_id et injecte automatiquement
 * instance_id à la création via CurrentInstance.
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
        static::addGlobalScope(new InstanceScope);

        static::creating(function (Model $model) {
            if (empty($model->instance_id)) {
                $instance = CurrentInstance::get();
                if (! $instance) {
                    throw new \RuntimeException(
                        'Impossible de créer un model '.get_class($model).' sans contexte d\'instance. '
                        .'Vérifiez que le middleware core.instance.bind est appliqué.'
                    );
                }
                $model->instance_id = $instance->id;
            }
        });
    }

    public static function withoutInstanceScope(): Builder
    {
        return static::withoutGlobalScope(InstanceScope::class);
    }

    public function instance()
    {
        return $this->belongsTo(Instance::class);
    }
}

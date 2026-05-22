<?php

namespace Modules\Core\Database\Traits;

// Ce trait est un alias vers App\Models\Concerns\BelongsToInstance.
// Conservé pour compatibilité avec les tests existants du module Core.
// Les deux implémentations sont identiques et utilisent le même
// Modules\Core\Database\Scopes\InstanceScope canonique.

use App\Instances\Instance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Core\Support\CurrentInstance;

/**
 * Trait BelongsToInstance (module Core).
 *
 * Identique à App\Models\Concerns\BelongsToInstance.
 * Préférer le trait App pour les nouveaux modèles.
 */
trait BelongsToInstance
{
    public static function bootBelongsToInstance(): void
    {
        static::addGlobalScope(new InstanceScope());

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

    public static function withoutInstanceScope(): Builder
    {
        return static::withoutGlobalScope(InstanceScope::class);
    }

    public function instance()
    {
        return $this->belongsTo(Instance::class);
    }
}

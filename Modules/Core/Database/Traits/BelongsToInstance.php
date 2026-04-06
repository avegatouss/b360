<?php

namespace Modules\Core\Database\Traits;

// Canonical BelongsToInstance trait.
// All models should import this namespace.
// App\Models\Concerns\BelongsToInstance is a thin re-export kept
// for backward compatibility only.

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

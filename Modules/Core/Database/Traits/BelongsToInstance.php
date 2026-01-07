<?php

namespace Modules\Core\Database\Traits;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Core\Support\CurrentInstance;

trait BelongsToInstance
{
    public static function bootBelongsToInstance(): void
    {
        static::addGlobalScope(new InstanceScope());

        static::creating(function ($model) {
            if (!isset($model->instance_id)) {
                $instance = CurrentInstance::get();
                if ($instance) {
                    $model->instance_id = $instance->id;
                }
            }
        });
    }

    public function scopeWithoutInstance($query)
    {
        return $query->withoutGlobalScope(InstanceScope::class);
    }
}

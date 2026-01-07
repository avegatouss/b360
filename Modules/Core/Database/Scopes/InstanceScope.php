<?php
// Modules/Core/Database/Scopes/InstanceScope.php

namespace Modules\Core\Database\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class InstanceScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $instance = app('currentInstance'); // ou helper currentInstance()
        if (!$instance) {
            // Safe default: aucune instance => bloquer (ou root-only selon ta convention)
            $builder->whereRaw('1 = 0');
            return;
        }

        // Shared mode: filtrer par instance_id
        $builder->where($model->getTable().'.instance_id', $instance->id);
    }
}

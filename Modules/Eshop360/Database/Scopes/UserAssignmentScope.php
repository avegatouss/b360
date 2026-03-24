<?php

namespace Modules\Eshop360\Database\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Modules\Eshop360\Services\UserResourceScopeService;

/**
 * Generic scope that filters a model by user resource assignments.
 * Supports store, warehouse, and customer resource types.
 */
class UserAssignmentScope implements Scope
{
    public function __construct(
        private string $resourceType,
        private string $column,
    ) {}

    public function apply(Builder $builder, Model $model): void
    {
        $service = app(UserResourceScopeService::class);

        $ids = match ($this->resourceType) {
            'store' => $service->storeIds(),
            'warehouse' => $service->warehouseIds(),
            'customer' => $service->customerIds(),
            default => null,
        };

        if ($ids === null) {
            return; // Admin = full access
        }

        // Empty collection = non-admin with no assignments → whereIn([]) returns 0 rows

        $builder->whereIn($model->getTable() . '.' . $this->column, $ids);
    }
}

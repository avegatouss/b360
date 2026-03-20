<?php

namespace Modules\Eshop360\Database\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Modules\Eshop360\Services\UserResourceScopeService;

/**
 * Compound scope for Order model.
 * An order is visible if ANY of the user's assigned resources match:
 * store_id OR warehouse_id OR customer_id.
 */
class OrderUserAssignmentScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $service = app(UserResourceScopeService::class);

        if ($service->isAdmin()) {
            return;
        }

        $storeIds = $service->storeIds();
        $warehouseIds = $service->warehouseIds();
        $customerIds = $service->customerIds();

        // No assignments at all = full access
        if ($storeIds === null && $warehouseIds === null && $customerIds === null) {
            return;
        }

        $table = $model->getTable();

        $builder->where(function (Builder $q) use ($table, $storeIds, $warehouseIds, $customerIds) {
            if ($storeIds !== null) {
                $q->orWhereIn("{$table}.store_id", $storeIds);
            }
            if ($warehouseIds !== null) {
                $q->orWhereIn("{$table}.warehouse_id", $warehouseIds);
            }
            if ($customerIds !== null) {
                $q->orWhereIn("{$table}.customer_id", $customerIds);
            }
        });
    }
}

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

        // null = admin (full access)
        if ($storeIds === null && $warehouseIds === null && $customerIds === null) {
            return;
        }

        // Collect all non-empty assignment sets for OR filtering
        $hasAny = ($storeIds && $storeIds->isNotEmpty())
            || ($warehouseIds && $warehouseIds->isNotEmpty())
            || ($customerIds && $customerIds->isNotEmpty());

        if (!$hasAny) {
            // Non-admin with zero assignments → sees nothing
            $builder->whereRaw('1 = 0');
            return;
        }

        $table = $model->getTable();

        $builder->where(function (Builder $q) use ($table, $storeIds, $warehouseIds, $customerIds) {
            if ($storeIds && $storeIds->isNotEmpty()) {
                $q->orWhereIn("{$table}.store_id", $storeIds);
            }
            if ($warehouseIds && $warehouseIds->isNotEmpty()) {
                $q->orWhereIn("{$table}.warehouse_id", $warehouseIds);
            }
            if ($customerIds && $customerIds->isNotEmpty()) {
                $q->orWhereIn("{$table}.customer_id", $customerIds);
            }
        });
    }
}

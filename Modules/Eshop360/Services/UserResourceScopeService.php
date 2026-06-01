<?php

namespace Modules\Eshop360\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\UserAssignment;

class UserResourceScopeService
{
    private ?User $user = null;

    private bool $resolved = false;

    private ?Collection $storeAssignments = null;

    private ?Collection $warehouseAssignments = null;

    private ?Collection $customerAssignments = null;

    /**
     * Initialize for the current authenticated user.
     */
    public function init(?User $user = null): void
    {
        $this->user = $user ?? auth()->user();
        $this->resolved = false;
        $this->storeAssignments = null;
        $this->warehouseAssignments = null;
        $this->customerAssignments = null;
    }

    /**
     * Check if user is an admin (sees everything).
     */
    public function isAdmin(): bool
    {
        if (! $this->user) {
            return true; // No auth context (console, queues) = full access
        }

        return $this->user->hasRole('super-admin') || $this->user->hasRole('instance-admin');
    }

    /**
     * Get assigned store IDs.
     * Returns null for admins (full access).
     * Returns empty collection if no assignments (no access).
     */
    public function storeIds(): ?Collection
    {
        if ($this->isAdmin()) {
            return null;
        }

        $this->resolveAssignments();

        return $this->storeAssignments;
    }

    /**
     * Get assigned warehouse IDs.
     * Returns null for admins (full access).
     * Returns empty collection if no assignments (no access).
     */
    public function warehouseIds(): ?Collection
    {
        if ($this->isAdmin()) {
            return null;
        }

        $this->resolveAssignments();

        return $this->warehouseAssignments;
    }

    /**
     * Get assigned customer IDs.
     * Returns null for admins (full access).
     * Returns empty collection if no assignments (no access).
     */
    public function customerIds(): ?Collection
    {
        if ($this->isAdmin()) {
            return null;
        }

        $this->resolveAssignments();

        return $this->customerAssignments;
    }

    public function hasStoreAssignments(): bool
    {
        return $this->storeIds() !== null;
    }

    public function hasWarehouseAssignments(): bool
    {
        return $this->warehouseIds() !== null;
    }

    public function hasCustomerAssignments(): bool
    {
        return $this->customerIds() !== null;
    }

    /**
     * Apply store scope to a query builder.
     */
    public function applyStoreScope(Builder $query, string $column = 'store_id'): Builder
    {
        $ids = $this->storeIds();

        if ($ids === null) {
            return $query;
        }

        return $query->whereIn($column, $ids);
    }

    /**
     * Apply warehouse scope to a query builder.
     */
    public function applyWarehouseScope(Builder $query, string $column = 'warehouse_id'): Builder
    {
        $ids = $this->warehouseIds();

        if ($ids === null) {
            return $query;
        }

        return $query->whereIn($column, $ids);
    }

    /**
     * Apply customer scope to a query builder.
     */
    public function applyCustomerScope(Builder $query, string $column = 'customer_id'): Builder
    {
        $ids = $this->customerIds();

        if ($ids === null) {
            return $query;
        }

        return $query->whereIn($column, $ids);
    }

    /**
     * Check if user can see Saphir pricing information.
     */
    public function canSeePricing(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->user) {
            return false;
        }

        return $this->user->can('eshop.products.factory_price')
            || $this->user->can('eshop.products.pght')
            || $this->user->can('eshop.products.cost_real');
    }

    /**
     * Load all assignments from DB once per request.
     */
    private function resolveAssignments(): void
    {
        if ($this->resolved) {
            return;
        }

        $this->resolved = true;
        $this->storeAssignments = collect();
        $this->warehouseAssignments = collect();
        $this->customerAssignments = collect();

        if (! $this->user) {
            return;
        }

        try {
            $instanceId = CurrentInstance::get()?->id;

            $assignments = UserAssignment::forUser($this->user->id)
                ->when($instanceId, fn ($q) => $q->forInstance($instanceId))
                ->get(['resource_type', 'resource_id']);

            foreach ($assignments as $assignment) {
                match ($assignment->resource_type) {
                    'store' => $this->storeAssignments->push($assignment->resource_id),
                    'warehouse' => $this->warehouseAssignments->push($assignment->resource_id),
                    'customer' => $this->customerAssignments->push($assignment->resource_id),
                };
            }
        } catch (\Throwable) {
            // Table may not exist yet (before migration). Silently ignore.
        }
    }
}

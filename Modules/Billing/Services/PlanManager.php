<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Billing\Models\Plan;

final class PlanManager
{
    public function all(bool $activeOnly = true): Collection
    {
        $query = Plan::query()->orderBy('sort_order')->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    public function forInstance(int $instanceId): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->visibleTo($instanceId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): ?Plan
    {
        return Plan::find($id);
    }

    public function findBySlug(string $slug): ?Plan
    {
        return Plan::where('slug', $slug)->first();
    }

    public function create(array $data): Plan
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return Plan::create($data);
    }

    public function update(Plan $plan, array $data): Plan
    {
        $plan->update($data);

        return $plan->refresh();
    }

    public function delete(Plan $plan): void
    {
        if ($plan->subscriptions()->where('status', '!=', 'expired')->exists()) {
            $plan->update(['is_active' => false]);
            return;
        }

        $plan->delete();
    }
}

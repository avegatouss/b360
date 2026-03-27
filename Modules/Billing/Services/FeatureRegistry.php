<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Collection;
use Modules\Billing\Models\Plan;
use Modules\Core\Hooks\DTO\BillableFeature;
use Modules\Core\Hooks\Registry\HookRegistry;

/**
 * Centralized feature registry — determines which features are available
 * for a given instance based on its subscription plan.
 *
 * Modules register features via HookRegistry::addFeature() in their hooks provider.
 * Plans store included feature IDs in the `features` JSON column (or '*' for all).
 */
final class FeatureRegistry
{
    private array $cache = [];

    public function __construct(
        private readonly HookRegistry $hookRegistry,
        private readonly SubscriptionManager $subscriptionManager,
    ) {}

    /**
     * Check if a feature is available for the given instance.
     */
    public function has(string $featureId, int $instanceId): bool
    {
        return in_array($featureId, $this->available($instanceId), true);
    }

    /**
     * Get all available features for an instance (free + plan-included).
     */
    public function available(int $instanceId): array
    {
        if (isset($this->cache[$instanceId])) {
            return $this->cache[$instanceId];
        }

        $allFeatures = $this->hookRegistry->features();

        // Free features are always available
        $free = $allFeatures
            ->where('tier', 'free')
            ->pluck('id')
            ->all();

        $sub = $this->subscriptionManager->current($instanceId);

        if (!$sub || !$sub->isActive()) {
            return $this->cache[$instanceId] = $free;
        }

        $plan = $sub->plan;
        if (!$plan) {
            return $this->cache[$instanceId] = $free;
        }

        $planFeatures = $plan->features ?? [];

        // Wildcard: all features included
        if (in_array('*', $planFeatures, true)) {
            return $this->cache[$instanceId] = $allFeatures->pluck('id')->all();
        }

        // Merge free + plan features (only those actually registered)
        $registeredIds = $allFeatures->pluck('id')->all();
        $included = array_intersect($planFeatures, $registeredIds);

        return $this->cache[$instanceId] = array_values(array_unique(array_merge($free, $included)));
    }

    /**
     * Get all registered features (from all modules).
     */
    public function all(): Collection
    {
        return $this->hookRegistry->features();
    }

    /**
     * Get features grouped by tier.
     */
    public function grouped(): array
    {
        $features = $this->hookRegistry->features();

        return [
            'free' => $features->where('tier', 'free')->values(),
            'paid' => $features->where('tier', 'paid')->values(),
        ];
    }

    /**
     * Get features grouped by category.
     */
    public function byCategory(): Collection
    {
        return $this->hookRegistry->features()->groupBy('category');
    }

    /**
     * Get paid features missing from the current plan.
     */
    public function missing(int $instanceId): Collection
    {
        $available = $this->available($instanceId);

        return $this->hookRegistry->features()
            ->where('tier', 'paid')
            ->reject(fn (BillableFeature $f) => in_array($f->id, $available, true))
            ->values();
    }

    /**
     * Check if instance has a paid plan (any active subscription).
     */
    public function isPaid(int $instanceId): bool
    {
        return $this->subscriptionManager->isActive($instanceId);
    }

    /**
     * Get the cheapest plan that includes a specific feature.
     */
    public function cheapestPlanFor(string $featureId, int $instanceId): ?Plan
    {
        return $this->plansIncluding($featureId, $instanceId)
            ->sortBy('price_monthly')
            ->first();
    }

    /**
     * Get all plans that include a specific feature.
     */
    public function plansIncluding(string $featureId, int $instanceId): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->visibleTo($instanceId)
            ->get()
            ->filter(function (Plan $plan) use ($featureId) {
                $features = $plan->features ?? [];

                return in_array('*', $features, true)
                    || in_array($featureId, $features, true);
            })
            ->values();
    }

    /**
     * Clear the resolved features cache (useful after plan change).
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}

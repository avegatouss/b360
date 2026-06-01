<?php

declare(strict_types=1);

namespace Modules\Billing\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Unified feature resolution with tenant-level overrides.
 *
 * Resolution order (highest priority first):
 * 1. Tenant override (tenant_feature_overrides table)
 * 2. Plan features (via FeatureRegistry + subscription)
 * 3. Default: false
 *
 * This replaces direct FeatureRegistry::has() calls when tenant-level
 * granularity is needed.
 */
final class FeatureResolver
{
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private readonly FeatureRegistry $registry,
    ) {}

    /**
     * Check if a feature is available for the given instance.
     */
    public function can(int $instanceId, string $featureSlug): bool
    {
        return (bool) Cache::remember(
            "feature_resolver.{$instanceId}.{$featureSlug}",
            self::CACHE_TTL,
            function () use ($instanceId, $featureSlug): bool {
                // 1. Check tenant override (highest priority)
                $override = $this->getTenantOverride($instanceId, $featureSlug);
                if ($override !== null) {
                    return $override;
                }

                // 2. Check plan-based features via FeatureRegistry
                return $this->registry->has($featureSlug, $instanceId);
            }
        );
    }

    /**
     * Get override configuration value for a tenant feature.
     *
     * Returns the JSON `value` column if the feature has a tenant override,
     * useful for features that carry configuration (e.g., max_credit_amount).
     */
    public function config(int $instanceId, string $featureSlug): mixed
    {
        $row = DB::connection('system')
            ->table('tenant_feature_overrides')
            ->where('instance_id', $instanceId)
            ->where('feature', $featureSlug)
            ->where('enabled', true)
            ->first();

        if (! $row) {
            return null;
        }

        return json_decode($row->value, true);
    }

    /**
     * Set a tenant-level feature override.
     */
    public function setOverride(int $instanceId, string $featureSlug, bool $enabled, ?array $value = null, ?int $userId = null): void
    {
        DB::connection('system')
            ->table('tenant_feature_overrides')
            ->updateOrInsert(
                ['instance_id' => $instanceId, 'feature' => $featureSlug],
                [
                    'enabled' => $enabled,
                    'value' => $value ? json_encode($value) : null,
                    'activated_by' => $userId,
                    'activated_at' => now(),
                    'updated_at' => now(),
                ]
            );

        $this->clearCache($instanceId, $featureSlug);
    }

    /**
     * Remove a tenant-level feature override (fall back to plan).
     */
    public function removeOverride(int $instanceId, string $featureSlug): void
    {
        DB::connection('system')
            ->table('tenant_feature_overrides')
            ->where('instance_id', $instanceId)
            ->where('feature', $featureSlug)
            ->delete();

        $this->clearCache($instanceId, $featureSlug);
    }

    /**
     * Get all overrides for an instance.
     */
    public function overridesFor(int $instanceId): array
    {
        return DB::connection('system')
            ->table('tenant_feature_overrides')
            ->where('instance_id', $instanceId)
            ->get()
            ->keyBy('feature')
            ->toArray();
    }

    /**
     * Clear cached resolution for a specific feature or all features of an instance.
     */
    public function clearCache(int $instanceId, ?string $featureSlug = null): void
    {
        if ($featureSlug) {
            Cache::forget("feature_resolver.{$instanceId}.{$featureSlug}");
        } else {
            // Clear all features for this instance — pattern-based clear
            // In practice, rely on TTL expiry for bulk invalidation
            $this->registry->clearCache();
        }
    }

    private function getTenantOverride(int $instanceId, string $featureSlug): ?bool
    {
        try {
            $row = DB::connection('system')
                ->table('tenant_feature_overrides')
                ->where('instance_id', $instanceId)
                ->where('feature', $featureSlug)
                ->first();

            if ($row === null) {
                return null;
            }

            return (bool) $row->enabled;
        } catch (\Throwable) {
            // Table may not exist yet (pre-migration) — fall through
            return null;
        }
    }
}

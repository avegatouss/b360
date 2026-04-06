<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Registry;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Pricing\Contracts\PricingRuleInterface;

/**
 * Holds all registered pricing rules and resolves which ones are active
 * for a given instance and pipeline type.
 */
class PricingRuleRegistry
{
    private const CACHE_KEY = 'eshop:pricing_rules:active';
    private const CACHE_TTL = 600; // 10 minutes

    /**
     * In-memory rule map keyed by slug.
     *
     * @var array<string, PricingRuleInterface>
     */
    private array $rules = [];

    /**
     * Register a rule instance. Later registrations with the same slug overwrite earlier ones.
     */
    public function register(PricingRuleInterface $rule): void
    {
        $this->rules[$rule->slug()] = $rule;
    }

    /**
     * Get all registered rules (unfiltered).
     *
     * @return PricingRuleInterface[]
     */
    public function all(): array
    {
        return array_values($this->rules);
    }

    /**
     * Get active rules for the retail pipeline, sorted by priority.
     *
     * @return PricingRuleInterface[]
     */
    public function getRulesForRetail(int $instanceId): array
    {
        return $this->filterByPipeline($instanceId, 'retail');
    }

    /**
     * Get active rules for the channel pipeline, sorted by priority.
     *
     * @return PricingRuleInterface[]
     */
    public function getRulesForChannel(int $instanceId): array
    {
        return $this->filterByPipeline($instanceId, 'channel');
    }

    /**
     * Get active rules for a given pipeline type, cross-referencing the DB table.
     *
     * @return PricingRuleInterface[]
     */
    private function filterByPipeline(int $instanceId, string $pipeline): array
    {
        $activeSlugs = $this->getActiveSlugs($instanceId, $pipeline);

        $filtered = array_filter(
            $this->rules,
            fn (PricingRuleInterface $rule) => in_array($rule->slug(), $activeSlugs, true),
        );

        // Sort by priority ascending (lower = runs first)
        usort($filtered, fn (PricingRuleInterface $a, PricingRuleInterface $b) => $a->priority() <=> $b->priority());

        return $filtered;
    }

    /**
     * Query the eshop_pricing_rules + per-instance config tables to determine
     * which rule slugs are active for this instance and pipeline.
     *
     * @return string[]
     */
    private function getActiveSlugs(int $instanceId, string $pipeline): array
    {
        $cacheKey = self::CACHE_KEY . ':' . $instanceId . ':' . $pipeline;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($instanceId, $pipeline) {
            return DB::table('eshop_pricing_rules as r')
                ->leftJoin('eshop_pricing_rule_configs as c', function ($join) use ($instanceId) {
                    $join->on('c.rule_id', '=', 'r.id')
                        ->where('c.instance_id', '=', $instanceId);
                })
                ->where('r.is_active', true)
                ->where(function ($q) use ($pipeline) {
                    $q->where('r.pipeline', $pipeline)
                        ->orWhere('r.pipeline', 'all');
                })
                // Per-instance config can deactivate a globally active rule
                ->where(function ($q) {
                    $q->whereNull('c.id')               // no per-instance override → use global
                        ->orWhere('c.is_active', true);  // per-instance override is active
                })
                ->pluck('r.slug')
                ->all();
        });
    }

    /**
     * Flush the cached active slugs (e.g. after admin changes rule config).
     */
    public function clearCache(int $instanceId): void
    {
        foreach (['retail', 'channel', 'wholesale'] as $pipeline) {
            Cache::forget(self::CACHE_KEY . ':' . $instanceId . ':' . $pipeline);
        }
    }
}

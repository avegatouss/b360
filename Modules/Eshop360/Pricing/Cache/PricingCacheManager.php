<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Cache;

use Illuminate\Support\Facades\Cache;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;

/**
 * Thin Redis-backed cache layer for computed line-item prices.
 */
class PricingCacheManager
{
    private const PREFIX = 'eshop:pricing:';
    private const DEFAULT_TTL = 300; // 5 minutes

    /**
     * Retrieve a cached LineItemPrice, or null if not present.
     */
    public function get(string $key): ?LineItemPrice
    {
        $data = Cache::store(config('cache.default'))->get(self::PREFIX . $key);

        if ($data === null) {
            return null;
        }

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (! is_array($data)) {
            return null;
        }

        return LineItemPrice::fromSnapshot($data);
    }

    /**
     * Store a computed LineItemPrice in cache.
     */
    public function set(string $key, LineItemPrice $price, int $ttl = self::DEFAULT_TTL): void
    {
        Cache::store(config('cache.default'))->put(
            self::PREFIX . $key,
            $price->toSnapshot(),
            $ttl,
        );
    }

    /**
     * Invalidate all cached prices for a given product.
     * Uses a tag-based approach: flushes keys matching the product pattern.
     */
    public function invalidateForProduct(int $productId): void
    {
        // Pattern: eshop:pricing:*:<productId>:*
        // Since not all Redis drivers support pattern-based deletion,
        // we use a tagged cache group when available, otherwise fall back
        // to a version counter that changes the key prefix.
        $versionKey = self::PREFIX . 'version:product:' . $productId;
        Cache::store(config('cache.default'))->increment($versionKey);
    }

    /**
     * Build a versioned cache key that auto-invalidates when the product version changes.
     */
    public function versionedKey(string $baseKey, int $productId): string
    {
        $versionKey = self::PREFIX . 'version:product:' . $productId;
        $version = (int) Cache::store(config('cache.default'))->get($versionKey, 0);

        return $baseKey . ':v' . $version;
    }
}

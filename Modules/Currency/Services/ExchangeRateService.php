<?php

namespace Modules\Currency\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Currency\Models\ExchangeRateHistory;

/**
 * Exchange rate service with Redis cache and API fallback.
 *
 * Resolution order:
 * 1. Redis cache (TTL 1 hour)
 * 2. Primary API (open.er-api.com)
 * 3. Fallback API (exchangerate-api.com)
 * 4. Last known rate from exchange_rate_history table
 *
 * Every fetched rate is persisted to exchange_rate_history for audit.
 */
final class ExchangeRateService
{
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get exchange rate between two currencies.
     */
    public function getRate(string $from, string $to): float
    {
        if ($from === $to) {
            return 1.0;
        }

        $cacheKey = "exchange_rate.{$from}.{$to}";

        return (float) Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            try {
                $rate = $this->fetchFromPrimary($from, $to);
                $this->persistHistory($from, $to, $rate, 'open.er-api.com');
                return $rate;
            } catch (\Throwable $e) {
                Log::warning("Primary exchange rate API failed for {$from}/{$to}: {$e->getMessage()}");
            }

            try {
                $rate = $this->fetchFromFallback($from, $to);
                $this->persistHistory($from, $to, $rate, 'exchangerate-api.com');
                return $rate;
            } catch (\Throwable $e) {
                Log::warning("Fallback exchange rate API failed for {$from}/{$to}: {$e->getMessage()}");
            }

            // Last resort: historical rate from database
            $historical = ExchangeRateHistory::latestRate($from, $to);
            if ($historical !== null) {
                Log::info("Using historical exchange rate for {$from}/{$to}: {$historical}");
                return $historical;
            }

            // Absolute fallback: use config rates from CurrencyManager
            $manager = app(CurrencyManager::class);
            $rates = $manager->rates();
            $fromRate = $rates[$from] ?? 1.0;
            $toRate = $rates[$to] ?? 1.0;

            return $toRate / $fromRate;
        });
    }

    /**
     * Convert an amount between currencies using the current rate.
     */
    public function convert(float $amount, string $from, string $to, ?int $decimals = null): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate = $this->getRate($from, $to);
        $converted = $amount * $rate;

        if ($decimals !== null) {
            return round($converted, $decimals);
        }

        // Use the target currency's decimal precision
        $manager = app(CurrencyManager::class);
        return round($converted, $manager->decimals($to));
    }

    /**
     * Persist a rate to the exchange_rate_history table for auditing.
     */
    public function persistHistory(string $from, string $to, float $rate, string $source): void
    {
        try {
            ExchangeRateHistory::create([
                'base_code' => $from,
                'target_code' => $to,
                'rate' => $rate,
                'source' => $source,
                'fetched_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("Failed to persist exchange rate history: {$e->getMessage()}");
        }
    }

    /**
     * Bulk fetch rates for a base currency from the primary API.
     *
     * @return array<string, float> ['USD' => 1.08, 'XOF' => 655.957, ...]
     */
    public function fetchAllRates(string $baseCurrency): array
    {
        $response = Http::timeout(15)
            ->connectTimeout(5)
            ->get("https://open.er-api.com/v6/latest/{$baseCurrency}");

        if (!$response->successful()) {
            throw new \RuntimeException("Exchange rate API returned HTTP {$response->status()}");
        }

        return $response->json('rates', []);
    }

    /**
     * Clear cached rates for a specific pair or all pairs.
     */
    public function clearCache(?string $from = null, ?string $to = null): void
    {
        if ($from && $to) {
            Cache::forget("exchange_rate.{$from}.{$to}");
        }
    }

    private function fetchFromPrimary(string $from, string $to): float
    {
        $response = Http::timeout(10)
            ->connectTimeout(5)
            ->get("https://open.er-api.com/v6/latest/{$from}");

        if (!$response->successful()) {
            throw new \RuntimeException("Primary API returned HTTP {$response->status()}");
        }

        $rates = $response->json('rates', []);

        if (!isset($rates[$to])) {
            throw new \RuntimeException("Currency {$to} not found in primary API response");
        }

        return (float) $rates[$to];
    }

    private function fetchFromFallback(string $from, string $to): float
    {
        $response = Http::timeout(10)
            ->connectTimeout(5)
            ->get("https://v6.exchangerate-api.com/v6/latest/{$from}");

        if (!$response->successful()) {
            throw new \RuntimeException("Fallback API returned HTTP {$response->status()}");
        }

        $rates = $response->json('conversion_rates', []);

        if (!isset($rates[$to])) {
            throw new \RuntimeException("Currency {$to} not found in fallback API response");
        }

        return (float) $rates[$to];
    }
}

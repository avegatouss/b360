<?php

namespace Modules\Currency\Services;

use Modules\Core\Support\CurrentInstance;

final class CurrencyManager
{
    /**
     * Resolve the active currency for the current context.
     * Priority: Currency module setting → billing.currency setting → config.
     *
     * When Currency module is active, it overrides billing.currency.
     */
    public function resolve(?int $instanceId = null): string
    {
        if ($instanceId === null) {
            $current = CurrentInstance::get();
            $instanceId = $current?->id;
        }

        // Currency module override: currency.active setting
        if (function_exists('setting')) {
            $override = setting('currency.active', null, $instanceId);
            if ($override && $this->isSupported($override)) {
                return $override;
            }
        }

        // Fallback to billing.currency setting directly (avoid calling currency() to prevent recursion)
        if (function_exists('setting')) {
            $billingCurrency = setting('billing.currency', null, $instanceId);
            if ($billingCurrency) {
                return $billingCurrency;
            }
        }

        return config('billing.currency', 'EUR');
    }

    /**
     * Convert an amount from one currency to another.
     */
    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rates = $this->rates();

        $fromRate = $rates[$from] ?? 1.0;
        $toRate = $rates[$to] ?? 1.0;

        // Convert to base (EUR), then to target
        $baseAmount = $amount / $fromRate;

        return round($baseAmount * $toRate, $this->decimals($to));
    }

    /**
     * Format an amount with currency symbol.
     */
    public function format(float $amount, ?string $code = null): string
    {
        $code = $code ?? $this->resolve();
        $info = $this->info($code);

        $formatted = number_format($amount, $info['decimals'], '.', ' ');

        return "{$formatted} {$info['symbol']}";
    }

    /**
     * Get exchange rates (from settings or config fallback).
     */
    public function rates(): array
    {
        $configRates = config('currency.rates', []);

        if (function_exists('setting')) {
            $customRates = setting('currency.rates');
            if (is_array($customRates) && !empty($customRates)) {
                return array_merge($configRates, $customRates);
            }
        }

        return $configRates;
    }

    public function supported(): array
    {
        return config('currency.supported', []);
    }

    public function isSupported(string $code): bool
    {
        return array_key_exists($code, $this->supported());
    }

    public function info(string $code): array
    {
        return $this->supported()[$code] ?? ['name' => $code, 'symbol' => $code, 'decimals' => 2];
    }

    public function decimals(string $code): int
    {
        return $this->info($code)['decimals'] ?? 2;
    }

    public function symbol(string $code): string
    {
        return $this->info($code)['symbol'] ?? $code;
    }
}

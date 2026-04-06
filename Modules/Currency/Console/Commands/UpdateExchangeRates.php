<?php

namespace Modules\Currency\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\Currency\Models\Currency;
use Modules\Currency\Services\ExchangeRateService;

class UpdateExchangeRates extends Command
{
    protected $signature = 'currency:update-rates';

    protected $description = 'Fetch and update exchange rates from open.er-api.com with history persistence';

    public function handle(ExchangeRateService $exchangeRateService): int
    {
        $this->info('Fetching exchange rates...');

        try {
            $default = Currency::where('is_default', true)->first();

            if (!$default) {
                $this->warn('No default currency set. Using USD as base.');
                $baseCurrency = 'USD';
            } else {
                $baseCurrency = $default->code;
            }

            $rates = $exchangeRateService->fetchAllRates($baseCurrency);

            if (empty($rates)) {
                $this->error('No rates returned from API.');
                return self::FAILURE;
            }

            $updated = 0;
            $currencies = Currency::where('is_default', false)
                ->where('auto_update', true)
                ->where('is_active', true)
                ->get();

            foreach ($currencies as $currency) {
                if (isset($rates[$currency->code])) {
                    $oldRate = $currency->rate;
                    $newRate = (float) $rates[$currency->code];

                    $currency->update([
                        'rate' => $newRate,
                        'rate_updated_at' => now(),
                    ]);

                    // Persist to exchange_rate_history for audit trail
                    $exchangeRateService->persistHistory(
                        $baseCurrency,
                        $currency->code,
                        $newRate,
                        'open.er-api.com'
                    );

                    $this->line("  {$currency->code}: {$oldRate} -> {$newRate}");
                    $updated++;
                } else {
                    $this->warn("  {$currency->code}: not found in API response.");
                }
            }

            $this->info("Updated {$updated} exchange rate(s) based on {$baseCurrency}.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

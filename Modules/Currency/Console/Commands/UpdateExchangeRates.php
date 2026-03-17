<?php

namespace Modules\Currency\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\Currency\Models\Currency;

class UpdateExchangeRates extends Command
{
    protected $signature = 'currency:update-rates';

    protected $description = 'Fetch and update exchange rates from open.er-api.com';

    public function handle(): int
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

            $response = Http::timeout(15)->get("https://open.er-api.com/v6/latest/{$baseCurrency}");

            if (!$response->successful()) {
                $this->error('API request failed: HTTP ' . $response->status());
                return self::FAILURE;
            }

            $data = $response->json();
            $rates = $data['rates'] ?? [];

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
                    $currency->update([
                        'rate' => $rates[$currency->code],
                        'rate_updated_at' => now(),
                    ]);
                    $this->line("  {$currency->code}: {$oldRate} -> {$rates[$currency->code]}");
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

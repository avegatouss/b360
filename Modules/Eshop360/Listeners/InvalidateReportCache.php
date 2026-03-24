<?php

namespace Modules\Eshop360\Listeners;

use Illuminate\Support\Facades\Cache;
use Modules\Eshop360\Events\ReportDataChanged;

class InvalidateReportCache
{
    /**
     * Report cache tag prefixes by domain.
     */
    private const DOMAIN_PREFIXES = [
        'sales'   => ['report:overview:', 'report:salesByCategory:', 'report:salesByProduct:', 'report:posOverview:', 'report:monthlyRevenue:', 'report:cashbook:', 'report:customerDues:', 'report:employeeCommissions:', 'report:taxReport:'],
        'stock'   => ['report:overview:', 'report:stockReport:'],
        'finance' => ['report:overview:', 'report:cashbook:', 'report:monthlyExpenses:', 'report:supplierDues:'],
    ];

    public function handle(ReportDataChanged $event): void
    {
        $instanceId = $event->instanceId;

        if ($event->domain === 'all') {
            // Flush all report caches for this instance
            $this->forgetByManifest($instanceId);
            return;
        }

        $prefixes = self::DOMAIN_PREFIXES[$event->domain] ?? [];

        foreach ($prefixes as $prefix) {
            $this->forgetByPrefix($prefix, $instanceId);
        }
    }

    /**
     * Forget cached keys tracked in the manifest for this instance.
     */
    private function forgetByManifest(int $instanceId): void
    {
        $manifestKey = "report:manifest:{$instanceId}";
        $keys = Cache::get($manifestKey, []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget($manifestKey);
    }

    /**
     * Forget all cache keys matching a prefix pattern for an instance.
     * Since Cache doesn't support wildcard forget, we use the manifest.
     */
    private function forgetByPrefix(string $prefix, int $instanceId): void
    {
        $manifestKey = "report:manifest:{$instanceId}";
        $keys = Cache::get($manifestKey, []);

        $remaining = [];
        foreach ($keys as $key) {
            if (str_starts_with($key, $prefix)) {
                Cache::forget($key);
            } else {
                $remaining[] = $key;
            }
        }

        if (count($remaining) !== count($keys)) {
            Cache::put($manifestKey, $remaining, 7200);
        }
    }
}

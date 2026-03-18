<?php

namespace Modules\Eshop360\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Events\ReportDataChanged;
use Modules\Eshop360\Listeners\InvalidateReportCache;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Services\ReportService;
use Modules\Eshop360\Tests\TestCase;

final class ReportCacheTest extends TestCase
{
    private ReportService $reports;
    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();

        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);
        $this->instanceId = $instance->id;

        $this->reports = new ReportService();
    }

    public function test_overview_result_is_cached(): void
    {
        $from = now()->startOfMonth()->toDateString();
        $to = now()->toDateString();

        $result1 = $this->reports->overview($this->instanceId, $from, $to);
        $result2 = $this->reports->overview($this->instanceId, $from, $to);

        // Both should be identical (served from cache)
        $this->assertSame($result1, $result2);

        // Cache key should exist
        $cacheKey = "report:overview:{$this->instanceId}:{$from}:{$to}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_cache_manifest_tracks_keys(): void
    {
        $from = now()->startOfMonth()->toDateString();
        $to = now()->toDateString();

        $this->reports->overview($this->instanceId, $from, $to);
        $this->reports->salesByCategory($this->instanceId, $from, $to);

        $manifest = Cache::get("report:manifest:{$this->instanceId}", []);

        $this->assertCount(2, $manifest);
        $this->assertStringContainsString('report:overview:', $manifest[0]);
        $this->assertStringContainsString('report:salesByCategory:', $manifest[1]);
    }

    public function test_report_data_changed_event_clears_sales_cache(): void
    {
        $from = now()->startOfMonth()->toDateString();
        $to = now()->toDateString();

        // Populate caches
        $this->reports->overview($this->instanceId, $from, $to);
        $this->reports->salesByCategory($this->instanceId, $from, $to);
        $this->reports->stockReport($this->instanceId);

        $overviewKey = "report:overview:{$this->instanceId}:{$from}:{$to}";
        $salesKey = "report:salesByCategory:{$this->instanceId}:{$from}:{$to}";
        $stockKey = "report:stockReport:{$this->instanceId}:all";

        $this->assertTrue(Cache::has($overviewKey));
        $this->assertTrue(Cache::has($salesKey));
        $this->assertTrue(Cache::has($stockKey));

        // Fire sales domain event
        $listener = new InvalidateReportCache();
        $listener->handle(new ReportDataChanged($this->instanceId, 'sales'));

        // Sales-related caches should be cleared
        $this->assertFalse(Cache::has($overviewKey));
        $this->assertFalse(Cache::has($salesKey));
        // Stock cache should remain
        $this->assertTrue(Cache::has($stockKey));
    }

    public function test_report_data_changed_all_clears_everything(): void
    {
        $from = now()->startOfMonth()->toDateString();
        $to = now()->toDateString();

        $this->reports->overview($this->instanceId, $from, $to);
        $this->reports->stockReport($this->instanceId);
        $this->reports->monthlyExpenses($this->instanceId, now()->year);

        // Fire 'all' domain event
        $listener = new InvalidateReportCache();
        $listener->handle(new ReportDataChanged($this->instanceId, 'all'));

        // All caches should be gone
        $manifest = Cache::get("report:manifest:{$this->instanceId}", []);
        $this->assertEmpty($manifest);
    }

    public function test_stock_report_returns_expected_structure(): void
    {
        $result = $this->reports->stockReport($this->instanceId);

        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total_items', $result);
        $this->assertArrayHasKey('total_quantity', $result);
        $this->assertArrayHasKey('total_value', $result);
        $this->assertArrayHasKey('low_stock', $result);
    }

    public function test_customer_dues_returns_expected_structure(): void
    {
        $result = $this->reports->customerDues($this->instanceId);

        $this->assertIsArray($result);
    }

    public function test_different_date_ranges_produce_different_cache_keys(): void
    {
        $this->reports->overview($this->instanceId, '2026-01-01', '2026-01-31');
        $this->reports->overview($this->instanceId, '2026-02-01', '2026-02-28');

        $manifest = Cache::get("report:manifest:{$this->instanceId}", []);

        $this->assertCount(2, $manifest);
    }
}

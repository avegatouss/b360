<?php

namespace Modules\Currency\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Core\Tests\Concerns\RequiresEshop360Schema;
use Modules\Currency\Models\ExchangeRateHistory;
use Modules\Currency\Models\OrderCurrencySnapshot;
use Modules\Currency\Models\TenantCurrencySetting;
use Modules\Currency\Services\ExchangeRateService;
use Modules\Currency\Services\SnapshotService;
use Modules\Currency\Services\TenantCurrencyManager;

/**
 * Tests for multi-currency Phase 1 & 2 features.
 */
final class MultiCurrencyTest extends \Modules\Billing\Tests\TestCase
{
    use RequiresEshop360Schema;

    // ──────────────────────────────────────────
    // ExchangeRateService
    // ──────────────────────────────────────────

    public function test_conversion_same_currency_returns_same_amount(): void
    {
        $service = app(ExchangeRateService::class);

        $result = $service->convert(1000, 'XOF', 'XOF');

        $this->assertEquals(1000.0, $result);
    }

    public function test_get_rate_same_currency_returns_one(): void
    {
        $service = app(ExchangeRateService::class);

        $rate = $service->getRate('EUR', 'EUR');

        $this->assertEquals(1.0, $rate);
    }

    public function test_get_rate_uses_cache_on_second_call(): void
    {
        Cache::flush();

        // Fake API response
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'rates' => ['XOF' => 655.957],
            ]),
        ]);

        $service = app(ExchangeRateService::class);

        $rate1 = $service->getRate('EUR', 'XOF');
        $rate2 = $service->getRate('EUR', 'XOF');

        $this->assertEquals($rate1, $rate2);

        // Only 1 HTTP request should have been made (second call from cache)
        Http::assertSentCount(1);
    }

    public function test_persist_history_writes_to_database(): void
    {
        $instance = $this->makeRootInstance();

        $service = app(ExchangeRateService::class);
        $service->persistHistory('EUR', 'XOF', 655.957, 'test');

        $this->assertDatabaseHas('exchange_rate_history', [
            'base_code' => 'EUR',
            'target_code' => 'XOF',
            'source' => 'test',
        ]);
    }

    public function test_fallback_to_historical_rate_when_apis_fail(): void
    {
        $instance = $this->makeRootInstance();
        Cache::flush();

        // Insert a historical rate
        ExchangeRateHistory::create([
            'base_code' => 'EUR',
            'target_code' => 'GBP',
            'rate' => 0.86,
            'source' => 'manual',
            'fetched_at' => now(),
        ]);

        // Both APIs fail
        Http::fake([
            'open.er-api.com/*' => Http::response('error', 500),
            'v6.exchangerate-api.com/*' => Http::response('error', 500),
        ]);

        $service = app(ExchangeRateService::class);
        $rate = $service->getRate('EUR', 'GBP');

        $this->assertEquals(0.86, $rate);
    }

    // ──────────────────────────────────────────
    // TenantCurrencyManager
    // ──────────────────────────────────────────

    public function test_tenant_default_currency_is_xof(): void
    {
        $instance = $this->makeRootInstance();

        $manager = app(TenantCurrencyManager::class);
        $default = $manager->getDefault($instance->id);

        $this->assertEquals('XOF', $default);
    }

    public function test_multi_currency_disabled_by_default(): void
    {
        $instance = $this->makeRootInstance();

        $manager = app(TenantCurrencyManager::class);

        $this->assertFalse($manager->isMultiCurrencyEnabled($instance->id));
    }

    public function test_user_preference_overrides_tenant_default(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeUser('pref@test.com');

        $manager = app(TenantCurrencyManager::class);
        $manager->setUserPreference($user->id, $instance->id, 'EUR');

        $currency = $manager->resolveDisplayCurrency($instance->id, $user->id);

        $this->assertEquals('EUR', $currency);
    }

    public function test_session_override_takes_priority(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeUser('session@test.com');

        $manager = app(TenantCurrencyManager::class);

        // Set persistent preference to EUR
        $manager->setUserPreference($user->id, $instance->id, 'EUR');

        // Set session override to USD
        $manager->setSessionCurrency($instance->id, 'USD');

        $currency = $manager->resolveDisplayCurrency($instance->id, $user->id);

        $this->assertEquals('USD', $currency);
    }

    // ──────────────────────────────────────────
    // SnapshotService
    // ──────────────────────────────────────────

    public function test_snapshot_creates_immutable_record(): void
    {
        $this->requireEshop360Schema();
        $instance = $this->makeRootInstance();
        $this->makeRootSuperAdmin($instance);

        // Create a mock order-like model
        $order = \Modules\Eshop360\Domain\Sales\Models\Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'SNP-001',
            'total' => 5000,
            'subtotal' => 4237,
            'status' => 'completed',
        ]);

        $service = app(SnapshotService::class);
        $snapshot = $service->snapshot($order, 'EUR', 'XOF', 655.957);

        $this->assertInstanceOf(OrderCurrencySnapshot::class, $snapshot);
        $this->assertEquals('EUR', $snapshot->display_currency);
        $this->assertEquals('XOF', $snapshot->base_currency);
        $this->assertEquals(655.957, $snapshot->exchange_rate);
        $this->assertArrayHasKey('total_display', $snapshot->amounts);
        $this->assertArrayHasKey('total_base', $snapshot->amounts);
        $this->assertEquals(5000, $snapshot->amounts['total_display']);
    }

    public function test_snapshot_does_not_change_after_rate_update(): void
    {
        $this->requireEshop360Schema();
        $instance = $this->makeRootInstance();
        $this->makeRootSuperAdmin($instance);

        $order = \Modules\Eshop360\Domain\Sales\Models\Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'SNP-002',
            'total' => 10000,
            'status' => 'completed',
        ]);

        $service = app(SnapshotService::class);

        // Snapshot with rate 655
        $snapshot = $service->snapshot($order, 'EUR', 'XOF', 655.0);
        $originalBase = $snapshot->amounts['total_base'];

        // Rate changes to 700 — snapshot must NOT change
        $snapshot->refresh();
        $this->assertEquals(655.0, $snapshot->exchange_rate);
        $this->assertEquals($originalBase, $snapshot->amounts['total_base']);
    }

    // ──────────────────────────────────────────
    // Backward compatibility
    // ──────────────────────────────────────────

    public function test_orders_without_currency_code_default_to_null(): void
    {
        $this->requireEshop360Schema();
        $instance = $this->makeRootInstance();
        $this->makeRootSuperAdmin($instance);

        $order = \Modules\Eshop360\Domain\Sales\Models\Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'LEGACY-001',
            'total' => 3000,
            'status' => 'pending',
        ]);

        // Existing orders without currency_code should show null (backward compat)
        $this->assertNull($order->currency_code);
        $this->assertNull($order->exchange_rate);
    }

    // ──────────────────────────────────────────
    // Mass-assignment regression — guards against $fillable drift
    // ──────────────────────────────────────────

    public function test_order_currency_columns_are_mass_assignable(): void
    {
        $this->requireEshop360Schema();
        $instance = $this->makeRootInstance();
        $this->makeRootSuperAdmin($instance);

        $order = \Modules\Eshop360\Domain\Sales\Models\Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'MA-001',
            'total' => 10000,
            'status' => 'completed',
            'currency_code' => 'EUR',
            'exchange_rate' => 655.957,
            'amount_in_base_currency' => 6559570.00,
        ]);

        $order->refresh();

        // These would all be NULL if currency_code/exchange_rate/amount_in_base_currency
        // were missing from $fillable (mass-assignment silently dropped).
        $this->assertSame('EUR', $order->currency_code);
        $this->assertEquals(655.957, (float) $order->exchange_rate);
        $this->assertEquals(6559570.00, (float) $order->amount_in_base_currency);
    }

    public function test_invoice_currency_columns_are_mass_assignable(): void
    {
        $this->requireEshop360Schema();
        $instance = $this->makeRootInstance();
        $this->makeRootSuperAdmin($instance);

        $invoice = \Modules\Eshop360\Domain\Finance\Models\Invoice::create([
            'instance_id' => $instance->id,
            'invoice_number' => 'MA-INV-001',
            'total' => 5000,
            'status' => 'unpaid',
            'currency_code' => 'USD',
            'exchange_rate' => 600.0,
            'amount_in_base_currency' => 3000000.0,
        ]);

        $invoice->refresh();

        $this->assertSame('USD', $invoice->currency_code);
        $this->assertEquals(600.0, (float) $invoice->exchange_rate);
        $this->assertEquals(3000000.0, (float) $invoice->amount_in_base_currency);
    }

    public function test_payment_currency_columns_are_mass_assignable(): void
    {
        $this->requireEshop360Schema();
        $instance = $this->makeRootInstance();
        $this->makeRootSuperAdmin($instance);

        $order = \Modules\Eshop360\Domain\Sales\Models\Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'MA-PAY-001',
            'total' => 1000,
            'status' => 'completed',
        ]);

        $payment = \Modules\Eshop360\Domain\Finance\Models\Payment::create([
            'instance_id' => $instance->id,
            'payable_type' => 'Modules\Eshop360\Models\Order',
            'payable_id' => $order->id,
            'amount' => 1000,
            'method' => 'cash',
            'status' => 'completed',
            'currency_code' => 'EUR',
            'exchange_rate' => 655.957,
            'amount_in_base_currency' => 655957.0,
        ]);

        $payment->refresh();

        $this->assertSame('EUR', $payment->currency_code);
        $this->assertEquals(655.957, (float) $payment->exchange_rate);
        $this->assertEquals(655957.0, (float) $payment->amount_in_base_currency);
    }

    // ──────────────────────────────────────────
    // SnapshotService::snapshotIfEnabled() — high-level helper
    // ──────────────────────────────────────────

    public function test_snapshot_if_enabled_is_noop_when_multi_currency_disabled(): void
    {
        $this->requireEshop360Schema();
        $instance = $this->makeRootInstance();
        $this->makeRootSuperAdmin($instance);

        $order = \Modules\Eshop360\Domain\Sales\Models\Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'NOOP-001',
            'total' => 1000,
            'status' => 'completed',
        ]);

        $service = app(SnapshotService::class);
        $result = $service->snapshotIfEnabled($order, $instance->id);

        $this->assertNull($result);

        $order->refresh();
        $this->assertNull($order->currency_code);
        $this->assertNull($order->exchange_rate);
    }

    public function test_snapshot_if_enabled_writes_base_when_display_equals_base(): void
    {
        $this->requireEshop360Schema();
        $instance = $this->makeRootInstance();
        $this->makeRootSuperAdmin($instance);

        // Enable multi-currency for the instance with default = XOF
        TenantCurrencySetting::create([
            'instance_id' => $instance->id,
            'default_currency' => 'XOF',
            'allowed_currencies' => ['XOF'],
            'multi_currency_enabled' => true,
            'auto_update_rates' => false,
            'api_source' => 'open.er-api.com',
        ]);

        $order = \Modules\Eshop360\Domain\Sales\Models\Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'SAME-001',
            'total' => 2500,
            'status' => 'completed',
        ]);

        $service = app(SnapshotService::class);
        $result = $service->snapshotIfEnabled($order, $instance->id);

        // Same currency: no polymorphic snapshot, but currency_code + exchange_rate = 1.0
        $this->assertNull($result);

        $order->refresh();
        $this->assertSame('XOF', $order->currency_code);
        $this->assertEquals(1.0, (float) $order->exchange_rate);
    }

    public function test_snapshot_if_enabled_creates_polymorphic_snapshot_when_currencies_differ(): void
    {
        $this->requireEshop360Schema();
        Cache::flush();

        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        // Enable multi-currency, allow EUR + XOF, default = XOF
        TenantCurrencySetting::create([
            'instance_id' => $instance->id,
            'default_currency' => 'XOF',
            'allowed_currencies' => ['XOF', 'EUR'],
            'multi_currency_enabled' => true,
            'auto_update_rates' => false,
            'api_source' => 'open.er-api.com',
        ]);

        // User prefers EUR
        app(TenantCurrencyManager::class)->setUserPreference($user->id, $instance->id, 'EUR');

        // Stub the exchange rate API
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'rates' => ['EUR' => 0.001525], // 1 XOF = 0.001525 EUR ≈ rate
            ]),
        ]);

        $order = \Modules\Eshop360\Domain\Sales\Models\Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'DIFF-001',
            'total' => 100000,
            'subtotal' => 100000,
            'status' => 'completed',
        ]);

        $service = app(SnapshotService::class);
        $snapshot = $service->snapshotIfEnabled($order, $instance->id, $user->id);

        $this->assertInstanceOf(OrderCurrencySnapshot::class, $snapshot);
        $this->assertSame('EUR', $snapshot->display_currency);
        $this->assertSame('XOF', $snapshot->base_currency);
        $this->assertGreaterThan(0, (float) $snapshot->exchange_rate);

        // Entity should also have its currency columns populated (mass-assignment fix)
        $order->refresh();
        $this->assertSame('EUR', $order->currency_code);
        $this->assertGreaterThan(0, (float) $order->exchange_rate);
    }
}

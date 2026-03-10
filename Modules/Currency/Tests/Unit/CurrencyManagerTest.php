<?php

namespace Modules\Currency\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Modules\Currency\Services\CurrencyManager;
use Modules\Currency\Tests\TestCase;

final class CurrencyManagerTest extends TestCase
{
    private CurrencyManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(CurrencyManager::class);
    }

    public function test_resolve_returns_billing_currency_when_no_override(): void
    {
        // Set billing.currency as global setting
        DB::connection('system')->table('settings')->insert([
            'instance_id' => 0,
            'group' => 'billing',
            'key' => 'currency',
            'value' => 'USD',
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('USD', $this->manager->resolve());
    }

    public function test_resolve_uses_currency_module_override(): void
    {
        // Set billing.currency global
        DB::connection('system')->table('settings')->insert([
            'instance_id' => 0,
            'group' => 'billing',
            'key' => 'currency',
            'value' => 'EUR',
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Set currency.active override
        DB::connection('system')->table('settings')->insert([
            'instance_id' => 0,
            'group' => 'currency',
            'key' => 'active',
            'value' => 'XOF',
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('XOF', $this->manager->resolve());
    }

    public function test_convert_same_currency_returns_same_amount(): void
    {
        $this->assertEquals(100.0, $this->manager->convert(100, 'EUR', 'EUR'));
    }

    public function test_convert_eur_to_xof(): void
    {
        config(['currency.rates' => ['EUR' => 1.0, 'XOF' => 655.957]]);

        $result = $this->manager->convert(100, 'EUR', 'XOF');

        // 100 EUR * 655.957 = 65595.7, XOF has 0 decimals → 65596
        $this->assertEquals(65596, $result);
    }

    public function test_convert_usd_to_eur(): void
    {
        config(['currency.rates' => ['EUR' => 1.0, 'USD' => 1.08]]);

        $result = $this->manager->convert(108, 'USD', 'EUR');

        // 108 USD / 1.08 = 100 EUR
        $this->assertEquals(100.0, $result);
    }

    public function test_format_with_eur(): void
    {
        config(['currency.supported' => ['EUR' => ['name' => 'Euro', 'symbol' => "\u{20AC}", 'decimals' => 2]]]);

        $formatted = $this->manager->format(1234.56, 'EUR');

        $this->assertStringContainsString('1 234.56', $formatted);
        $this->assertStringContainsString("\u{20AC}", $formatted);
    }

    public function test_format_with_xof_no_decimals(): void
    {
        config(['currency.supported' => ['XOF' => ['name' => 'Franc CFA', 'symbol' => 'CFA', 'decimals' => 0]]]);

        $formatted = $this->manager->format(65596, 'XOF');

        $this->assertStringContainsString('65 596', $formatted);
        $this->assertStringContainsString('CFA', $formatted);
    }

    public function test_supported_returns_all_currencies(): void
    {
        $supported = $this->manager->supported();

        $this->assertArrayHasKey('EUR', $supported);
        $this->assertArrayHasKey('USD', $supported);
        $this->assertArrayHasKey('XOF', $supported);
    }

    public function test_is_supported(): void
    {
        $this->assertTrue($this->manager->isSupported('EUR'));
        $this->assertTrue($this->manager->isSupported('XOF'));
        $this->assertFalse($this->manager->isSupported('BTC'));
    }

    public function test_symbol(): void
    {
        $this->assertSame('CFA', $this->manager->symbol('XOF'));
    }

    public function test_decimals(): void
    {
        $this->assertSame(0, $this->manager->decimals('XOF'));
        $this->assertSame(2, $this->manager->decimals('EUR'));
        $this->assertSame(3, $this->manager->decimals('TND'));
    }
}

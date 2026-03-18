<?php

namespace Modules\Eshop360\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\EshopModuleSetting;
use Modules\Eshop360\Services\EshopSettingsService;
use Modules\Eshop360\Tests\TestCase;

final class EshopSettingsServiceTest extends TestCase
{
    private EshopSettingsService $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $this->settings = new EshopSettingsService();
    }

    public function test_get_returns_defaults_when_no_db_record(): void
    {
        $pos = $this->settings->get('pos');

        $this->assertIsArray($pos);
        $this->assertSame('layout1', $pos['default_layout']);
        $this->assertSame(24, $pos['products_per_page']);
        $this->assertTrue($pos['print_receipt']);
    }

    public function test_set_persists_to_database(): void
    {
        $this->settings->set('pos', ['default_layout' => 'layout3', 'products_per_page' => 48]);

        $record = EshopModuleSetting::where('instance_id', CurrentInstance::get()->id)
            ->where('group', 'pos')
            ->first();

        $this->assertNotNull($record);
        $this->assertSame('layout3', $record->data['default_layout']);
        $this->assertSame(48, $record->data['products_per_page']);
    }

    public function test_get_reads_from_database(): void
    {
        // Write directly to DB
        EshopModuleSetting::create([
            'instance_id' => CurrentInstance::get()->id,
            'group' => 'invoice',
            'data' => ['company_name' => 'ACME Corp', 'default_due_days' => 15],
        ]);

        // Clear cache so it reads from DB
        Cache::flush();

        $invoice = $this->settings->get('invoice');

        $this->assertSame('ACME Corp', $invoice['company_name']);
        $this->assertSame(15, $invoice['default_due_days']);
        // Defaults should be merged
        $this->assertSame('default', $invoice['default_template']);
    }

    public function test_set_updates_cache(): void
    {
        $this->settings->set('printer', ['receipt_width' => 58]);

        // Second call should hit cache, not DB
        $printer = $this->settings->get('printer');

        $this->assertSame(58, $printer['receipt_width']);
    }

    public function test_value_returns_single_setting(): void
    {
        $this->settings->set('pos', ['products_per_page' => 36]);

        $this->assertSame(36, $this->settings->value('pos', 'products_per_page'));
    }

    public function test_value_returns_default_for_missing_key(): void
    {
        $result = $this->settings->value('pos', 'nonexistent_key', 'fallback');

        $this->assertSame('fallback', $result);
    }

    public function test_forget_clears_cache(): void
    {
        $this->settings->set('pos', ['default_layout' => 'layout2']);

        $this->settings->forget('pos');

        // Next get should re-read from DB (which has layout2)
        $pos = $this->settings->get('pos');
        $this->assertSame('layout2', $pos['default_layout']);
    }

    public function test_set_overwrite_existing_record(): void
    {
        $this->settings->set('pos', ['default_layout' => 'layout2']);
        $this->settings->set('pos', ['default_layout' => 'layout5', 'sound_enabled' => false]);

        $pos = $this->settings->get('pos');

        $this->assertSame('layout5', $pos['default_layout']);
        $this->assertFalse($pos['sound_enabled']);

        // Should only have 1 record
        $count = EshopModuleSetting::where('instance_id', CurrentInstance::get()->id)
            ->where('group', 'pos')
            ->count();
        $this->assertSame(1, $count);
    }

    public function test_defaults_method(): void
    {
        $defaults = $this->settings->defaults('pos');

        $this->assertSame('layout1', $defaults['default_layout']);
        $this->assertSame(['cash', 'card'], $defaults['payment_methods']);
    }

    public function test_instance_isolation(): void
    {
        $instance1 = CurrentInstance::get();
        $this->settings->set('pos', ['default_layout' => 'layout3']);

        // Switch to another instance
        $instance2 = \App\Instances\Instance::create([
            'name' => 'Other', 'slug' => 'other', 'is_active' => true,
        ]);
        CurrentInstance::set($instance2);
        Cache::flush();

        // Instance 2 should get defaults, not instance 1's data
        $pos = $this->settings->get('pos');
        $this->assertSame('layout1', $pos['default_layout']);

        // Restore
        CurrentInstance::set($instance1);
    }
}

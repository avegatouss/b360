<?php

namespace Modules\Settings\Tests\Feature;

use Modules\Settings\Tests\TestCase;
use Modules\Settings\Services\SettingsManager;

final class SettingHelperTest extends TestCase
{
    public function test_setting_helper_exists(): void
    {
        $this->assertTrue(function_exists('setting'));
    }

    public function test_setting_helper_returns_default(): void
    {
        $this->assertSame('default', setting('nonexistent.key', 'default'));
    }

    public function test_setting_helper_returns_value(): void
    {
        app(SettingsManager::class)->set('app.name', 'B360 Helper Test');
        $this->assertSame('B360 Helper Test', setting('app.name'));
    }

    public function test_setting_helper_supports_instance_id(): void
    {
        $mgr = app(SettingsManager::class);
        $mgr->set('app.theme', 'light');
        $mgr->set('app.theme', 'dark', 5);

        $this->assertSame('light', setting('app.theme'));
        $this->assertSame('dark', setting('app.theme', null, 5));
    }
}

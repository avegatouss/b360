<?php

namespace Modules\Settings\Tests\Unit;

use Modules\Settings\Models\Setting;
use PHPUnit\Framework\TestCase;

final class SettingModelTest extends TestCase
{
    public function test_casted_value_string(): void
    {
        $setting = new Setting();
        $setting->type = 'string';
        $setting->value = 'hello';

        $this->assertSame('hello', $setting->casted_value);
    }

    public function test_casted_value_boolean_true(): void
    {
        $setting = new Setting();
        $setting->type = 'boolean';
        $setting->value = '1';

        $this->assertTrue($setting->casted_value);
    }

    public function test_casted_value_boolean_false(): void
    {
        $setting = new Setting();
        $setting->type = 'boolean';
        $setting->value = '0';

        $this->assertFalse($setting->casted_value);
    }

    public function test_casted_value_integer(): void
    {
        $setting = new Setting();
        $setting->type = 'integer';
        $setting->value = '42';

        $this->assertSame(42, $setting->casted_value);
    }

    public function test_casted_value_json(): void
    {
        $setting = new Setting();
        $setting->type = 'json';
        $setting->value = '{"key":"val"}';

        $this->assertSame(['key' => 'val'], $setting->casted_value);
    }
}

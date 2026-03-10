<?php

namespace Modules\Settings\Tests\Feature;

use Modules\Settings\Tests\TestCase;
use Modules\Settings\Services\SettingsManager;

final class SettingsManagerTest extends TestCase
{
    private function manager(): SettingsManager
    {
        return app(SettingsManager::class);
    }

    public function test_get_returns_default_when_no_setting(): void
    {
        $this->assertSame('fallback', $this->manager()->get('app.name', 'fallback'));
    }

    public function test_set_and_get_string(): void
    {
        $this->manager()->set('app.name', 'B360 Test');
        $this->assertSame('B360 Test', $this->manager()->get('app.name'));
    }

    public function test_set_and_get_boolean(): void
    {
        $this->manager()->set('instances.allow_creation', true, 0, 'boolean');
        $this->assertTrue($this->manager()->get('instances.allow_creation'));

        $this->manager()->set('instances.allow_creation', false, 0, 'boolean');
        $this->assertFalse($this->manager()->get('instances.allow_creation'));
    }

    public function test_set_and_get_integer(): void
    {
        $this->manager()->set('instances.max_instances', 10, 0, 'integer');
        $this->assertSame(10, $this->manager()->get('instances.max_instances'));
    }

    public function test_set_and_get_json(): void
    {
        $data = ['key1' => 'val1', 'key2' => 'val2'];
        $this->manager()->set('app.config', $data, 0, 'json');
        $this->assertSame($data, $this->manager()->get('app.config'));
    }

    public function test_instance_override_cascades_to_global(): void
    {
        // Global setting
        $this->manager()->set('app.name', 'Global Name');

        // Instance-specific override
        $this->manager()->set('app.name', 'Instance Name', 5);

        // Without instance: returns global
        $this->assertSame('Global Name', $this->manager()->get('app.name'));

        // With instance 5: returns override
        $this->assertSame('Instance Name', $this->manager()->get('app.name', null, 5));

        // With instance 99 (no override): cascades to global
        $this->assertSame('Global Name', $this->manager()->get('app.name', null, 99));
    }

    public function test_group_returns_all_settings_for_group(): void
    {
        $this->manager()->set('instances.allow_creation', true, 0, 'boolean');
        $this->manager()->set('instances.max_instances', 5, 0, 'integer');

        $group = $this->manager()->group('instances');

        $this->assertArrayHasKey('allow_creation', $group);
        $this->assertArrayHasKey('max_instances', $group);
        $this->assertTrue($group['allow_creation']);
        $this->assertSame(5, $group['max_instances']);
    }

    public function test_group_with_caching(): void
    {
        $this->manager()->set('app.name', 'Cached');

        // First call populates cache
        $group1 = $this->manager()->group('app');
        $this->assertSame('Cached', $group1['name']);

        // Modify directly in DB (bypass cache)
        \Illuminate\Support\Facades\DB::connection('system')
            ->table('settings')
            ->where('group', 'app')
            ->where('key', 'name')
            ->update(['value' => 'Updated']);

        // Should still return cached value
        $group2 = $this->manager()->group('app');
        $this->assertSame('Cached', $group2['name']);

        // After cache clear, returns new value
        $this->manager()->clearCache(0, 'app');
        $group3 = $this->manager()->group('app');
        $this->assertSame('Updated', $group3['name']);
    }

    public function test_key_without_dot_uses_general_group(): void
    {
        $this->manager()->set('site_name', 'B360');
        $this->assertSame('B360', $this->manager()->get('site_name'));

        $group = $this->manager()->group('general');
        $this->assertArrayHasKey('site_name', $group);
    }

    public function test_set_updates_existing_value(): void
    {
        $this->manager()->set('app.name', 'First');
        $this->assertSame('First', $this->manager()->get('app.name'));

        $this->manager()->set('app.name', 'Second');
        $this->assertSame('Second', $this->manager()->get('app.name'));
    }
}

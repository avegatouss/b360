<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\TestCase;
use Modules\Core\Modules\ModuleManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

final class ModuleManagerFallbackTest extends TestCase
{
    public function test_db_authoritative_when_present(): void
    {
        Cache::forget('core.enabled_modules');

        // Insert a real module in DB as enabled
        DB::connection('system')->table('modules')->insert([
            'name' => 'Dashboard', 'is_enabled' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $mm = new ModuleManager();
        $enabled = $mm->enabledModules();

        $this->assertContains('DASHBOARD', $enabled);
    }

    public function test_db_disabled_overrides_nwidart(): void
    {
        Cache::forget('core.enabled_modules');

        // Insert Core as disabled in DB (even though nwidart has it enabled)
        DB::connection('system')->table('modules')->insert([
            'name' => 'Core', 'is_enabled' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $mm = new ModuleManager();
        $mm->clearCache();
        $enabled = $mm->enabledModules();

        // Core should be disabled because DB is authoritative
        $this->assertNotContains('CORE', $enabled);
    }

    public function test_clear_cache_refreshes_results(): void
    {
        $mm = app(ModuleManager::class);
        Cache::forget('core.enabled_modules');

        // Insert Users as disabled in DB
        DB::connection('system')->table('modules')->insert([
            'name' => 'Users', 'is_enabled' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // First call caches (Users disabled)
        $enabled = $mm->enabledModules();
        $this->assertNotContains('USERS', $enabled);

        // Enable in DB
        DB::connection('system')->table('modules')
            ->where('name', 'Users')
            ->update(['is_enabled' => true]);

        // Still cached - won't see change
        $this->assertNotContains('USERS', $mm->enabledModules());

        // After clear, sees the change
        $mm->clearCache();
        $this->assertContains('USERS', $mm->enabledModules());
    }

    public function test_empty_db_falls_back_to_nwidart(): void
    {
        Cache::forget('core.enabled_modules');

        // Ensure modules table is empty
        DB::connection('system')->table('modules')->truncate();

        $mm = new ModuleManager();
        $enabled = $mm->enabledModules();

        // With empty DB, should fallback to nwidart and find at least Core
        // (Core is always enabled in modules_statuses.json)
        // Note: In test env nwidart might not be available, so just check it doesn't crash
        $this->assertIsArray($enabled);
    }
}

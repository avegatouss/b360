<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Modules\Core\Modules\ModuleManager;

final class ModuleManagerCacheTest extends TestCase
{
    public function test_enabled_modules_are_cached_and_can_be_cleared(): void
    {
        DB::connection('system')->table('modules')->insert([
            'name' => 'POS', 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()
        ]);

        $mm = app(ModuleManager::class);
        $this->assertTrue($mm->isEnabled('POS'));

        // Disable in DB but cache still returns true
        DB::connection('system')->table('modules')->where('name', 'POS')->update(['is_enabled' => false]);
        $this->assertTrue($mm->isEnabled('POS'));

        $mm->clearCache();
        $this->assertFalse($mm->isEnabled('POS'));
    }
}

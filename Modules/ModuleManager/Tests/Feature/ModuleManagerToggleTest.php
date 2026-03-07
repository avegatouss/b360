<?php

namespace Modules\ModuleManager\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Modules\ModuleManager\Tests\TestCase;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Module as NwidartModule;

final class ModuleManagerToggleTest extends TestCase
{
    public function test_toggle_blocks_protected_module(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->put("/i/{$root->slug}/modules/Core/toggle")
            ->assertStatus(403);
    }

    public function test_toggle_disables_and_enables_non_protected_module(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        DB::connection('system')->table('modules')->insert([
            'name' => 'CustomModule',
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $module = \Mockery::mock(NwidartModule::class);
        $module->shouldReceive('isEnabled')->andReturn(true, false);
        $module->shouldReceive('disable')->once();
        $module->shouldReceive('enable')->once();

        Module::shouldReceive('find')
            ->with('CustomModule')
            ->andReturn($module);

        $this->actingAs($user)
            ->put("/i/{$root->slug}/modules/CustomModule/toggle")
            ->assertStatus(302);

        $this->assertDatabaseHas('modules', [
            'name' => 'CustomModule',
            'is_enabled' => false,
        ]);

        $this->actingAs($user)
            ->put("/i/{$root->slug}/modules/CustomModule/toggle")
            ->assertStatus(302);

        $this->assertDatabaseHas('modules', [
            'name' => 'CustomModule',
            'is_enabled' => true,
        ]);
    }
}

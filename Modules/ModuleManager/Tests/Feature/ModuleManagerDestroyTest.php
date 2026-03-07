<?php

namespace Modules\ModuleManager\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Modules\ModuleManager\Tests\TestCase;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Module as NwidartModule;

final class ModuleManagerDestroyTest extends TestCase
{
    public function test_destroy_blocks_protected_module(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->delete("/i/{$root->slug}/modules/Core")
            ->assertStatus(403);
    }

    public function test_destroy_deletes_module_record(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        DB::connection('system')->table('modules')->insert([
            'name' => 'DisposableModule',
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $module = \Mockery::mock(NwidartModule::class);
        $module->shouldReceive('isEnabled')->andReturn(true);
        $module->shouldReceive('disable')->once();
        $module->shouldReceive('delete')->once();

        Module::shouldReceive('find')
            ->with('DisposableModule')
            ->andReturn($module);

        $this->actingAs($user)
            ->delete("/i/{$root->slug}/modules/DisposableModule")
            ->assertStatus(302);

        $this->assertDatabaseMissing('modules', ['name' => 'DisposableModule']);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Couture360\Tests\Feature;

use Modules\Couture360\Tests\TestCase;
use Nwidart\Modules\Facades\Module;

final class ModuleBootstrapTest extends TestCase
{
    public function test_registers_the_module_as_enabled(): void
    {
        $module = Module::find('Couture360');
        $this->assertNotNull($module);
        $this->assertTrue($module->isEnabled());
    }

    public function test_exposes_an_unauthenticated_health_endpoint(): void
    {
        $this->getJson('/api/couture/health')
            ->assertOk()
            ->assertJson(['status' => 'ok', 'module' => 'couture360']);
    }
}

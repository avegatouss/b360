<?php

namespace Modules\Core\Tests\Unit;

use App\Instances\Instance;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Tests\TestCase;

final class CurrentInstanceTest extends TestCase
{
    public function test_get_returns_instance_when_set(): void
    {
        $instance = Instance::create([
            'name' => 'Acme',
            'slug' => 'acme',
            'is_active' => true,
        ]);

        CurrentInstance::set($instance);

        $this->assertSame($instance->id, CurrentInstance::get()?->id);
    }

    public function test_get_returns_null_when_not_instance_or_not_set(): void
    {
        app()->forgetInstance('currentInstance');
        $this->assertNull(CurrentInstance::get());

        app()->instance('currentInstance', 'not-an-instance');
        $this->assertNull(CurrentInstance::get());
    }
}

<?php

namespace Modules\Core\Tests\Feature;

use App\Instances\Instance;
use Illuminate\Support\Facades\Route;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Tests\TestCase;

final class EnsureInstanceResolvedMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'core.instance.resolved'])
            ->get('/__test/instance-resolved', fn () => response('ok'));
    }

    public function test_blocks_when_instance_not_resolved(): void
    {
        CurrentInstance::set(null);

        $this->get('/__test/instance-resolved')->assertStatus(503);
    }

    public function test_allows_when_instance_is_resolved(): void
    {
        $instance = Instance::create([
            'name' => 'Acme',
            'slug' => 'acme',
            'is_active' => true,
        ]);

        CurrentInstance::set($instance);

        $this->get('/__test/instance-resolved')
            ->assertOk()
            ->assertSee('ok');
    }
}

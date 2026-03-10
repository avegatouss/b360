<?php

namespace Modules\Core\Tests\Feature;

use App\Instances\Instance;
use Illuminate\Support\Facades\Route;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Tests\TestCase;

final class BindInstanceFromRouteMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'core.instance.bind'])
            ->get('/__test/bind/{slug}', function () {
                return response(CurrentInstance::get()?->slug ?? 'none');
            });
    }

    public function test_returns_404_when_instance_not_found(): void
    {
        $this->get('/__test/bind/missing')->assertStatus(404);
    }

    public function test_binds_instance_from_slug(): void
    {
        Instance::create([
            'name' => 'Acme',
            'slug' => 'acme',
            'is_active' => true,
        ]);

        $this->get('/__test/bind/acme')
            ->assertOk()
            ->assertSee('acme');
    }
}

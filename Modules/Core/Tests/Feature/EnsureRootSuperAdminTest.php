<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\TestCase;
use Modules\Core\Tests\Helpers\CreatesInstanceContext;
use Modules\Core\Database\Seeders\CoreRbacSeeder;
use Modules\Core\Http\Middleware\EnsureRootSuperAdmin;
use Modules\Core\Support\TeamContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class EnsureRootSuperAdminTest extends TestCase
{
    use CreatesInstanceContext;

    private function runMiddleware(?object $user): Response|int
    {
        $middleware = new EnsureRootSuperAdmin();
        $request = Request::create('/test');
        if ($user) {
            $request->setUserResolver(fn () => $user);
        }

        try {
            return $middleware->handle($request, fn ($r) => new Response('OK'));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return $e->getStatusCode();
        }
    }

    public function test_allows_super_admin_on_root(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $root = $this->makeInstance('root');
        $this->bindInstance($root);

        $user = $this->makeUser('sa@test.com');
        TeamContext::clear();
        $user->assignRole('super-admin');

        $response = $this->runMiddleware($user);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_blocks_non_root_instance(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $instance = $this->makeInstance('acme');
        $this->bindInstance($instance);

        $user = $this->makeUser('sa@test.com');
        TeamContext::clear();
        $user->assignRole('super-admin');

        $this->assertSame(403, $this->runMiddleware($user));
    }

    public function test_blocks_regular_user_on_root(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $root = $this->makeInstance('root');
        $this->bindInstance($root);

        $user = $this->makeUser('regular@test.com');

        $this->assertSame(403, $this->runMiddleware($user));
    }

    public function test_blocks_unauthenticated_user(): void
    {
        $root = $this->makeInstance('root');
        $this->bindInstance($root);

        $this->assertSame(403, $this->runMiddleware(null));
    }

    public function test_blocks_instance_admin_on_root(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $root = $this->makeInstance('root');
        $this->bindInstance($root);

        $user = $this->makeUser('ia@test.com');
        TeamContext::clear();
        Permission::findOrCreate('instances.view');
        $role = Role::findOrCreate('instance-admin');
        $role->syncPermissions(['instances.view']);

        TeamContext::set($root->id);
        $user->assignRole($role);

        $this->assertSame(403, $this->runMiddleware($user));
    }
}

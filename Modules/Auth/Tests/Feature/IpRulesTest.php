<?php

namespace Modules\Auth\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Http\Middleware\CheckIpAccess;
use Modules\Auth\Models\IpRule;
use Modules\Auth\Tests\TestCase;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class IpRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.installed' => true]);
    }

    private function makeInstance(string $slug = 'acme'): Instance
    {
        return Instance::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function makeAdmin(Instance $instance): User
    {
        $user = User::create([
            'full_name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        TeamContext::clear();
        Role::findOrCreate('super-admin');
        $user->assignRole('super-admin');

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    public function test_admin_can_create_ip_rule(): void
    {
        $instance = $this->makeInstance('iptest');
        $user = $this->makeAdmin($instance);
        CurrentInstance::set($instance);

        $response = $this->actingAs($user)
            ->post(route('ip-rules.store', $instance->slug), [
                'ip_address' => '192.168.1.100',
                'type' => 'deny',
                'note' => 'Blocked suspicious IP',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('ip_rules', [
            'instance_id' => $instance->id,
            'ip_address' => '192.168.1.100',
            'type' => 'deny',
            'created_by' => $user->id,
        ]);
    }

    public function test_denied_ip_is_blocked(): void
    {
        $instance = $this->makeInstance('blocked');
        CurrentInstance::set($instance);

        // Create deny rule directly
        IpRule::create([
            'instance_id' => $instance->id,
            'ip_address' => '127.0.0.1',
            'type' => 'deny',
            'created_by' => 1,
        ]);

        // Simulate request through the middleware
        $middleware = new CheckIpAccess();

        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $aborted = false;
        try {
            $middleware->handle($request, function () {
                return response('OK');
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $aborted = $e->getStatusCode() === 403;
        }

        $this->assertTrue($aborted, 'Denied IP should receive 403');
    }

    public function test_allowed_ip_passes_whitelist(): void
    {
        $instance = $this->makeInstance('whitelist');
        CurrentInstance::set($instance);

        // Create allow rule for a specific IP
        IpRule::create([
            'instance_id' => $instance->id,
            'ip_address' => '10.0.0.1',
            'type' => 'allow',
            'created_by' => 1,
        ]);

        $middleware = new CheckIpAccess();

        // Request from allowed IP should pass
        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $request->server->set('REMOTE_ADDR', '10.0.0.1');

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertSame(200, $response->getStatusCode());

        // Request from non-whitelisted IP should be blocked
        $request2 = \Illuminate\Http\Request::create('/test', 'GET');
        $request2->server->set('REMOTE_ADDR', '192.168.1.1');

        $blocked = false;
        try {
            $middleware->handle($request2, function () {
                return response('OK');
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $blocked = $e->getStatusCode() === 403;
        }

        $this->assertTrue($blocked, 'Non-whitelisted IP should receive 403');
    }
}

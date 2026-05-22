<?php

namespace Modules\Auth\Tests\Unit;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Services\LoginRedirector;
use Modules\Auth\Tests\TestCase;

final class LoginRedirectorTest extends TestCase
{
    private function makeUser(string $email = 'user@test.com'): User
    {
        return User::create([
            'full_name' => 'User',
            'email' => $email,
            'password' => 'password',
        ]);
    }

    private function makeInstance(string $slug, bool $active = true): Instance
    {
        return Instance::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => $active,
        ]);
    }

    public function test_active_instances_filters_by_membership_and_active_instance(): void
    {
        $user = $this->makeUser();

        $activeInstance = $this->makeInstance('active');
        $inactiveInstance = $this->makeInstance('inactive', false);
        $invitedInstance = $this->makeInstance('invited');

        DB::connection('system')->table('instance_user')->insert([
            [
                'instance_id' => $activeInstance->id,
                'user_id' => $user->id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'instance_id' => $inactiveInstance->id,
                'user_id' => $user->id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'instance_id' => $invitedInstance->id,
                'user_id' => $user->id,
                'status' => 'invited',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $data = app(LoginRedirector::class)->activeInstances($user);

        $this->assertSame(1, $data['count']);
        $this->assertSame([['id' => $activeInstance->id, 'slug' => 'active']], $data['instances']);
    }

    public function test_redirect_after_global_login_when_no_active_instances(): void
    {
        $user = $this->makeUser();

        $response = app(LoginRedirector::class)->redirectAfterGlobalLogin($user);

        $this->assertSame(route('instances.no_active'), $response->getTargetUrl());
    }

    public function test_redirect_after_global_login_when_single_active_instance(): void
    {
        $user = $this->makeUser();
        $instance = $this->makeInstance('solo');

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = app(LoginRedirector::class)->redirectAfterGlobalLogin($user);

        $this->assertSame(url('/i/solo'), $response->getTargetUrl());
    }

    public function test_redirect_after_global_login_when_multiple_active_instances(): void
    {
        $user = $this->makeUser();
        $first = $this->makeInstance('first');
        $second = $this->makeInstance('second');

        DB::connection('system')->table('instance_user')->insert([
            [
                'instance_id' => $first->id,
                'user_id' => $user->id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'instance_id' => $second->id,
                'user_id' => $user->id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = app(LoginRedirector::class)->redirectAfterGlobalLogin($user);

        $this->assertSame(route('instances.select'), $response->getTargetUrl());
    }
}

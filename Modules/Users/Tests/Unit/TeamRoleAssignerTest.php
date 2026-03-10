<?php

namespace Modules\Users\Tests\Unit;

use App\Instances\Instance;
use App\Models\User;
use Modules\Core\Support\TeamContext;
use Modules\Users\Services\TeamRoleAssigner;
use Modules\Users\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class TeamRoleAssignerTest extends TestCase
{
    private function makeUser(string $email = 'role@test.com'): User
    {
        return User::create([
            'full_name' => 'Role User',
            'email' => $email,
            'password' => 'password',
        ]);
    }

    private function makeInstance(string $slug = 'acme'): Instance
    {
        return Instance::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function seedRolesForInstance(int $instanceId): void
    {
        TeamContext::set($instanceId);

        foreach (['user', 'manager', 'agent', 'instance-admin'] as $name) {
            Role::findOrCreate($name);
        }
    }

    public function test_sync_roles_filters_invalid_and_keeps_allowed(): void
    {
        $user = $this->makeUser();
        $instance = $this->makeInstance();

        $this->seedRolesForInstance($instance->id);

        app(TeamRoleAssigner::class)->syncRolesForInstance($user, $instance->id, [
            'invalid',
            'manager',
            'manager',
        ]);

        TeamContext::set($instance->id);
        $user->unsetRelation('roles');

        $this->assertTrue($user->hasRole('manager'));
        $this->assertFalse($user->hasRole('user'));
    }

    public function test_sync_roles_defaults_to_user_when_empty(): void
    {
        $user = $this->makeUser('default@test.com');
        $instance = $this->makeInstance('default');

        $this->seedRolesForInstance($instance->id);

        app(TeamRoleAssigner::class)->syncRolesForInstance($user, $instance->id, [
            'unknown',
        ]);

        TeamContext::set($instance->id);
        $user->unsetRelation('roles');

        $this->assertTrue($user->hasRole('user'));
    }
}

<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\TestCase;
use Modules\Core\Tests\Helpers\CreatesInstanceContext;
use Modules\Core\Database\Seeders\CoreRbacSeeder;
use Modules\Core\Support\TeamContext;

final class RbacTeamsTest extends TestCase
{
    use CreatesInstanceContext;

    public function test_super_admin_global_gate_before_allows_everything(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $u = $this->makeUser('sa@example.com');
        // assign global role (no team context)
        TeamContext::clear();
        $u->assignRole('super-admin');

        $this->assertTrue($u->can('users.manage'));
        $this->assertTrue($u->can('settings.manage'));
    }

    public function test_instance_admin_permission_is_team_scoped(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $i1 = $this->makeInstance('a');
        $i2 = $this->makeInstance('b');
        $u = $this->makeUser('ia@example.com');

        // Create role for this team, sync permissions, and assign
        TeamContext::set($i1->id);
        $role = \Spatie\Permission\Models\Role::findOrCreate('instance-admin');
        $role->syncPermissions(['instances.view', 'users.view', 'users.manage']);
        $u->assignRole('instance-admin');

        // In instance a -> allowed
        TeamContext::set($i1->id);
        $this->assertTrue($u->can('users.manage'));

        // In instance b -> NOT allowed (team mismatch)
        TeamContext::set($i2->id);
        $this->assertFalse($u->can('users.manage'));
    }
}

<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\TestCase;
use Modules\Core\Tests\Helpers\CreatesInstanceContext;
use Modules\Core\Database\Seeders\CoreRbacSeeder;
use Modules\Core\Support\TeamContext;
use Spatie\Permission\PermissionRegistrar;

final class TeamContextTest extends TestCase
{
    use CreatesInstanceContext;

    public function test_set_changes_spatie_team_id(): void
    {
        TeamContext::set(42);
        $this->assertSame(42, app(PermissionRegistrar::class)->getPermissionsTeamId());
    }

    public function test_clear_sets_global_team_id(): void
    {
        TeamContext::set(99);
        TeamContext::clear();
        $this->assertSame(TeamContext::GLOBAL_TEAM_ID, TeamContext::current());
    }

    public function test_current_returns_current_team_id(): void
    {
        TeamContext::set(7);
        $this->assertSame(7, TeamContext::current());
    }

    public function test_is_super_admin_returns_true_for_super_admin(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $user = $this->makeUser('sa@test.com');
        TeamContext::clear();
        $user->assignRole('super-admin');

        $this->assertTrue(TeamContext::isSuperAdmin($user));
    }

    public function test_is_super_admin_returns_false_for_regular_user(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $user = $this->makeUser('regular@test.com');

        $this->assertFalse(TeamContext::isSuperAdmin($user));
    }

    public function test_is_super_admin_returns_false_for_null(): void
    {
        $this->assertFalse(TeamContext::isSuperAdmin(null));
    }

    public function test_is_super_admin_works_regardless_of_current_team_context(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $user = $this->makeUser('sa2@test.com');
        TeamContext::clear();
        $user->assignRole('super-admin');

        // Set a different team context (simulating being on a specific instance)
        $instance = $this->makeInstance('acme');
        TeamContext::set($instance->id);

        // Should still detect super-admin even with different team context
        $this->assertTrue(TeamContext::isSuperAdmin($user));

        // Team context should be restored after check
        $this->assertSame($instance->id, TeamContext::current());
    }

    public function test_is_super_admin_returns_false_for_instance_admin(): void
    {
        $this->seed(CoreRbacSeeder::class);

        $instance = $this->makeInstance('acme');
        $user = $this->makeUser('ia@test.com');

        // Create the role for this specific instance team and assign it
        TeamContext::set($instance->id);
        \Spatie\Permission\Models\Role::findOrCreate('instance-admin');
        $user->assignRole('instance-admin');

        $this->assertFalse(TeamContext::isSuperAdmin($user));
    }
}

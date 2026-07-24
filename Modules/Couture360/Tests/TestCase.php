<?php

declare(strict_types=1);

namespace Modules\Couture360\Tests;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Tests\TestCase as BillingTestCase;
use Modules\Core\Support\TeamContext;
use Spatie\Permission\Models\Role;

/**
 * Base TestCase Couture360. Extends Billing (→ Core) for the multi-tenant
 * + RefreshDatabase setup (in-memory SQLite for default and `system`
 * connections, permission.teams=true).
 */
abstract class TestCase extends BillingTestCase
{
    protected function makeInstance(string $slug = 'atelier-1'): Instance
    {
        return Instance::create([
            'name' => 'Atelier '.$slug,
            'slug' => $slug,
            'is_active' => true,
            'meta' => [],
        ]);
    }

    /** Regular (non-super-admin) active member of the given instance. */
    protected function makeMember(Instance $instance, string $email = 'agent@test.com'): User
    {
        $user = User::create([
            'full_name' => 'Agent Test',
            'email' => $email,
            'password' => 'password',
        ]);

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    /**
     * Global super-admin: role assigned under the Spatie team-0 "cross-instance"
     * sentinel (see database/seeders/SuperAdminSeeder.php), deliberately NOT
     * inserted into `instance_user` for any instance. Mirrors
     * Modules\Billing\Tests\TestCase::makeRootSuperAdmin() minus the
     * membership row, so it exercises the InstanceAccessService bypass path
     * rather than the membership path.
     */
    protected function makeSuperAdmin(string $email = 'super-admin@test.com'): User
    {
        $user = User::create([
            'full_name' => 'Super Admin',
            'email' => $email,
            'password' => 'password',
        ]);

        TeamContext::clear();
        Role::findOrCreate('super-admin');
        $user->assignRole('super-admin');

        return $user;
    }
}

<?php

namespace Modules\Billing\Tests;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\TeamContext;
use Modules\Core\Tests\TestCase as CoreTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends CoreTestCase
{
    protected function makeRootInstance(): Instance
    {
        return Instance::create([
            'name' => 'Root',
            'slug' => 'root',
            'is_active' => true,
            'meta' => ['is_root' => true],
        ]);
    }

    protected function makeUser(string $email = 'admin@test.com'): User
    {
        return User::create([
            'full_name' => 'Admin',
            'email' => $email,
            'password' => 'password',
        ]);
    }

    protected function makeRootSuperAdmin(Instance $root): User
    {
        $user = $this->makeUser();

        TeamContext::clear();
        // Use Eloquent firstOrCreate (DB-authoritative) so Spatie's cache cannot
        // return a stale entry pointing to a rolled-back row in parallel-mode
        // tests (CACHE_STORE=array persists per worker process).
        Permission::firstOrCreate(['name' => 'billing.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'billing.manage', 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('super-admin');
        $user->assignRole('super-admin');

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $root->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }
}

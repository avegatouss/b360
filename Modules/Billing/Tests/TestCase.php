<?php

namespace Modules\Billing\Tests;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\TeamContext;
use Modules\Core\Tests\TestCase as CoreTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        Permission::findOrCreate('billing.view');
        Permission::findOrCreate('billing.manage');
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

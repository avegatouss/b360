<?php

namespace Modules\ModuleManager\Tests;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Support\TeamContext;
use Modules\Core\Tests\TestCase as CoreTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends CoreTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.installed' => true]);

        if (!Schema::connection('system')->hasTable('modules')) {
            Schema::connection('system')->create('modules', function ($table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->boolean('is_enabled')->default(false);
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->index(['is_enabled', 'name']);
            });
        }
    }

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

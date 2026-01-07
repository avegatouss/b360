<?php

namespace Modules\Core\Tests\Helpers;

use App\Models\User;
use App\Instances\Instance;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;

trait CreatesInstanceContext
{
    protected function makeInstance(string $slug = 'acme'): Instance
    {
        return Instance::query()->on('system')->create([
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    protected function makeUser(string $email = 'u@example.com'): User
    {
        return User::query()->on('system')->create([
            'name' => 'User',
            'email' => $email,
            'password' => bcrypt('password'),
        ]);
    }

    protected function bindInstance(Instance $instance): void
    {
        CurrentInstance::set($instance);
        TeamContext::set($instance->id);
    }

    protected function addActiveMembership(User $user, Instance $instance): void
    {
        DB::connection('system')->table('instance_user')->updateOrInsert(
            ['instance_id' => $instance->id, 'user_id' => $user->id],
            ['status' => 'active', 'created_at' => now(), 'updated_at' => now()]
        );
    }
}

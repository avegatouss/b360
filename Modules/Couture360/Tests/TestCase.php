<?php

declare(strict_types=1);

namespace Modules\Couture360\Tests;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Tests\TestCase as BillingTestCase;

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
}

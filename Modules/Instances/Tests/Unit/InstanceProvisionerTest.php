<?php

namespace Modules\Instances\Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Instances\Services\InstanceProvisioner;
use Modules\Instances\Tests\TestCase;
use RuntimeException;

final class InstanceProvisionerTest extends TestCase
{
    public function test_provision_shared_strategy_creates_instance_and_membership(): void
    {
        config(['app.instance_db_strategy' => 'shared']);

        $user = User::create([
            'full_name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $instance = app(InstanceProvisioner::class)->provision([
            'name' => 'Acme',
            'slug' => 'acme',
            'domain' => null,
            'subdomain' => null,
            'database' => null,
            'is_active' => true,
        ]);

        $this->assertNull($instance->database);
        $this->assertNull($instance->db_driver);

        $this->assertDatabaseHas('instance_user', [
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
        ]);
    }

    public function test_provision_dedicated_strategy_rejects_invalid_database_name(): void
    {
        config(['app.instance_db_strategy' => 'database-per-instance']);

        $this->expectException(RuntimeException::class);

        app(InstanceProvisioner::class)->provision([
            'name' => 'Bad DB',
            'slug' => 'bad-db',
            'database' => 'invalid-name',
            'is_active' => true,
        ]);
    }

    public function test_resolve_database_name_uses_prefix_and_suffix(): void
    {
        config([
            'app.instance_db_prefix' => 'pre_',
            'app.instance_db_suffix' => '_suf',
        ]);

        $provisioner = app(InstanceProvisioner::class);
        $method = new \ReflectionMethod($provisioner, 'resolveDatabaseName');
        $method->setAccessible(true);

        $name = $method->invoke($provisioner, 'acme-test', null);

        $this->assertSame('pre_acme_test_suf', $name);
    }
}

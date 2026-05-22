<?php

namespace Modules\Instances\Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Instances\Services\InstanceProvisioner;
use Tests\ExternalDbTestCase;

final class InstanceProvisioningDedicatedSuccessTest extends ExternalDbTestCase
{
    public function test_provision_creates_dedicated_database_on_mysql(): void
    {
        if (env('TEST_DB_DRIVER') !== 'mysql') {
            $this->markTestSkipped('Dedicated DB provisioning test requires MySQL (TEST_DB_DRIVER=mysql).');
        }

        config(['app.instance_db_strategy' => 'database-per-instance']);
        config(['app.instance_db_prefix' => 'b360_']);
        config(['app.instance_db_suffix' => '_test']);

        $user = User::create([
            'full_name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $slug = 'dedicated_' . uniqid();

        $instance = app(InstanceProvisioner::class)->provision([
            'name' => 'Dedicated',
            'slug' => $slug,
            'database' => null,
            'is_active' => true,
        ]);

        $this->assertNotEmpty($instance->database);
        $this->assertSame('mysql', $instance->db_driver);

        $exists = DB::connection('system')->selectOne(
            'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$instance->database]
        );
        $this->assertNotNull($exists);

        DB::connection('system')->statement("DROP DATABASE `{$instance->database}`");
    }
}

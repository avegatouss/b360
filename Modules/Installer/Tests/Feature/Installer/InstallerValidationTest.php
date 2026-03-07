<?php

namespace Modules\Installer\Tests\Feature\Installer;

use Modules\Installer\Tests\TestCase;

final class InstallerValidationTest extends TestCase
{
    public function test_database_test_requires_step1(): void
    {
        $this->postJson('/install/database/test', [])
            ->assertStatus(422)
            ->assertJsonFragment(['ok' => false]);
    }

    public function test_database_test_validation_fails_with_missing_fields(): void
    {
        $this->withSession(['installer.steps' => [1 => true]]);

        $this->postJson('/install/database/test', [])
            ->assertStatus(422)
            ->assertJsonFragment(['ok' => false])
            ->assertJsonStructure(['errors']);
    }

    public function test_configuration_requires_steps_1_and_2(): void
    {
        $this->postJson('/install/configuration/validate', [])
            ->assertStatus(422);

        $this->withSession(['installer.steps' => [1 => true]]);

        $this->postJson('/install/configuration/validate', [])
            ->assertStatus(422);
    }

    public function test_admin_requires_steps_1_2_3(): void
    {
        $this->postJson('/install/admin/validate', [])
            ->assertStatus(422);

        $this->withSession([
            'installer.steps' => [1 => true, 2 => true, 3 => true],
            'installer' => ['db_confirmed' => false, 'config_confirmed' => false],
        ]);

        $this->postJson('/install/admin/validate', [])
            ->assertStatus(422);
    }
}

<?php

namespace Modules\Installer\Tests\Feature\Installer;

use Modules\Installer\Tests\TestCase;

final class InstallerRequirementsTest extends TestCase
{
    public function test_requirements_endpoint_returns_expected_payload(): void
    {
        $response = $this->getJson('/install/requirements')
            ->assertOk();

        $response->assertJsonStructure([
            'ok',
            'php' => ['min', 'current', 'ok'],
            'extensions' => ['required', 'missing', 'ok'],
            'permissions' => ['paths', 'not_writable', 'ok'],
            'env' => ['path', 'exists', 'ok'],
        ]);

        $response->assertSessionHas('installer.steps.1');
    }
}

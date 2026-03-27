<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SelectInventoryCommandTest extends TestCase
{
    public function test_it_writes_select_inventory_artifacts(): void
    {
        Config::set('ui-selects.inventory.json_path', 'storage/framework/testing/selects-inventory.json');
        Config::set('ui-selects.inventory.markdown_path', 'storage/framework/testing/selects-inventory.md');
        Config::set('ui-selects.inventory.stub_path', 'storage/framework/testing/selects-overrides.stub.php');

        $jsonPath = base_path(config('ui-selects.inventory.json_path'));
        $markdownPath = base_path(config('ui-selects.inventory.markdown_path'));
        $stubPath = base_path(config('ui-selects.inventory.stub_path'));

        foreach ([$jsonPath, $markdownPath, $stubPath] as $path) {
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $this->artisan('ui:inventory-selects --write')
            ->expectsOutputToContain('Selects detectes')
            ->expectsOutputToContain('Artefacts ecrits')
            ->assertExitCode(0);

        $this->assertFileExists($jsonPath);
        $this->assertFileExists($markdownPath);
        $this->assertFileExists($stubPath);

        $this->assertStringContainsString('"selects"', File::get($jsonPath));
        $this->assertStringContainsString('# Inventaire Selects', File::get($markdownPath));
        $this->assertStringContainsString("'overrides' => [", File::get($stubPath));
    }
}

<?php

namespace Modules\ModuleManager\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Core\Modules\ModuleManager;
use Modules\ModuleManager\Services\ModuleInstaller;
use Modules\ModuleManager\Tests\TestCase;
use Nwidart\Modules\Facades\Module;
use RuntimeException;
use ZipArchive;

final class ModuleInstallerTest extends TestCase
{
    private function makeZipWithModuleJson(string $moduleName): string
    {
        $tmpDir = storage_path('app/tmp');
        File::ensureDirectoryExists($tmpDir);

        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . 'module-' . uniqid() . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('module.json', json_encode([
            'name' => $moduleName,
            'providers' => ["Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider"],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $zip->close();

        return $zipPath;
    }

    private function makeZipWithoutModuleJson(): string
    {
        $tmpDir = storage_path('app/tmp');
        File::ensureDirectoryExists($tmpDir);

        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . 'module-' . uniqid() . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('readme.txt', 'no module json');
        $zip->close();

        return $zipPath;
    }

    private function makeZipWithMigrations(string $moduleName): string
    {
        $tmpDir = storage_path('app/tmp');
        File::ensureDirectoryExists($tmpDir);

        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . 'module-' . uniqid() . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);

        $zip->addFromString('module.json', json_encode([
            'name' => $moduleName,
            'providers' => ["Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider"],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $zip->addFromString('Database/Migrations/2026_01_01_000000_create_dummy_table.php', '<?php // dummy migration');

        $zip->close();

        return $zipPath;
    }

    public function test_install_from_zip_copies_module_and_updates_db(): void
    {
        $moduleName = 'TempTestModule';
        $zipPath = $this->makeZipWithModuleJson($moduleName);

        $uploaded = new UploadedFile($zipPath, 'module.zip', 'application/zip', null, true);

        Artisan::shouldReceive('call')
            ->once()
            ->with('module:enable', ['module' => $moduleName])
            ->andReturn(0);

        $cleared = new class extends ModuleManager {
            public int $count = 0;
            public function clearCache(): void { $this->count++; }
        };
        $this->app->instance(ModuleManager::class, $cleared);

        $name = app(ModuleInstaller::class)->installFromZip($uploaded);

        $this->assertSame($moduleName, $name);
        $this->assertDatabaseHas('modules', [
            'name' => $moduleName,
            'is_enabled' => true,
        ]);
        $this->assertTrue(File::isDirectory(base_path("Modules/{$moduleName}")));
        $this->assertSame(1, $cleared->count);

        File::delete($zipPath);
        File::deleteDirectory(base_path("Modules/{$moduleName}"));
    }

    public function test_install_from_zip_requires_module_json(): void
    {
        $zipPath = $this->makeZipWithoutModuleJson();
        $uploaded = new UploadedFile($zipPath, 'module.zip', 'application/zip', null, true);

        $this->expectException(RuntimeException::class);

        try {
            app(ModuleInstaller::class)->installFromZip($uploaded);
        } finally {
            File::delete($zipPath);
        }
    }

    public function test_uninstall_disables_and_removes_module_record(): void
    {
        DB::connection('system')->table('modules')->insert([
            'name' => 'DisposableModule',
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $module = \Mockery::mock(\Nwidart\Modules\Module::class);
        $module->shouldReceive('isEnabled')->andReturn(true);
        $module->shouldReceive('disable')->once();
        $module->shouldReceive('delete')->once();

        Module::shouldReceive('find')
            ->with('DisposableModule')
            ->andReturn($module);

        app(ModuleInstaller::class)->uninstall('DisposableModule');

        $this->assertDatabaseMissing('modules', ['name' => 'DisposableModule']);
    }

    public function test_install_runs_module_migrations_when_present(): void
    {
        $moduleName = 'MigratedModule';
        $zipPath = $this->makeZipWithMigrations($moduleName);
        $uploaded = new UploadedFile($zipPath, 'module.zip', 'application/zip', null, true);

        Artisan::shouldReceive('call')
            ->once()
            ->with('module:enable', ['module' => $moduleName])
            ->andReturn(0);

        Artisan::shouldReceive('call')
            ->once()
            ->with('module:migrate', ['module' => $moduleName, '--force' => true])
            ->andReturn(0);

        $name = app(ModuleInstaller::class)->installFromZip($uploaded);

        $this->assertSame($moduleName, $name);

        File::delete($zipPath);
        File::deleteDirectory(base_path("Modules/{$moduleName}"));
    }
}

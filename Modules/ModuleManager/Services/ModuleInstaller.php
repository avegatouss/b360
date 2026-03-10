<?php

namespace Modules\ModuleManager\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Core\Modules\ModuleManager;
use Nwidart\Modules\Facades\Module;
use RuntimeException;
use ZipArchive;

final class ModuleInstaller
{
    public function installFromZip(UploadedFile $file): string
    {
        $tmpDir = storage_path('app/tmp/module-install-' . uniqid());

        try {
            $this->extractZip($file, $tmpDir);

            $moduleJson = $this->findModuleJson($tmpDir);
            if (!$moduleJson) {
                throw new RuntimeException('Fichier module.json introuvable dans le ZIP.');
            }

            $config = json_decode(file_get_contents($moduleJson), true);
            if (!$config || empty($config['name']) || empty($config['providers'])) {
                throw new RuntimeException('module.json invalide : "name" et "providers" sont requis.');
            }

            $name = $config['name'];
            $sourceDir = dirname($moduleJson);
            $targetDir = base_path("Modules/{$name}");

            // Copier vers Modules/
            if (File::isDirectory($targetDir)) {
                File::deleteDirectory($targetDir);
            }
            File::copyDirectory($sourceDir, $targetDir);

            // Activer le module via nwidart
            Artisan::call('module:enable', ['module' => $name]);

            // Mettre à jour la table modules
            DB::connection('system')->table('modules')->updateOrInsert(
                ['name' => $name],
                ['is_enabled' => true, 'updated_at' => now(), 'created_at' => now()]
            );

            // Exécuter les migrations du module
            if (File::isDirectory("{$targetDir}/Database/Migrations")) {
                Artisan::call('module:migrate', ['module' => $name, '--force' => true]);
            }

            app(ModuleManager::class)->clearCache();

            return $name;
        } finally {
            if (File::isDirectory($tmpDir)) {
                File::deleteDirectory($tmpDir);
            }
        }
    }

    public function uninstall(string $name): void
    {
        $mod = Module::find($name);

        if ($mod) {
            if ($mod->isEnabled()) {
                $mod->disable();
            }
            $mod->delete();
        }

        DB::connection('system')
            ->table('modules')
            ->where('name', $name)
            ->delete();

        app(ModuleManager::class)->clearCache();
    }

    private function extractZip(UploadedFile $file, string $targetDir): void
    {
        $zip = new ZipArchive();
        $result = $zip->open($file->getRealPath());

        if ($result !== true) {
            throw new RuntimeException('Impossible d\'ouvrir le fichier ZIP.');
        }

        File::ensureDirectoryExists($targetDir);
        $zip->extractTo($targetDir);
        $zip->close();
    }

    private function findModuleJson(string $dir): ?string
    {
        // Chercher module.json à la racine
        $direct = $dir . '/module.json';
        if (file_exists($direct)) {
            return $direct;
        }

        // Chercher dans un sous-dossier unique (pattern courant dans les ZIPs)
        $subdirs = File::directories($dir);
        if (count($subdirs) === 1) {
            $nested = $subdirs[0] . '/module.json';
            if (file_exists($nested)) {
                return $nested;
            }
        }

        return null;
    }
}

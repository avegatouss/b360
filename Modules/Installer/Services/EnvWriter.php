<?php

namespace Modules\Installer\Services;
use App\Installer\InstallLock;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Gestionnaire sécurisé et atomique du fichier .env
 *
 * Implémente une stratégie de mise à jour sécurisée avec :
 * - Backup automatique avec permissions sécurisées
 * - Écriture atomique (évite la corruption)
 * - Restauration automatique en cas d'échec
 * - Gestion robuste des valeurs et échappement
 *
 * @final Pour garantir l'intégrité du processus d'écriture
 */
class EnvWriter
{
    /**
     * Chemin du fichier de backup .env
     * Stocké dans storage/app/b360/ pour isolation et sécurité
     */
    public const BACKUP_FILENAME = 'env.backup';

    /**
     * Écrit ou met à jour le fichier .env avec les données d'installation
     *
     * Séquence sécurisée :
     * 1. Vérification que l'application n'est pas déjà installée
     * 2. Création du .env si inexistant (.env.example ou squelette minimal)
     * 3. Backup unique avec permissions sécurisées
     * 4. Application des mises à jour ciblées
     * 5. Écriture atomique avec vérification
     *
     * @param array $data Données de configuration d'installation
     * @return void
     * @throws RuntimeException Si installation déjà terminée ou erreur d'écriture
     */
    public function write(array $data): void
    {
         // Double barrière : flag config + lock FS
        if (config('app.installed', false) === true || InstallLock::isInstalled()) {
            throw new RuntimeException('Application déjà installée.');
        }

        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        // Créer un .env si absent
        if (!File::exists($envPath)) {
            if (File::exists($examplePath)) {
                $this->atomicWrite($envPath, File::get($examplePath));
            } else {
                $this->atomicWrite($envPath, $this->minimalSkeleton());
            }
        }

        // Backup unique (servira au rollback)
        $backupPath = $this->backupPath();
        if (!File::exists($backupPath)) {
            File::ensureDirectoryExists(dirname($backupPath));
            File::copy($envPath, $backupPath);
            @chmod($backupPath, 0640);
        }

        $content = File::get($envPath);

        $updates = [];

        // Application
        $updates['APP_NAME']      = $data['app_name'] ?? 'B360';
        $updates['APP_ENV']       = ($data['environment_mode'] ?? 'production') === 'demo' ? 'local' : 'production';
        $updates['APP_DEBUG']     = ($data['environment_mode'] ?? 'production') === 'demo' ? 'true' : 'false';
        $updates['APP_URL']       = $data['app_url'] ?? 'http://localhost';
        $updates['APP_TIMEZONE']  = $data['timezone'] ?? 'UTC';
        $updates['APP_LOCALE']    = $data['locale'] ?? 'fr';
        $updates['APP_INSTALLED'] = 'false';

        // Session et cache en "file" pendant l'installation.
        // Évite SQLSTATE[HY000][1049] si SESSION_DRIVER=database ou CACHE_STORE=database
        // et que la DB cible n'existe pas encore (retentative après échec).
        $updates['SESSION_DRIVER'] = 'file';
        $updates['CACHE_STORE']    = 'file';

        // Instance (v1)
        $updates['INSTANCE_MODE'] = $data['instance_mode'] ?? 'single';
        $updates['INSTANCE_RESOLUTION'] = $data['instance_resolution'] ?? 'path';
        $updates['INSTANCE_DB_STRATEGY'] = $data['instance_db_strategy'] ?? 'shared';
        $updates['DB_PREFIX']     = $data['db_prefix'] ?? '';
        $updates['DB_SUFFIX']     = $data['db_suffix'] ?? '';

        // DB centrale
        $updates['DB_CONNECTION'] = $data['db_connection'] ?? 'mysql';
        $updates['DB_HOST']       = $data['db_host'] ?? '127.0.0.1';
        $updates['DB_PORT']       = (string)($data['db_port'] ?? 3306);
        $updates['DB_DATABASE']   = $data['db_database'] ?? '';
        $updates['DB_USERNAME']   = $data['db_username'] ?? '';
        $updates['DB_PASSWORD']   = $data['db_password'] ?? '';

        // APP_KEY si absent
        $currentKey = $this->getEnvValue($content, 'APP_KEY');
        if (!$currentKey) {
            $updates['APP_KEY'] = 'base64:' . base64_encode(random_bytes(32));
        }

        foreach ($updates as $key => $value) {
            $content = $this->setEnvValue($content, $key, (string)$value);
        }

        $this->atomicWrite($envPath, $content);
        @chmod($envPath, 0640);
    }

    public function markInstalled(): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            throw new RuntimeException('.env introuvable.');
        }

        $content = File::get($envPath);
        $content = $this->setEnvValue($content, 'APP_INSTALLED', 'true');

        $this->atomicWrite($envPath, $content);
        @chmod($envPath, 0640);
        // Réduction surface d’attaque : supprimer le backup après succès
        // (le lock FS + .env en place suffisent; le backup est sensible)
        $backup = $this->backupPath();
        if (File::exists($backup)) {
            @File::delete($backup);
        }
    }

    /**
     * Rollback minimal : restaure le backup du .env s'il existe.
     */
    public function restoreBackup(): void
    {
        $envPath = base_path('.env');
        $backupPath = $this->backupPath();

        if (!File::exists($backupPath)) {
            return;
        }

        $this->atomicWrite($envPath, File::get($backupPath));
        @chmod($envPath, 0640);
    }

    public function backupPath(): string
    {
        return storage_path('app/b360/env.backup');
    }

    private function minimalSkeleton(): string
    {
        return implode(PHP_EOL, [
            'APP_NAME="B360"',
            'APP_ENV=production',
            'APP_KEY=',
            'APP_DEBUG=false',
            'APP_URL=http://localhost',
            'APP_TIMEZONE=UTC',
            'APP_LOCALE=fr',
            'APP_INSTALLED=false',
            'SESSION_DRIVER=file',
            'CACHE_STORE=file',
            'INSTANCE_MODE=single',
            'INSTANCE_RESOLUTION=path',
            'INSTANCE_DB_STRATEGY=shared',
            '',
        ]) . PHP_EOL;
    }

    private function setEnvValue(string $content, string $key, string $value): string
    {
        $line = $key . '=' . $this->escapeEnvValue($value);

        if (preg_match('/^' . preg_quote($key, '/') . '=.*/m', $content)) {
            return preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $line, $content);
        }

        $content = rtrim($content) . PHP_EOL;
        return $content . $line . PHP_EOL;
    }

    private function getEnvValue(string $content, string $key): ?string
    {
        if (!preg_match('/^' . preg_quote($key, '/') . '=(.*)$/m', $content, $m)) {
            return null;
        }

        $raw = trim($m[1]);

        if ((str_starts_with($raw, '"') && str_ends_with($raw, '"')) ||
            (str_starts_with($raw, "'") && str_ends_with($raw, "'"))
        ) {
            $raw = substr($raw, 1, -1);
        }

        return $raw === '' ? null : $raw;
    }

    private function escapeEnvValue(string $value): string
    {
        if ($value === 'true' || $value === 'false' || is_numeric($value)) {
            return $value;
        }

        if ($value === '') {
            return '""';
        }

        if (preg_match('/\s|[#"\'=]/', $value)) {
            $value = str_replace('"', '\"', $value);
            return '"' . $value . '"';
        }

        return $value;
    }

    private function atomicWrite(string $path, string $content): void
    {

        // Écriture atomique + concurrent-safe : tmp unique puis rename
        $tmpPath = $path . '.' . bin2hex(random_bytes(6)) . '.tmp';
        File::put($tmpPath, $content);
        @chmod($tmpPath, 0640);
        // rename est atomique sur la plupart des FS
        if (!rename($tmpPath, $path)) {
            @unlink($tmpPath);
            throw new RuntimeException("Échec d'écriture atomique vers {$path}");
        }
    }
}

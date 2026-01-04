<?php

namespace Modules\Installer\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class EnvWriter
{
    /**
     * Crée/Met à jour le fichier .env de manière sécurisée et atomique.
     *
     * Politique:
     * - si .env n'existe pas : créer depuis .env.example (si possible), sinon squelette minimal
     * - si .env existe : backup puis update seulement des clés nécessaires
     */
    public function write(array $data): void
    {
        // Sécurité : déjà installé
        if (config('app.installed', false) === true) {
            throw new RuntimeException('Application déjà installée.');
        }

        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        // 1) Préparer un .env existant ou le créer
        if (!File::exists($envPath)) {
            if (File::exists($examplePath)) {
                // Créer depuis .env.example
                $this->atomicWrite($envPath, File::get($examplePath));
            } else {
                // Squelette minimal (fallback)
                $skeleton = implode(PHP_EOL, [
                    'APP_NAME="B360"',
                    'APP_ENV=production',
                    'APP_KEY=',
                    'APP_DEBUG=false',
                    'APP_URL=http://localhost',
                    'APP_TIMEZONE=UTC',
                    'APP_LOCALE=fr',
                    'APP_INSTALLED=false',
                    '',
                ]);
                $this->atomicWrite($envPath, $skeleton . PHP_EOL);
            }
        } else {
            // Si .env existe déjà, on backup (utile en cas de réinstall ou test)
            $backup = $envPath . '.bak.' . now()->format('YmdHis');
            File::copy($envPath, $backup);
        }

        // 2) Charger contenu actuel
        $content = File::get($envPath);

        // 3) Construire les valeurs à appliquer
        $updates = [];

        // Application
        $updates['APP_NAME']      = $data['app_name'] ?? 'B360';
        $updates['APP_ENV']       = 'production';
        $updates['APP_DEBUG']     = 'false';
        $updates['APP_URL']       = $data['app_url'] ?? 'http://localhost';
        $updates['APP_TIMEZONE']  = $data['timezone'] ?? 'UTC';
        $updates['APP_LOCALE']    = $data['locale'] ?? 'fr';
        $updates['APP_INSTALLED'] = 'false';

        // APP_KEY: si absent ou vide => générer
        $currentKey = $this->getEnvValue($content, 'APP_KEY');
        if (!$currentKey) {
            $updates['APP_KEY'] = 'base64:' . base64_encode(random_bytes(32));
        }

        // Database centrale
        $updates['DB_CONNECTION'] = $data['db_connection'] ?? 'mysql';
        $updates['DB_HOST']       = $data['db_host'] ?? '127.0.0.1';
        $updates['DB_PORT']       = (string)($data['db_port'] ?? 3306);
        $updates['DB_DATABASE']   = $data['db_database'] ?? '';
        $updates['DB_USERNAME']   = $data['db_username'] ?? '';
        $updates['DB_PASSWORD']   = $data['db_password'] ?? '';

        // Mode Instance
        $updates['INSTANCE_MODE'] = $data['instance_mode'] ?? 'single';
        $updates['DB_PREFIX']     = $data['db_prefix'] ?? '';
        $updates['DB_SUFFIX']     = $data['db_suffix'] ?? '';

        // 4) Appliquer updates (set/replace)
        foreach ($updates as $key => $value) {
            $content = $this->setEnvValue($content, $key, $value);
        }

        // 5) Écriture atomique + permissions
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
    }

    /**
     * Remplace ou ajoute une clé dans un contenu .env
     */
    private function setEnvValue(string $content, string $key, string $value): string
    {
        $line = $key . '=' . $this->escapeEnvValue($value);

        // Remplacer si existe (clé au début de ligne)
        if (preg_match('/^' . preg_quote($key, '/') . '=.*/m', $content)) {
            return preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $line, $content);
        }

        // Sinon ajouter en fin (avec saut de ligne propre)
        $content = rtrim($content) . PHP_EOL;
        return $content . $line . PHP_EOL;
    }

    /**
     * Lire une valeur d'env depuis le contenu .env (simple)
     */
    private function getEnvValue(string $content, string $key): ?string
    {
        if (!preg_match('/^' . preg_quote($key, '/') . '=(.*)$/m', $content, $m)) {
            return null;
        }
        $raw = trim($m[1]);

        // enlever quotes si présentes
        if ((str_starts_with($raw, '"') && str_ends_with($raw, '"')) ||
            (str_starts_with($raw, "'") && str_ends_with($raw, "'"))) {
            $raw = substr($raw, 1, -1);
        }
        return $raw === '' ? null : $raw;
    }

    /**
     * Échapper valeur .env : quoted si espace/symboles.
     */
    private function escapeEnvValue(string $value): string
    {
        // Valeurs bool/num simples
        if ($value === 'true' || $value === 'false' || is_numeric($value)) {
            return $value;
        }

        // Si vide
        if ($value === '') {
            return '""';
        }

        // Si contient espaces ou caractères spéciaux, on quote en double
        if (preg_match('/\s|[#"\'=]/', $value)) {
            $value = str_replace('"', '\"', $value);
            return '"' . $value . '"';
        }

        return $value;
    }

    /**
     * Écriture atomique sur disque
     */
    private function atomicWrite(string $path, string $content): void
    {
        $tmpPath = $path . '.tmp';
        File::put($tmpPath, $content);
        File::move($tmpPath, $path);
    }
}

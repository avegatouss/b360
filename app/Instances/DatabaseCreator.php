<?php

namespace App\Instances;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use App\Installer\InstallLock;
class DatabaseCreator
{
    /**
     * Crée une base de données MySQL pour une instance.
     *
     * RÈGLES ABSOLUES :
     * - uniquement en mode MULTI
     * - uniquement pendant l’Installer
     * - jamais après APP_INSTALLED=true
     */
    public function create(string $database): void
    {
        /*
        |--------------------------------------------------------------------------
        | Sécurité 1 : App déjà installée
        |--------------------------------------------------------------------------
        */
         // Double barrière : flag + lock FS
        if (config('app.installed', false) === true || InstallLock::isInstalled()) {
            throw new RuntimeException(
                'Création de base interdite après installation.'
            );
        }

        // Uniquement pendant l'installer (anti-abus)
        if (!InstallLock::isInstalling()) {
            throw new RuntimeException('Création de base autorisée uniquement pendant l’installation.');
        }

        /*
        |--------------------------------------------------------------------------
        | Sécurité 2 : Mode Instance
        |--------------------------------------------------------------------------
        */
        if (config('app.instance_mode') !== 'multi') {
            throw new RuntimeException(
                'DatabaseCreator utilisable uniquement en mode multi.'
            );
        }

        // Uniquement pendant l'installer (anti-abus)
        if (config('app.instance_db_strategy', 'shared') !== 'database-per-instance') {
            throw new RuntimeException('Création de base autorisée uniquement en stratégie database-per-instance.');
        }
        /*
        |--------------------------------------------------------------------------
        | Sécurité 3 : Nom de base valide
        |--------------------------------------------------------------------------
        */
         $database = trim($database);

        // MySQL: max 64 chars, charset sûr, pas d'espaces, pas de tirets
        if ($database === '' || strlen($database) > 64 || !preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            throw new RuntimeException('Nom de base invalide.');
        }
        /*
        |--------------------------------------------------------------------------
        | Création DB (via connexion centrale)
        |--------------------------------------------------------------------------
        */
        try {
            DB::connection('system')->statement(
                "CREATE DATABASE IF NOT EXISTS `{$database}`
                 CHARACTER SET utf8mb4
                 COLLATE utf8mb4_unicode_ci"
            );
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Impossible de créer la base de données. Droits insuffisants.'
            );
        }
    }
}

<?php

namespace App\Instances;

use Illuminate\Support\Facades\DB;
use RuntimeException;

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
        if (config('app.installed', false) === true) {
            throw new RuntimeException(
                'Création de base interdite après installation.'
            );
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

        /*
        |--------------------------------------------------------------------------
        | Sécurité 3 : Nom de base valide
        |--------------------------------------------------------------------------
        */
        if (
            empty($database) ||
            !preg_match('/^[a-zA-Z0-9_]+$/', $database)
        ) {
            throw new RuntimeException('Nom de base invalide.');
        }

        /*
        |--------------------------------------------------------------------------
        | Création DB (via connexion centrale)
        |--------------------------------------------------------------------------
        */
        try {
            DB::statement(
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

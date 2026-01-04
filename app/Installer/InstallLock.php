<?php

namespace App\Installer;

use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Système de verrouillage d'installation B360
 *
 * Implémente une double barrière physique via le système de fichiers :
 * 1. installing.lock : verrou d'exécution unique (anti-concurrence)
 * 2. installed.lock  : verrou définitif d'installation terminée
 *
 * Avantages par rapport aux solutions basées sur base de données :
 * - Indépendant de Laravel DB (fonctionne avant installation complète)
 * - Résilient aux problèmes de cache de configuration
 * - Audit simple via inspection directe des fichiers
 * - Résistant aux échecs de flag en base de données
 *
 * Sécurité : permissions 0640 (rw- r-- ---) pour limiter l'accès
 *
 * @final Empêche l'héritage pour garantir l'intégrité du système de lock
 */
final class InstallLock
{
    /**
     * Répertoire racine de stockage des locks
     * Stocké dans storage/app/b360/ pour isolation
     */
    public const DIR = 'b360';

    /**
     * Fichier de lock pour installation en cours
     * Contient : run_id, started_at (ISO 8601)
     */
    public const INSTALLING = 'installing.lock';

    /**
     * Fichier de lock pour installation terminée
     * Contient : run_id, installed_at (ISO 8601)
     */
    public const INSTALLED = 'installed.lock';

    /**
     * Retourne le chemin absolu du fichier installing.lock
     *
     * @return string Chemin complet dans le storage Laravel
     */
    public static function installingPath(): string
    {
        return storage_path('app/' . self::DIR . '/' . self::INSTALLING);
    }

    /**
     * Retourne le chemin absolu du fichier installed.lock
     *
     * @return string Chemin complet dans le storage Laravel
     */
    public static function installedPath(): string
    {
        return storage_path('app/' . self::DIR . '/' . self::INSTALLED);
    }

    /**
     * Vérifie si une installation est actuellement en cours d'exécution
     *
     * @return bool True si installing.lock existe et est lisible
     */
    public static function isInstalling(): bool
    {
        return File::exists(self::installingPath());
    }

    /**
     * Vérifie si l'application est marquée comme installée
     *
     * @return bool True si installed.lock existe et est lisible
     */
    public static function isInstalled(): bool
    {
        return File::exists(self::installedPath());
    }

    /**
     * Acquiert le verrou d'installation en cours
     *
     * Vérifications en cascade :
     * 1. Installation déjà finalisée → exception immédiate
     * 2. Installation déjà en cours → exception immédiate
     * 3. Création du verrou avec métadonnées d'exécution
     *
     * @param string $runId Identifiant unique UUID de l'exécution
     * @return void
     * @throws RuntimeException Si installation déjà finalisée ou en cours
     */
    public static function acquire(string $runId): void
    {
        // Garantit l'existence du répertoire parent (avec permissions par défaut)
        File::ensureDirectoryExists(dirname(self::installingPath()));

        // Barrière 1 : empêche toute nouvelle installation si déjà terminée
        if (self::isInstalled()) {
            throw new RuntimeException(
                'L\'application est déjà installée. Supprimez manuellement ' .
                self::installedPath() . ' pour forcer une réinstallation.'
            );
        }

        // Barrière 2 : empêche les exécutions concurrentes
        if (self::isInstalling()) {
            $lockContent = File::get(self::installingPath());
            $data = json_decode($lockContent, true);

            throw new RuntimeException(sprintf(
                'Une installation est déjà en cours (démarrée le %s). ' .
                'Si c\'est une erreur, supprimez manuellement %s.',
                $data['started_at'] ?? 'inconnu',
                self::installingPath()
            ));
        }

        // Création du verrou avec métadonnées structurées
        File::put(self::installingPath(), json_encode([
            'run_id' => $runId,
            'started_at' => now()->toIso8601String(),
            'pid' => getmypid() ?: null, // PID du processus pour diagnostic
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        // Sécurisation des permissions (propriétaire: rw, groupe: r, autres: aucun)
        @chmod(self::installingPath(), 0640);
    }

    /**
     * Libère le verrou d'installation en cours
     *
     * Utilisé dans deux contextes :
     * 1. Rollback après échec d'installation
     * 2. Après succès, avant création de installed.lock
     *
     * @return void
     */
    public static function releaseInstalling(): void
    {
        if (self::isInstalling()) {
            // Suppression silencieuse (ignore si fichier déjà supprimé)
            @File::delete(self::installingPath());
        }
    }

    /**
     * Marque définitivement l'installation comme terminée avec succès
     *
     * Séquence atomique :
     * 1. Crée installed.lock avec horodatage
     * 2. Sécurise les permissions
     * 3. Nettoie installing.lock (libère le verrou d'exécution)
     *
     * @param string $runId Identifiant unique UUID de l'exécution
     * @return void
     */
    public static function markInstalled(string $runId): void
    {
        // Double garantie de l'existence du répertoire
        File::ensureDirectoryExists(dirname(self::installedPath()));

        // Création du verrou définitif avec métadonnées complètes
        File::put(self::installedPath(), json_encode([
            'run_id' => $runId,
            'installed_at' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            'lock_schema' => '1.0', // Version du schéma pour forward compatibility
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        // Permissions restrictives pour sécurité
        @chmod(self::installedPath(), 0640);

        // Nettoyage du verrou temporaire (idempotent)
        self::releaseInstalling();
    }

    /**
     * Récupère les métadonnées du lock d'installation en cours
     *
     * @return array|null Données du lock ou null si inexistant/corrompu
     */
    public static function getInstallingMetadata(): ?array
    {
        if (!self::isInstalling()) {
            return null;
        }

        try {
            $content = File::get(self::installingPath());
            $data = json_decode($content, true);

            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            // En cas d'erreur de lecture/décodage, considère comme inexistant
            return null;
        }
    }

    /**
     * Force la suppression de tous les locks (opération de maintenance)
     *
     * ATTENTION : Méthode dangereuse, à utiliser uniquement pour :
     * - Réinitialisation après échec catastrophique
     * - Maintenance manuelle par administrateur
     * - Environnements de développement/test
     *
     * @return bool True si suppression réussie, false sinon
     */
    public static function forceCleanup(): bool
    {
        $success = true;

        if (self::isInstalling()) {
            $success = $success && @File::delete(self::installingPath());
        }

        if (self::isInstalled()) {
            $success = $success && @File::delete(self::installedPath());
        }

        // Nettoie également le répertoire parent s'il est vide
        $dir = dirname(self::installingPath());
        if (File::isDirectory($dir) && count(File::files($dir)) === 0) {
            @File::deleteDirectory($dir);
        }

        return $success;
    }
}

<?php

namespace App\Instances;

use App\Instances\Support\HostParser;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

/**
 * Résolution SAFE d'Instance :
 * - Ne casse jamais l'app en cas de DB down / migrations incomplètes.
 *  * - subdomain (default)
 * - domain (option)
 * - fallback: si domain configuré mais non utilisable => subdomain
 */
class InstanceResolver
{
    /**
     * Résout l'instance active de manière sécurisée avec gestion des erreurs
     * Cette méthode vérifie plusieurs préconditions avant de tenter la résolution
     * et retourne null en cas d'erreur ou de condition non remplie
     *
     * @param Request $request La requête HTTP courante
     * @return Instance|null L'instance trouvée ou null si non trouvée ou en cas d'erreur
     */
    public function resolveSafely(Request $request): ?Instance
    {
        // Vérifie que l'application est installée
        // Si l'application n'est pas installée, aucune instance ne peut être résolue
        if (config('app.installed', false) !== true) {
            return null;
        }

        // Vérifie que la connexion "system" est disponible
        // La connexion "system" est nécessaire pour accéder aux tables système
        try {
            DB::connection('system')->getPdo();
        } catch (\Throwable) {
            // En cas d'erreur de connexion, retourne null
            return null;
        }

        // Vérifie que la table "instances" existe dans la connexion "system"
        // Cela évite les erreurs si les migrations n'ont pas été exécutées
        try {
            if (!Schema::connection('system')->hasTable('instances')) {
                return null;
            }
        } catch (\Throwable) {
            // En cas d'erreur lors de la vérification de la table, retourne null
            return null;
        }

        // Récupère la méthode de résolution configurée
        // Par défaut, utilise la résolution par domaine
        $resolution = config('app.instance_resolution', 'path');
        if (!in_array($resolution, ['path', 'subdomain', 'domain', 'header'], true)) {
            $resolution = 'path';
        }
        $host = strtolower($request->getHost());
        try {
            // Résolution par domaine : compare l'hôte de la requête avec les domaines enregistrés
            // 1) PATH (default): /i/{slug}/...
            if ($resolution === 'path') {
                $slug = $this->extractSlugFromPath($request->path());
                if ($slug) {
                    return Instance::query()
                        ->where('is_active', true)
                        ->where('slug', $slug)
                        ->first();
                }
                // fallback safe
                $resolution = 'subdomain';
            }

            // 2) SUBDOMAIN: {slug}.example.com
            if ($resolution === 'subdomain') {
                $baseHost = HostParser::baseHost();
                $sub = HostParser::extractSubdomain($host, $baseHost);
                if ($sub && $sub !== 'www') {
                    $found = Instance::query()
                        ->where('is_active', true)
                        ->where('slug', $sub)
                        ->first();
                    if ($found) {
                        return $found;
                    }
                }
                // fallback safe
                $resolution = 'domain';
            }

            // 3) DOMAIN: mapping exact sur instances.domain
            if ($resolution === 'domain') {
                $found = Instance::query()
                    ->where('is_active', true)
                    ->where('domain', $host)
                    ->first();
                if ($found) {
                    return $found;
                }
                // fallback safe
                $resolution = 'header';
            }

            // 4) HEADER: X-Instance / X-Instance-Slug
            if ($resolution === 'header') {
                $raw = trim((string) ($request->header('X-Instance') ?: $request->header('X-Instance-Slug')));
                $slug = $this->normalizeSlug($raw);
                if ($slug) {
                    return Instance::query()
                        ->where('is_active', true)
                        ->where('slug', $slug)
                        ->first();
                }
            }

            return null;
        } catch (QueryException) {
            // En cas d'erreur de base de données (table non existante, etc.)
            // Retourne null au lieu de propager l'exception
            Log::debug('InstanceResolver DB error', ['host' => $host]);
            return null;
        }
    }
    
    private function extractSlugFromPath(string $path): ?string
    {
        // Format MVP: /i/{slug}/...
        // - Accepte "/i/slug" et "/i/slug/..."
        $path = '/' . ltrim($path, '/');
        if (!preg_match('#^/i/([a-z0-9][a-z0-9\-]{0,62})(?:/|$)#i', $path, $m)) {
            return null;
        }
        return $this->normalizeSlug($m[1]);
    }

    private function normalizeSlug(?string $value): ?string
    {
        $value = $value === null ? null : strtolower(trim($value));
        if ($value === '' || $value === null) {
            return null;
        }
        // Même règle que path: slug 1..63, alnum + tiret, commence par alnum
        if (!preg_match('/^[a-z0-9][a-z0-9\-]{0,62}$/', $value)) {
            return null;
        }
        return $value;
    }
}

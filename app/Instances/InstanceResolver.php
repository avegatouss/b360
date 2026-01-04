<?php

namespace App\Instances;

use App\Instances\Support\HostParser;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $resolution = config('app.instance_resolution', 'domain');

        $host = strtolower($request->getHost());
        try {
            // Résolution par domaine : compare l'hôte de la requête avec les domaines enregistrés
            // 1) domain (si configuré)
            if ($resolution === 'domain') {
                $found = Instance::query()
                    ->where('is_active', true) // Seulement les instances actives
                    ->where('domain', $host)  // Correspondance exacte du domaine
                    ->first();  // Prend la première correspondance

                if ($found) {
                    return $found;
                }

                // fallback obligatoire v1
                $resolution = 'subdomain';
            }

            // 2) subdomain (default + fallback)
            $baseHost = HostParser::baseHost();
            $sub = HostParser::extractSubdomain($host, $baseHost);

            if (!$sub) {
                return null;
            }

            // Fallback sécurisé : retourne la première instance active
            // Utilisé lorsque la résolution par domaine n'est pas configurée ou en cas d'autre méthode
            return Instance::query()
                ->where('is_active', true)  // Seulement les instances actives
                ->orderBy('id')             // Ordonne par ID pour une cohérence
                ->first();                  // Prend la première instance active
        } catch (QueryException) {
            // En cas d'erreur de base de données (table non existante, etc.)
            // Retourne null au lieu de propager l'exception
            return null;
        }
    }
}

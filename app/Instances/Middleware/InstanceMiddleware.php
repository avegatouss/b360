<?php

namespace App\Instances\Middleware;

use App\Instances\InstanceManager;
use App\Instances\InstanceResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstanceMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        /*
        |--------------------------------------------------------------------------
        | 1) Tant que l’app n’est pas installée : ne rien faire
        |--------------------------------------------------------------------------
        */
        if (config('app.installed', false) !== true) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | 2) Ne jamais exécuter l’Instance Engine sur l’installateur
        |--------------------------------------------------------------------------
        | (utile si l’installateur est encore accessible en dev)
        */
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | 3) Résolution  application (sécurisées)
        |--------------------------------------------------------------------------
        */
        $resolver = app(InstanceResolver::class);
        $manager  = app(InstanceManager::class);

        $instance = $resolver->resolveSafely($request);

        // Si aucune instance résolue :
        // - en mode "domain" strict : 404 (évite servir une mauvaise instance)
        // - sinon : on laisse passer (routes publiques / root / healthcheck etc.)
        if (!$instance) {
            if (config('app.instance_resolution', 'path') === 'domain') {
                abort(404);
            }
            return $next($request);
        }

        // Appliquer le contexte DB (shared/database-per-instance) - ne doit pas casser l'app
        try {
            $manager->apply($instance);
        } catch (\Throwable $e) {
            Log::warning('instance.context.failed', [
                'instance_id' => $instance->id,
                'instance_slug' => $instance->slug,
                'host' => $request->getHost(),
                'error' => get_class($e),
            ]);

            // En résolution strict "domain": si contexte échoue, on renvoie 503 neutre
            if (config('app.instance_resolution', 'path') === 'domain') {
                abort(503);
            }

            // Sinon on laisse passer (ne casse pas), sans publier de contexte
            return $next($request);
        }

     
        // Publier le contexte dans le container pour usage applicatif
        app()->instance('currentInstance', $instance);

        // Log minimal : éviter INFO sur chaque requête (bruit/perf)
        Log::debug('instance.resolved', [
            'instance_id' => $instance->id,
            'instance_slug' => $instance->slug,
        ]);

        return $next($request);
    }
}

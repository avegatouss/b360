<?php

namespace App\Instances\Middleware;

use App\Instances\InstanceManager;
use App\Instances\InstanceResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstanceMiddleware
{
    public function handle(Request $request, Closure $next)
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
        | 3) Résolution + application (sécurisées)
        |--------------------------------------------------------------------------
        */
        $resolver = app(InstanceResolver::class);
        $manager  = app(InstanceManager::class);

        $instance = $resolver->resolveSafely($request);

        // Si aucune instance résolue : on laisse passer (évite crash)
        if (!$instance) {
            // v1 domain : si pas d'instance => 404 (évite servir mauvaise instance)
            if (config('app.instance_resolution', 'domain') === 'domain') {
                abort(404, 'Instance not found');
            }
            return $next($request);
        }
        // Appliquer le contexte DB (shared/database-per-instance)

        $manager->apply($instance);
        // Publier le contexte dans le container pour usage applicatif
        app()->instance('currentInstance', $instance);
        // Log minimal (sans secret)
        Log::info('instance.resolved', [
            'instance_id' => $instance->id,
            'instance_slug' => $instance->slug,
            'host' => $request->getHost(),
        ]);

        return $next($request);
    }
}

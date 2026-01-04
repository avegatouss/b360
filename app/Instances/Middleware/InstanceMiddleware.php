<?php

namespace App\Instances\Middleware;

use App\Instances\InstanceManager;
use App\Instances\InstanceResolver;
use Closure;
use Illuminate\Http\Request;

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

        $instance = $resolver->resolveSafely();

        // Si aucune instance résolue : on laisse passer (évite crash)
        if (!$instance) {
            return $next($request);
        }

        $manager->apply($instance);

        app()->instance('currentInstance', $instance);

        return $next($request);
    }
}

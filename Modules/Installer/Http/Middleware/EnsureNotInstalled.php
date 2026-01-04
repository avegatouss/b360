<?php

namespace Modules\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotInstalled
{
    /**
     * Handle an incoming request.
     *
     * Ce middleware empêche tout accès à l’installateur
     * une fois l’application marquée comme installée.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Vérification stricte du flag APP_INSTALLED
        |--------------------------------------------------------------------------
        |
        | On utilise config() plutôt que env() afin de :
        | - respecter le cache de configuration
        | - éviter toute lecture directe du .env
        |
        */

        $installed = (bool) config('app.installed', false);

        if ($installed === true) {

            /*
            |--------------------------------------------------------------------------
            | Application déjà installée
            |--------------------------------------------------------------------------
            |
            | - On interdit l’accès à l’installateur
            | - On redirige vers la racine (ou login plus tard)
            | - Aucune exception volontairement levée
            |
            */

            return redirect('/');
        }

        return $next($request);
    }
}

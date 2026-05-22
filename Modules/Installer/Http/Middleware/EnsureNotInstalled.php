<?php

namespace Modules\Installer\Http\Middleware;

use App\Installer\InstallLock;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Empêche tout accès à l’installateur après installation.
 *
 * IMPORTANT :
 * - On ne se base pas uniquement sur APP_INSTALLED (flag) :
 *   un cache config ou une mauvaise manip pourrait le rendre incohérent.
 * - On ajoute installed.lock comme source de vérité "physique".
 */
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
        // Double barrière : flag config + lock file
        $installedFlag = (bool) config('app.installed', false);

  // Installer définitivement inaccessible
        if ($installedFlag === true || InstallLock::isInstalled()) {

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
            // abort(404) plutôt que redirect : en prod, l'installateur
            // ne doit pas révéler son existence. Un 404 neutre est préférable
            // à un redirect qui confirmerait que la route existe.
            abort(404);
        }

         // Si une installation est déjà en cours : refuser
           // Anti-concurrence : si une installation est déjà en cours
        // (On autorise /install/stream car c'est le flux qui exécute le runner)
        if (InstallLock::isInstalling() && !$request->is('install/stream')) {
            return response('Installation en cours. Veuillez patienter.', 409);
        }

        return $next($request);
    }
}

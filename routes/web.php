<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — B360 (routes de base)
|--------------------------------------------------------------------------
|
| Ce fichier ne contient que les routes racines de l'application.
| Toutes les routes fonctionnelles sont dans les modules :
|   - Modules/Installer/Routes/web.php  → /install/*
|   - Modules/Auth/Routes/web.php       → /login, /i/{slug}/login, etc.
|   - Modules/Dashboard/Routes/web.php  → /i/{slug}/
|   - Modules/Users/Routes/web.php      → /i/{slug}/users/*
|   - Modules/Core/Routes/web.php       → /_core/health
|
*/

// Racine : redirigée par RedirectRootAfterInstall middleware
// (vers /install si non installé, vers /login si installé)
Route::get('/', function () {
    // Ce handler ne sera atteint que si le middleware RedirectRootAfterInstall
    // n'a pas encore redirigé (cas extrême). On redirige vers /login par sécurité.
    return redirect('/login');
})->name('home');

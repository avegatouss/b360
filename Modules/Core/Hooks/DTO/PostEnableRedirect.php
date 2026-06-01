<?php

declare(strict_types=1);

namespace Modules\Core\Hooks\DTO;

use Closure;

/**
 * R-401-FIX S1 / ADR-022 — Redirect post-activation d'un module.
 *
 * Permet à un module de déclarer où rediriger l'utilisateur lorsque le
 * ModuleController active ce module pour la première fois (wizard de
 * setup, page de configuration initiale, etc.).
 *
 * Remplace le couplage en dur `if ($name === 'Eshop360') redirect(...)`
 * dans ModuleController::toggle (cf. R-401).
 *
 * Si `$condition` est non null, elle est invoquée avec l'instance courante
 * et doit retourner true pour que le redirect soit actif. Permet par
 * exemple de ne rediriger que si le module n'est pas encore initialisé.
 */
final class PostEnableRedirect
{
    /**
     * @param  Closure(\App\Instances\Instance): bool|null  $condition  fn($instance): bool — true = redirect actif
     */
    public function __construct(
        public readonly string $moduleName,
        public readonly string $route,
        public readonly ?Closure $condition = null,
        public readonly int $priority = 0,
    ) {}
}

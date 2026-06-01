<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Concerns;

use Illuminate\Support\Facades\Schema;

/**
 * R-403 — Helper de skip conditionnel pour les tests qui dépendent
 * des migrations Eshop360 (tables `eshop_*`).
 *
 * À utiliser dans `setUp()` après `parent::setUp()`, ou au début d'une
 * méthode de test individuelle :
 *
 * ```php
 * protected function setUp(): void
 * {
 *     parent::setUp();
 *     $this->requireEshop360Schema();
 *     // …
 * }
 * ```
 *
 * Pourquoi : quand Eshop360 est désactivé via `modules_statuses.json`, son
 * `ServiceProvider` ne charge pas ses migrations, donc les tables `eshop_*`
 * sont absentes de la SQLite `:memory:` de test. Les tests qui touchent
 * ces tables échouent avec un `QueryException` opaque. Ce skip transforme
 * l'erreur en signal explicite (R-403) — voir `docs/memory/OPEN_RISKS.md`.
 *
 * Le trait vit dans Core (modules socle) car plusieurs modules le consomment
 * (Menuiserie360, Currency, etc.). Ne crée pas de dépendance code Core →
 * Eshop360 : le check est purement runtime (`Schema::hasTable(...)`), pas
 * un import de classe.
 */
trait RequiresEshop360Schema
{
    /**
     * Marque le test comme skipped si les tables Eshop360 ne sont pas
     * chargées dans la base de test courante.
     */
    protected function requireEshop360Schema(): void
    {
        if (! Schema::hasTable('eshop_customers')) {
            $this->markTestSkipped(
                'R-403 : Eshop360 désactivé — table `eshop_customers` absente. '
                .'Réactiver Eshop360 dans modules_statuses.json pour exécuter ce test.'
            );
        }
    }
}

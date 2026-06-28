<?php

declare(strict_types=1);

namespace Modules\Eshop360\Tests\Feature\Referentiel;

use Illuminate\Database\DatabaseTransactionsManager;

/**
 * Manager de transactions de TEST uniquement.
 *
 * Sous `RefreshDatabase`, le test entier tourne dans une transaction de niveau 1
 * jamais committée (rollback en fin de test). Avec le manager standard, les
 * callbacks `DB::afterCommit` ne s'exécutent qu'au commit vers le niveau 0 — ils
 * ne se déclenchent donc JAMAIS pendant un test RefreshDatabase.
 *
 * Pour valider la mécanique réelle des observers (push best-effort post-commit),
 * on force l'exécution des callbacks au commit vers le niveau 1 (le niveau de
 * base ouvert par RefreshDatabase) : une transaction métier imbriquée
 * (`DB::transaction` du contrôleur store/update) qui se committe déclenche alors
 * les afterCommit, comme en production où elle committe vers le niveau 0.
 */
final class ImmediateAfterCommitTransactionsManager extends DatabaseTransactionsManager
{
    public function afterCommitCallbacksShouldBeExecuted($level): bool
    {
        return $level <= 1;
    }
}

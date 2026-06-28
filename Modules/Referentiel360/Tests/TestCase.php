<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests;

use Modules\Billing\Tests\TestCase as BillingTestCase;

/**
 * Base TestCase Referentiel360.
 *
 * Hérite de Billing\Tests\TestCase (qui hérite de Core) pour bénéficier de
 * `makeRootInstance()` / `makeUser()` et du setup multi-tenant SQLite standard,
 * sans introduire de dépendance applicative vers Billing (héritage de test
 * uniquement — choix documenté, identique à Menuiserie360).
 */
abstract class TestCase extends BillingTestCase {}

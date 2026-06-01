<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests;

use Modules\Billing\Tests\TestCase as BillingTestCase;

/**
 * Base TestCase Menuiserie360. Hérite de Billing (qui hérite de Core)
 * pour bénéficier de `makeRootInstance()`, `makeRootSuperAdmin()` et
 * du setup multi-tenant standard.
 *
 * Ne définit aucun setUp/tearDown spécifique en P0 — les tests Menuiserie360
 * doivent être autonomes côté cache (cf. `feedback_test_environment_isolation`
 * dans la mémoire IA — `Cache::flush()` recommandé en setUp pour éviter la
 * pollution `array` entre tests dans un worker parallel paratest).
 */
abstract class TestCase extends BillingTestCase {}

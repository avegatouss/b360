<?php

namespace Modules\Eshop360\Tests;

use Modules\Billing\Tests\TestCase as BillingTestCase;
use Modules\Core\Support\CurrentInstance;

abstract class TestCase extends BillingTestCase
{
    protected function tearDown(): void
    {
        CurrentInstance::clear();

        parent::tearDown();
    }
}

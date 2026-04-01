<?php

namespace Modules\Eshop360\Tests;

use App\Instances\Instance;
use App\Models\User;
use Modules\Billing\Tests\TestCase as BillingTestCase;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Support\CurrentChannel;

abstract class TestCase extends BillingTestCase
{
    /**
     * Set up instance + authenticated super-admin in one call.
     *
     * Most Eshop360 models use BelongsToChannel whose ChannelScope
     * fails closed when no user is authenticated.  Call this helper
     * at the top of any test that creates or queries such models.
     *
     * @return array{0: Instance, 1: User}
     */
    protected function setUpInstanceWithAdmin(): array
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        $this->actingAs($user);

        return [$instance, $user];
    }

    protected function tearDown(): void
    {
        CurrentChannel::flush();
        CurrentInstance::clear();

        parent::tearDown();
    }
}

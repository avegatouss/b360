<?php

namespace Modules\Eshop360\Tests;

use App\Instances\Instance;
use App\Models\User;
use Modules\Billing\Tests\TestCase as BillingTestCase;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Support\CurrentChannel;
use Spatie\Permission\Models\Role;

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

    /**
     * Create a user with instance-admin role so BelongsToChannel
     * considers them a hub admin (no RuntimeException on model creation).
     */
    protected function makeUser(string $email = 'admin@test.com'): User
    {
        $user = parent::makeUser($email);

        TeamContext::clear();
        Role::findOrCreate('instance-admin');
        $user->assignRole('instance-admin');

        return $user;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Reset static state that may leak from previous test classes
        CurrentChannel::flush();
        CurrentInstance::clear();
    }

    protected function tearDown(): void
    {
        CurrentChannel::flush();
        CurrentInstance::clear();

        parent::tearDown();
    }
}

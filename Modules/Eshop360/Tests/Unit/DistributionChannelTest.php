<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Channel\Models\ChannelUser;
use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;
use Modules\Eshop360\Tests\TestCase;

final class DistributionChannelTest extends TestCase
{
    public function test_calculate_sale_price(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Grossiste',
            'slug' => 'grossiste',
            'code' => 'GR-01',
            'is_active' => true,
            'margin_rate' => 0.13,
            'buy_rate' => 0.25,
            'debt_share' => 0.30,
            'channel_share' => 0.30,
            'owner_share' => 0.40,
        ]);

        // calculateSalePrice(pght) = pght * (1 + buy_rate) = 1000 * 1.25 = 1250
        $salePrice = $channel->calculateSalePrice(1000);

        $this->assertSame(1250.0, $salePrice);
    }

    public function test_calculate_sale_price_with_zero_rate(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Direct',
            'slug' => 'direct',
            'code' => 'DR-01',
            'is_active' => true,
            'margin_rate' => 0,
            'buy_rate' => 0,
            'debt_share' => 0.33,
            'channel_share' => 0.34,
            'owner_share' => 0.33,
        ]);

        // buy_rate = 0 => sale_price = pght * 1.0 = pght
        $salePrice = $channel->calculateSalePrice(500);
        $this->assertSame(500.0, $salePrice);
    }

    public function test_is_user_member(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Members Channel',
            'slug' => 'members-channel',
            'code' => 'MC-01',
            'is_active' => true,
            'margin_rate' => 0.10,
            'buy_rate' => 0.15,
            'debt_share' => 0.33,
            'channel_share' => 0.34,
            'owner_share' => 0.33,
        ]);

        // Not a member yet
        $this->assertFalse($channel->isUserMember($user->id));

        // Add as member
        ChannelUser::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        $this->assertTrue($channel->isUserMember($user->id));
    }

    public function test_managers_returns_only_manager_role(): void
    {
        $instance = $this->makeRootInstance();
        $admin = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Team Channel',
            'slug' => 'team-channel',
            'code' => 'TC-01',
            'is_active' => true,
            'margin_rate' => 0.10,
            'buy_rate' => 0.15,
            'debt_share' => 0.33,
            'channel_share' => 0.34,
            'owner_share' => 0.33,
        ]);

        // Create a second user as manager
        $manager = \App\Models\User::create([
            'full_name' => 'Manager User',
            'email' => 'manager@test.com',
            'password' => 'password',
        ]);

        // admin = member role, manager = manager role
        $channel->users()->attach($admin->id, ['role' => 'member']);
        $channel->users()->attach($manager->id, ['role' => 'manager']);

        $managers = $channel->managers;

        $this->assertCount(1, $managers);
        $this->assertSame($manager->id, $managers->first()->id);
    }
}

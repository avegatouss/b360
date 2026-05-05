<?php

namespace Modules\Eshop360\Tests\Unit;

use App\Instances\Instance;
use App\Models\User;
use Modules\Eshop360\Models\CashRegister;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Services\CashRegisterService;
use Modules\Eshop360\Tests\TestCase;

final class CashRegisterServiceTest extends TestCase
{
    private CashRegisterService $service;

    private Instance $instance;

    private User $user;

    private DistributionChannel $channelA;

    private DistributionChannel $channelB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CashRegisterService;

        [$this->instance, $this->user] = $this->setUpInstanceWithAdmin();

        $this->channelA = DistributionChannel::create([
            'instance_id' => $this->instance->id,
            'name' => 'Channel A',
            'slug' => 'channel-a',
            'is_active' => true,
        ]);

        $this->channelB = DistributionChannel::create([
            'instance_id' => $this->instance->id,
            'name' => 'Channel B',
            'slug' => 'channel-b',
            'is_active' => true,
        ]);
    }

    /**
     * Reproduces the "double cash register" bug:
     * 1. Open a global register (channel_id=null) for user X
     * 2. Open a channel-scoped register for the same user
     * 3. Both registers stay open simultaneously because the close query
     *    in open() filters by channel_id and does not match the global register
     */
    public function test_open_closes_any_previously_open_register_cross_channel(): void
    {
        // Step 1: open a global register (no channel)
        $first = $this->service->open(
            instanceId: $this->instance->id,
            openingAmount: 100.00,
            channelId: null,
        );

        $this->assertSame('open', $first->status);

        // Step 2: open a channel-scoped register for the SAME user
        $second = $this->service->open(
            instanceId: $this->instance->id,
            openingAmount: 200.00,
            channelId: $this->channelA->id,
        );

        $this->assertSame('open', $second->status);

        // Step 3: only ONE register should be open for this user across ALL channels
        $openCount = CashRegister::query()
            ->where('user_id', $this->user->id)
            ->where('status', 'open')
            ->count();

        $this->assertSame(
            1,
            $openCount,
            'Only the most recently opened cash register should remain open for a given user; '
            .'the previous open register (any channel scope) should be auto-closed.'
        );

        // The remaining open register should be the second one
        $this->assertTrue(
            CashRegister::where('id', $second->id)->where('status', 'open')->exists(),
            'The most recently opened register should still be open.'
        );

        // The first register should now be closed
        $first->refresh();
        $this->assertSame('closed', $first->status);
        $this->assertNotNull($first->closed_at);
    }

    /**
     * Reverse scenario: open channel-scoped first, then global.
     */
    public function test_open_global_closes_previously_open_channel_register(): void
    {
        $channelReg = $this->service->open(
            instanceId: $this->instance->id,
            openingAmount: 100.00,
            channelId: $this->channelA->id,
        );

        $globalReg = $this->service->open(
            instanceId: $this->instance->id,
            openingAmount: 200.00,
            channelId: null,
        );

        $openCount = CashRegister::query()
            ->where('user_id', $this->user->id)
            ->where('status', 'open')
            ->count();

        $this->assertSame(1, $openCount);

        $channelReg->refresh();
        $this->assertSame('closed', $channelReg->status);
    }

    /**
     * Two different channels for the same user — only the most recent open register
     * should remain.
     */
    public function test_open_closes_previously_open_register_on_a_different_channel(): void
    {
        $regA = $this->service->open(
            instanceId: $this->instance->id,
            openingAmount: 50.00,
            channelId: $this->channelA->id,
        );

        $regB = $this->service->open(
            instanceId: $this->instance->id,
            openingAmount: 80.00,
            channelId: $this->channelB->id,
        );

        $openCount = CashRegister::query()
            ->where('user_id', $this->user->id)
            ->where('status', 'open')
            ->count();

        $this->assertSame(1, $openCount);

        $regA->refresh();
        $this->assertSame('closed', $regA->status);
    }
}

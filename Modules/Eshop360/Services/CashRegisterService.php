<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Domain\Sales\Models\CashRegister;
use Modules\Eshop360\Domain\Sales\Models\Order;

class CashRegisterService
{
    /**
     * Open a cash register.
     *
     * Atomically closes any previously open register for the current user
     * (regardless of channel scope) before creating a new one. The whole
     * operation is wrapped in a transaction with `lockForUpdate()` to
     * prevent two concurrent calls from leaving multiple open registers.
     *
     * @see CashRegisterServiceTest for the regression suite
     */
    public function open(int $instanceId, float $openingAmount, ?int $storeId = null, ?int $channelId = null): CashRegister
    {
        return DB::transaction(function () use ($instanceId, $openingAmount, $storeId, $channelId) {
            // Lock and close ANY previously open register for this user across ALL channels.
            // We do not filter by channel_id: a single user can never legitimately
            // hold two open registers at once (one global + one channel-scoped, or
            // two different channels), so the new open() always supersedes the
            // previous one regardless of scope.
            CashRegister::query()
                ->where('user_id', auth()->id())
                ->where('status', 'open')
                ->lockForUpdate()
                ->update(['status' => 'closed', 'closed_at' => now()]);

            return CashRegister::create([
                'instance_id' => $instanceId,
                'channel_id' => $channelId,
                'store_id' => $storeId,
                'user_id' => auth()->id(),
                'opening_amount' => $openingAmount,
                'status' => 'open',
                'opened_at' => now(),
            ]);
        });
    }

    /**
     * Close cash register with reconciliation
     */
    public function close(CashRegister $register, float $closingAmount): CashRegister
    {
        $expectedAmount = $this->expectedAmount($register);
        $difference = $closingAmount - $expectedAmount;

        $register->update([
            'closing_amount' => $closingAmount,
            'expected_amount' => $expectedAmount,
            'difference' => $difference,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return $register->fresh();
    }

    /**
     * Get the current open register for the authenticated user
     */
    public function getCurrentRegister(?int $channelId = null): ?CashRegister
    {
        $query = CashRegister::where('user_id', auth()->id())
            ->where('status', 'open')
            ->with('store')
            ->latest('opened_at');

        if ($channelId) {
            $query->where('channel_id', $channelId);
        } else {
            $query->whereNull('channel_id');
        }

        return $query->first();
    }

    public function expectedAmount(CashRegister $register): float
    {
        $salesTotal = Order::where('cash_register_id', $register->id)
            ->where('status', '!=', 'cancelled')
            ->where('payment_method', 'cash')
            ->sum('paid_amount');

        return round((float) $register->opening_amount + (float) $salesTotal, 2);
    }
}

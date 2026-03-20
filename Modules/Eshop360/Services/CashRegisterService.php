<?php

namespace Modules\Eshop360\Services;

use Modules\Eshop360\Models\CashRegister;
use Modules\Eshop360\Models\Order;

class CashRegisterService
{
    /**
     * Open a cash register
     */
    public function open(int $instanceId, float $openingAmount, ?int $storeId = null, ?int $channelId = null): CashRegister
    {
        // Close any previously open register for this user (same channel scope)
        $query = CashRegister::where('user_id', auth()->id())->where('status', 'open');
        if ($channelId) {
            $query->where('channel_id', $channelId);
        }
        $query->update(['status' => 'closed', 'closed_at' => now()]);

        return CashRegister::create([
            'instance_id' => $instanceId,
            'channel_id' => $channelId,
            'store_id' => $storeId,
            'user_id' => auth()->id(),
            'opening_amount' => $openingAmount,
            'status' => 'open',
            'opened_at' => now(),
        ]);
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

<?php

namespace Modules\Eshop360\Services;

use Carbon\Carbon;
use Modules\Eshop360\Domain\Finance\Models\ChargeLog;
use Modules\Eshop360\Domain\Finance\Models\CompanyCharge;
use Modules\Eshop360\Support\CurrentChannel;

class ChargesService
{
    const SECONDS_PER_MONTH = 30 * 24 * 3600; // 2,592,000

    /**
     * Get cost per second for all active charges
     */
    public function getTotalCostPerSecond(int $instanceId): float
    {
        return CompanyCharge::where('instance_id', $instanceId)
            ->where('is_active', true)
            ->when(CurrentChannel::isScoped(), fn ($q) => $q->where('channel_id', CurrentChannel::id()))
            ->get()
            ->sum(fn ($charge) => $charge->amount_monthly / self::SECONDS_PER_MONTH);
    }

    /**
     * Get charges accumulated since start of current month
     */
    public function getAccumulatedCharges(int $instanceId): float
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $secondsElapsed = Carbon::now()->diffInSeconds($startOfMonth);
        $costPerSecond = $this->getTotalCostPerSecond($instanceId);

        return round($costPerSecond * $secondsElapsed, 2);
    }

    /**
     * Get detailed breakdown by category
     */
    public function getBreakdownByCategory(int $instanceId): array
    {
        $charges = CompanyCharge::where('instance_id', $instanceId)
            ->where('is_active', true)
            ->when(CurrentChannel::isScoped(), fn ($q) => $q->where('channel_id', CurrentChannel::id()))
            ->get()
            ->groupBy('category');

        $startOfMonth = Carbon::now()->startOfMonth();
        $secondsElapsed = Carbon::now()->diffInSeconds($startOfMonth);
        $breakdown = [];

        foreach ($charges as $category => $items) {
            $monthlyTotal = $items->sum('amount_monthly');
            $costPerSecond = $monthlyTotal / self::SECONDS_PER_MONTH;
            $breakdown[$category] = [
                'monthly_total' => round($monthlyTotal, 2),
                'cost_per_second' => $costPerSecond,
                'accumulated' => round($costPerSecond * $secondsElapsed, 2),
                'items' => $items->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'amount_monthly' => $c->amount_monthly,
                ])->values()->toArray(),
            ];
        }

        return $breakdown;
    }

    /**
     * Get data for dashboard real-time counter (JSON for JavaScript)
     */
    public function getDashboardData(int $instanceId): array
    {
        return [
            'cost_per_second' => $this->getTotalCostPerSecond($instanceId),
            'accumulated_since_month_start' => $this->getAccumulatedCharges($instanceId),
            'month_start' => Carbon::now()->startOfMonth()->toIso8601String(),
            'breakdown' => $this->getBreakdownByCategory($instanceId),
        ];
    }

    /**
     * Log charge computation for audit
     */
    public function logComputation(int $instanceId): void
    {
        $charges = CompanyCharge::where('instance_id', $instanceId)
            ->where('is_active', true)
            ->when(CurrentChannel::isScoped(), fn ($q) => $q->where('channel_id', CurrentChannel::id()))
            ->get();

        foreach ($charges as $charge) {
            ChargeLog::create([
                'charge_id' => $charge->id,
                'amount_per_second' => $charge->amount_monthly / self::SECONDS_PER_MONTH,
                'period_start' => Carbon::now()->startOfMonth(),
                'period_end' => Carbon::now(),
                'total_accumulated' => ($charge->amount_monthly / self::SECONDS_PER_MONTH) * Carbon::now()->diffInSeconds(Carbon::now()->startOfMonth()),
            ]);
        }
    }
}

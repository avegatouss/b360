<?php

namespace Modules\Billing\Services;

use Carbon\Carbon;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;

final class SubscriptionManager
{
    public function current(int $instanceId): ?Subscription
    {
        return Subscription::where('instance_id', $instanceId)
            ->whereIn('status', ['trial', 'active'])
            ->latest('id')
            ->first();
    }

    public function subscribe(int $instanceId, int $planId, ?int $trialDays = null): Subscription
    {
        $plan = Plan::findOrFail($planId);

        // Determine trial days: explicit param → instance setting override → plan default
        if ($trialDays === null) {
            $override = function_exists('setting')
                ? setting('billing.trial_days_override', null, $instanceId)
                : null;
            $trialDays = $override !== null ? (int) $override : $plan->trial_days;
        }

        $now = now();
        $status = $trialDays > 0 ? 'trial' : 'active';
        $trialEndsAt = $trialDays > 0 ? $now->copy()->addDays($trialDays) : null;

        return Subscription::create([
            'instance_id' => $instanceId,
            'plan_id' => $planId,
            'status' => $status,
            'trial_ends_at' => $trialEndsAt,
            'starts_at' => $now,
            'ends_at' => null,
        ]);
    }

    public function cancel(Subscription $sub, ?string $reason = null): Subscription
    {
        $sub->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $sub->refresh();
    }

    public function renew(Subscription $sub, ?Carbon $endsAt = null): Subscription
    {
        $sub->update([
            'status' => 'active',
            'ends_at' => $endsAt ?? now()->addMonth(),
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);

        return $sub->refresh();
    }

    public function changePlan(Subscription $sub, int $newPlanId): Subscription
    {
        Plan::findOrFail($newPlanId);

        $sub->update(['plan_id' => $newPlanId]);

        return $sub->refresh();
    }

    public function isActive(int $instanceId): bool
    {
        $sub = $this->current($instanceId);

        if (!$sub) {
            return false;
        }

        return $sub->isActive();
    }

    public function isTrialing(int $instanceId): bool
    {
        $sub = $this->current($instanceId);

        if (!$sub) {
            return false;
        }

        return $sub->isTrialing();
    }

    public function daysLeftInTrial(int $instanceId): int
    {
        $sub = $this->current($instanceId);

        if (!$sub || $sub->status !== 'trial' || !$sub->trial_ends_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($sub->trial_ends_at, false));
    }

    public function expireOverdue(): int
    {
        $count = 0;

        // Expire trials that have passed their trial_ends_at
        $count += Subscription::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->update(['status' => 'expired']);

        // Expire active subscriptions past their ends_at
        $count += Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->update(['status' => 'expired']);

        return $count;
    }
}

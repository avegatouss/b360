<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Services\FeatureRegistry;
use Modules\Billing\Services\GatewayManager;
use Modules\Billing\Services\PlanManager;
use Modules\Billing\Services\SubscriptionManager;
use Modules\Core\Support\CurrentInstance;

/**
 * Upgrade flow — shown when a user accesses a paid feature not in their plan.
 */
final class UpgradeController extends Controller
{
    public function __construct(
        private readonly FeatureRegistry $featureRegistry,
        private readonly PlanManager $planManager,
        private readonly SubscriptionManager $subscriptionManager,
        private readonly GatewayManager $gatewayManager,
    ) {}

    public function show(Request $request, string $slug)
    {
        $instance = CurrentInstance::get();
        $featureId = $request->query('feature');

        $feature = $featureId
            ? $this->featureRegistry->all()->firstWhere('id', $featureId)
            : null;

        $currentSub = $this->subscriptionManager->current($instance->id);
        $currentPlan = $currentSub?->plan;

        $recommendedPlans = $featureId
            ? $this->featureRegistry->plansIncluding($featureId, $instance->id)
            : collect();

        $allPlans = $this->planManager->forInstance($instance->id);
        $enabledGateways = $this->gatewayManager->enabledFor($instance->id);

        // Group features by category for display
        $featuresByCategory = $this->featureRegistry->byCategory();

        return view('billing::upgrade.show', compact(
            'instance',
            'feature',
            'currentPlan',
            'recommendedPlans',
            'allPlans',
            'enabledGateways',
            'featuresByCategory',
        ));
    }
}

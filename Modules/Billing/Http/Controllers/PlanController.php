<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use App\Instances\Instance;
use Modules\Billing\Models\Plan;
use Modules\Billing\Services\PlanManager;
use Modules\Core\Support\CurrentInstance;

final class PlanController extends Controller
{
    public function __construct(
        private readonly PlanManager $planManager
    ) {}

    public function index(string $slug): View
    {
        $instance = CurrentInstance::get();
        $plans = $this->planManager->all(activeOnly: false)->load('instances');

        return view('billing::plans.index', compact('instance', 'plans'));
    }

    public function create(string $slug): View
    {
        $instance = CurrentInstance::get();
        $allInstances = Instance::on('system')->where('slug', '!=', 'root')->get();

        return view('billing::plans.form', ['instance' => $instance, 'plan' => null, 'allInstances' => $allInstances]);
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:system.plans,slug',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'trial_days' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'visibility' => 'required|in:all,specific',
            'instance_ids' => 'array',
            'instance_ids.*' => 'integer|exists:system.instances,id',
        ]);

        $instanceIds = $validated['instance_ids'] ?? [];
        unset($validated['instance_ids']);

        $plan = $this->planManager->create($validated);

        if ($validated['visibility'] === 'specific') {
            $plan->instances()->sync($instanceIds);
        }

        return redirect()->route('billing.plans.index', $instance->slug)
            ->with('success', 'Plan cree avec succes.');
    }

    public function show(string $slug, string $plan): View
    {
        $instance = CurrentInstance::get();
        $plan = $this->planManager->find((int) $plan);
        abort_unless($plan, 404);
        $allInstances = Instance::on('system')->where('slug', '!=', 'root')->get();

        return view('billing::plans.form', compact('instance', 'plan', 'allInstances'));
    }

    public function update(Request $request, string $slug, string $id): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $plan = $this->planManager->find((int) $id);
        abort_unless($plan, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'trial_days' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'visibility' => 'required|in:all,specific',
            'instance_ids' => 'array',
            'instance_ids.*' => 'integer|exists:system.instances,id',
        ]);

        $instanceIds = $validated['instance_ids'] ?? [];
        unset($validated['instance_ids']);

        $this->planManager->update($plan, $validated);

        if ($validated['visibility'] === 'specific') {
            $plan->instances()->sync($instanceIds);
        } else {
            $plan->instances()->detach();
        }

        return redirect()->route('billing.plans.index', $instance->slug)
            ->with('success', 'Plan mis a jour.');
    }

    public function destroy(string $slug, string $id): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $plan = $this->planManager->find((int) $id);
        abort_unless($plan, 404);

        $this->planManager->delete($plan);

        return redirect()->route('billing.plans.index', $instance->slug)
            ->with('success', 'Plan supprime.');
    }
}

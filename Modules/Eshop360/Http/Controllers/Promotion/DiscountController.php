<?php

namespace Modules\Eshop360\Http\Controllers\Promotion;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Eshop360\Models\Discount;
use Modules\Eshop360\Models\DiscountPlan;

class DiscountController extends Controller
{
    public function index(Request $request)
    {
        $discounts = Discount::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->discount_type, fn ($q, $t) => $q->where('discount_type', $t))
            ->when($request->applies_to, fn ($q, $a) => $q->where('applies_to', $a))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('eshop360::promotions.discounts', compact('discounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:500',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'applies_to'     => 'required|in:all,category,brand,product',
            'target_ids'     => 'nullable|array',
            'target_ids.*'   => 'integer',
            'min_quantity'   => 'nullable|integer|min:1',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'is_active'      => 'boolean',
        ]);

        $validated['instance_id'] = $request->route('instance_id') ?? session('instance_id');

        Discount::create($validated);

        return redirect()->route('eshop360.discounts.index')
            ->with('success', __('Discount created successfully.'));
    }

    public function update(Request $request, Discount $discount): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:500',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'applies_to'     => 'required|in:all,category,brand,product',
            'target_ids'     => 'nullable|array',
            'target_ids.*'   => 'integer',
            'min_quantity'   => 'nullable|integer|min:1',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'is_active'      => 'boolean',
        ]);

        $discount->update($validated);

        return redirect()->route('eshop360.discounts.index')
            ->with('success', __('Discount updated successfully.'));
    }

    public function destroy(Discount $discount): RedirectResponse
    {
        $discount->delete();

        return redirect()->route('eshop360.discounts.index')
            ->with('success', __('Discount deleted successfully.'));
    }

    public function plans(Request $request)
    {
        $plans = DiscountPlan::with('discounts')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('eshop360::promotions.discount-plans', compact('plans'));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:500',
            'discount_ids'  => 'required|array|min:1',
            'discount_ids.*' => 'exists:eshop_discounts,id',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'is_active'     => 'boolean',
        ]);

        $validated['instance_id'] = $request->route('instance_id') ?? session('instance_id');
        $discountIds = $validated['discount_ids'];
        unset($validated['discount_ids']);

        $plan = DiscountPlan::create($validated);
        $plan->discounts()->sync($discountIds);

        return redirect()->route('eshop360.discounts.plans')
            ->with('success', __('Discount plan created successfully.'));
    }

    public function updatePlan(Request $request, DiscountPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:500',
            'discount_ids'  => 'required|array|min:1',
            'discount_ids.*' => 'exists:eshop_discounts,id',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'is_active'     => 'boolean',
        ]);

        $discountIds = $validated['discount_ids'];
        unset($validated['discount_ids']);

        $plan->update($validated);
        $plan->discounts()->sync($discountIds);

        return redirect()->route('eshop360.discounts.plans')
            ->with('success', __('Discount plan updated successfully.'));
    }

    public function destroyPlan(DiscountPlan $plan): RedirectResponse
    {
        $plan->discounts()->detach();
        $plan->delete();

        return redirect()->route('eshop360.discounts.plans')
            ->with('success', __('Discount plan deleted successfully.'));
    }
}

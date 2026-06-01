<?php

namespace Modules\Eshop360\Http\Controllers\Promotion;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Promotions\Models\Coupon;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $instanceId = CurrentInstance::idOrFail();
        $channelId = $request->integer('channel_id') ?: null;
        $query = Coupon::where('instance_id', $instanceId)
            ->when($channelId, fn ($q, $c) => $q->where('channel_id', $c))
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('code', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%");
            }))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->expired === '1', fn ($q) => $q->whereNotNull('valid_until')->where('valid_until', '<', now()))
            ->when($request->expired === '0', fn ($q) => $q->where(fn ($sq) => $sq->whereNull('valid_until')->orWhere('valid_until', '>=', now())));

        // KPIs
        $fq = clone $query;
        $kpi = (object) [
            'total' => (clone $fq)->count(),
            'active' => (clone $fq)->where('is_active', true)->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()))->count(),
            'expired' => (clone $fq)->whereNotNull('valid_until')->where('valid_until', '<', now())->count(),
            'total_used' => (int) (clone $fq)->sum('used_count'),
            'percentage' => (clone $fq)->where('type', 'percentage')->count(),
            'fixed' => (clone $fq)->where('type', 'fixed')->count(),
        ];

        $coupons = $query->latest()->paginate(20)->withQueryString();

        return view('eshop360::promotions.coupons', compact('coupons', 'kpi'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:eshop_coupons,code',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'usage_limit' => 'nullable|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        $instance = CurrentInstance::get();
        $validated['instance_id'] = $instance->id;
        $validated['code'] = strtoupper($validated['code']);
        $validated['name'] = $validated['name'] ?? $validated['code'];

        Coupon::create($validated);

        return redirect()->route('eshop360.coupons.index', $instance->slug)
            ->with('success', __('Coupon cree avec succes.'));
    }

    public function update(Request $request, string $slug, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:eshop_coupons,code,'.$coupon->id,
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'usage_limit' => 'nullable|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $coupon->update($validated);

        return redirect()->route('eshop360.coupons.index', $slug)
            ->with('success', __('Coupon mis a jour.'));
    }

    public function destroy(string $slug, Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('eshop360.coupons.index', $slug)
            ->with('success', __('Coupon supprime.'));
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'order_amount' => 'nullable|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))
            ->where('instance_id', CurrentInstance::idOrFail())
            ->where('is_active', true)
            ->visibleToChannel($request->integer('channel_id') ?: null)
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()))
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()))
            ->first();

        if (! $coupon) {
            return response()->json(['valid' => false, 'message' => __('Code coupon invalide ou expire.')], 422);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['valid' => false, 'message' => __('Limite d\'utilisation atteinte.')], 422);
        }

        $orderAmount = (float) ($request->order_amount ?? 0);

        $discount = $coupon->type === 'percentage'
            ? round($orderAmount * ($coupon->value / 100), 2)
            : min($coupon->value, $orderAmount);

        return response()->json([
            'valid' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ],
            'discount_amount' => $discount,
            'message' => __('Coupon valide.'),
        ]);
    }
}

<?php

namespace Modules\Eshop360\Http\Controllers\Promotion;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Eshop360\Models\Coupon;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::query()
            ->when($request->search, fn ($q, $s) => $q->where('code', 'like', "%{$s}%")
                ->orWhere('description', 'like', "%{$s}%"))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->discount_type, fn ($q, $t) => $q->where('discount_type', $t))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('eshop360::promotions.coupons', compact('coupons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'             => 'required|string|max:50|unique:eshop_coupons,code',
            'description'      => 'nullable|string|max:500',
            'discount_type'    => 'required|in:percentage,fixed',
            'discount_value'   => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount'     => 'nullable|numeric|min:0',
            'max_uses'         => 'nullable|integer|min:0',
            'max_uses_per_user' => 'nullable|integer|min:0',
            'valid_from'       => 'nullable|date',
            'valid_until'      => 'nullable|date|after_or_equal:valid_from',
            'is_active'        => 'boolean',
        ]);

        $validated['instance_id'] = $request->route('instance_id') ?? session('instance_id');
        $validated['code'] = strtoupper($validated['code']);

        Coupon::create($validated);

        return redirect()->route('eshop360.coupons.index')
            ->with('success', __('Coupon created successfully.'));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validate([
            'code'             => 'required|string|max:50|unique:eshop_coupons,code,' . $coupon->id,
            'description'      => 'nullable|string|max:500',
            'discount_type'    => 'required|in:percentage,fixed',
            'discount_value'   => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount'     => 'nullable|numeric|min:0',
            'max_uses'         => 'nullable|integer|min:0',
            'max_uses_per_user' => 'nullable|integer|min:0',
            'valid_from'       => 'nullable|date',
            'valid_until'      => 'nullable|date|after_or_equal:valid_from',
            'is_active'        => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $coupon->update($validated);

        return redirect()->route('eshop360.coupons.index')
            ->with('success', __('Coupon updated successfully.'));
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('eshop360.coupons.index')
            ->with('success', __('Coupon deleted successfully.'));
    }

    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code'         => 'required|string|max:50',
            'order_amount' => 'nullable|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->first();

        if (!$coupon) {
            return response()->json([
                'valid'   => false,
                'message' => __('Invalid or expired coupon code.'),
            ], 422);
        }

        if ($coupon->max_uses > 0 && $coupon->used_count >= $coupon->max_uses) {
            return response()->json([
                'valid'   => false,
                'message' => __('Coupon usage limit reached.'),
            ], 422);
        }

        // Check per-user usage limit
        if ($coupon->max_uses_per_user > 0 && auth()->check()) {
            $userUsageCount = \Modules\Eshop360\Models\Order::where('coupon_code', $coupon->code)
                ->where('biller_id', auth()->id())
                ->count();

            if ($userUsageCount >= $coupon->max_uses_per_user) {
                return response()->json([
                    'valid'   => false,
                    'message' => __('You have reached the maximum usage limit for this coupon.'),
                ], 422);
            }
        }

        $orderAmount = $request->input('order_amount', 0);
        if ($coupon->min_order_amount > 0 && $orderAmount < $coupon->min_order_amount) {
            return response()->json([
                'valid'   => false,
                'message' => __('Minimum order amount of :amount required.', ['amount' => number_format($coupon->min_order_amount, 2)]),
            ], 422);
        }

        $discount = $coupon->discount_type === 'percentage'
            ? round($orderAmount * ($coupon->discount_value / 100), 2)
            : min($coupon->discount_value, $orderAmount);

        if ($coupon->max_discount > 0 && $discount > $coupon->max_discount) {
            $discount = $coupon->max_discount;
        }

        return response()->json([
            'valid'          => true,
            'coupon'         => [
                'id'             => $coupon->id,
                'code'           => $coupon->code,
                'discount_type'  => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
            ],
            'discount_amount' => $discount,
            'message'         => __('Coupon is valid.'),
        ]);
    }
}

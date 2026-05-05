<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Promotions\Models\Coupon;

class ChannelPortalPromotionController extends Controller
{
    public function index(Request $request)
    {
        $channel = $request->resolved_channel;
        $coupons = Coupon::where('channel_id', $channel->id)
            ->latest()
            ->paginate(20);

        return view('eshop360::channel-portal.promotions.coupons', compact('channel', 'coupons'));
    }

    public function store(Request $request)
    {
        $channel = $request->resolved_channel;
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:eshop_coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:valid_from',
        ]);

        Coupon::create(array_merge($validated, [
            'instance_id' => $instance->id,
            'channel_id' => $channel->id,
            'code' => strtoupper($validated['code']),
            'is_active' => true,
            'used_count' => 0,
        ]));

        return back()->with('success', 'Coupon créé.');
    }

    public function update(Request $request, $channelParam, $couponId)
    {
        $channel = $request->resolved_channel;
        $coupon = Coupon::where('channel_id', $channel->id)->findOrFail($couponId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:valid_from',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $coupon->update($validated);

        return back()->with('success', 'Coupon mis à jour.');
    }

    public function destroy(Request $request, $channelParam, $couponId)
    {
        $channel = $request->resolved_channel;
        Coupon::where('channel_id', $channel->id)->findOrFail($couponId)->delete();

        return back()->with('success', 'Coupon supprimé.');
    }
}

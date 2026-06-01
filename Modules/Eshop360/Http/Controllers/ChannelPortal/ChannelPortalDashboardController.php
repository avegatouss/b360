<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Domain\Channel\Models\ChannelMarginLog;
use Modules\Eshop360\Domain\Sales\Models\Order;

class ChannelPortalDashboardController extends Controller
{
    public function index(Request $request)
    {
        $channel = $request->resolved_channel;

        // Stats for today
        $todaySales = Order::forChannel($channel->id)
            ->whereDate('created_at', today())
            ->sum('total');

        $todayOrdersCount = Order::forChannel($channel->id)
            ->whereDate('created_at', today())
            ->count();

        $pendingOrders = Order::forChannel($channel->id)
            ->where('status', 'pending')
            ->count();

        // Monthly margin summary
        $monthlyMargins = ChannelMarginLog::where('channel_id', $channel->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('SUM(total_margin) as total_margin, SUM(channel_part) as channel_part, SUM(owner_part) as owner_part, SUM(debt_part) as debt_part')
            ->first();

        // Stock count (products linked to this channel)
        $productCount = $channel->products()->count();

        // Recent orders
        $recentOrders = Order::forChannel($channel->id)
            ->with('customer')
            ->latest()
            ->limit(10)
            ->get();

        return view('eshop360::channel-portal.dashboard', compact(
            'channel', 'todaySales', 'todayOrdersCount', 'pendingOrders',
            'monthlyMargins', 'productCount', 'recentOrders'
        ));
    }
}

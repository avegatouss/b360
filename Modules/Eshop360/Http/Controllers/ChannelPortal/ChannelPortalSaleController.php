<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\Order;

class ChannelPortalSaleController extends Controller
{
    /**
     * List completed sales/orders for this channel.
     */
    public function index(Request $request)
    {
        $channel = $request->resolved_channel;

        $query = Order::forChannel($channel->id)
            ->where('status', 'completed')
            ->with('customer', 'items');

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $sales = $query->latest()->paginate(20)->withQueryString();

        $totalSales = Order::forChannel($channel->id)
            ->where('status', 'completed')
            ->sum('total');

        return view('eshop360::channel-portal.sales.index', compact('channel', 'sales', 'totalSales'));
    }

    /**
     * Show sale details.
     */
    public function show(Request $request, $channel, $orderId)
    {
        $channel = $request->resolved_channel;

        $order = Order::forChannel($channel->id)
            ->where('status', 'completed')
            ->with('customer', 'items.product', 'channelMarginLogs')
            ->findOrFail($orderId);

        return view('eshop360::channel-portal.sales.show', compact('channel', 'order'));
    }
}

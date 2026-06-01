<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Domain\Sales\Models\Order;
use Modules\Eshop360\Domain\Sales\Models\OrderItem;

class ChannelPortalReportController extends Controller
{
    public function sales(Request $request)
    {
        $channel = $request->resolved_channel;
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $orders = Order::forChannel($channel->id)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        $stats = [
            'total_sales' => (clone $orders)->sum('total'),
            'total_paid' => (clone $orders)->sum('paid_amount'),
            'total_due' => (clone $orders)->sum('due_amount'),
            'order_count' => (clone $orders)->count(),
            'completed' => (clone $orders)->where('status', 'completed')->count(),
            'pending' => (clone $orders)->where('status', 'pending')->count(),
            'cancelled' => (clone $orders)->where('status', 'cancelled')->count(),
        ];

        $recentOrders = (clone $orders)->with('customer')->latest()->limit(20)->get();

        // Daily sales for chart
        $dailySales = Order::forChannel($channel->id)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->where('status', '!=', 'cancelled')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return view('eshop360::channel-portal.reports.sales', compact('channel', 'stats', 'recentOrders', 'dailySales', 'from', 'to'));
    }

    public function products(Request $request)
    {
        $channel = $request->resolved_channel;
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $topProducts = OrderItem::whereHas('order', function ($q) use ($channel, $from, $to) {
            $q->where('channel_id', $channel->id)
                ->where('status', '!=', 'cancelled')
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to);
        })
            ->select('product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total) as total_revenue'),
            )
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_revenue')
            ->limit(50)
            ->get();

        return view('eshop360::channel-portal.reports.products', compact('channel', 'topProducts', 'from', 'to'));
    }

    public function customers(Request $request)
    {
        $channel = $request->resolved_channel;
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $topCustomers = Order::forChannel($channel->id)
            ->whereNotNull('customer_id')
            ->where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->select('customer_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('SUM(paid_amount) as total_paid'),
            )
            ->groupBy('customer_id')
            ->with('customer')
            ->orderByDesc('total_spent')
            ->limit(30)
            ->get();

        return view('eshop360::channel-portal.reports.customers', compact('channel', 'topCustomers', 'from', 'to'));
    }
}

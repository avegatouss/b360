<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Order;

class ChannelPortalCustomerController extends Controller
{
    /**
     * List customers associated with orders from this channel.
     */
    public function index(Request $request)
    {
        $channel = $request->resolved_channel;

        // Get customer IDs that have orders in this channel
        $customerIds = Order::forChannel($channel->id)
            ->whereNotNull('customer_id')
            ->distinct()
            ->pluck('customer_id');

        $query = Customer::whereIn('id', $customerIds);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();

        // Load order counts per customer for this channel
        $orderCounts = Order::forChannel($channel->id)
            ->whereNotNull('customer_id')
            ->whereIn('customer_id', $customers->pluck('id'))
            ->selectRaw('customer_id, COUNT(*) as count, SUM(total) as total_spent')
            ->groupBy('customer_id')
            ->pluck('total_spent', 'customer_id')
            ->toArray();

        $orderCountsNum = Order::forChannel($channel->id)
            ->whereNotNull('customer_id')
            ->whereIn('customer_id', $customers->pluck('id'))
            ->selectRaw('customer_id, COUNT(*) as count')
            ->groupBy('customer_id')
            ->pluck('count', 'customer_id')
            ->toArray();

        return view('eshop360::channel-portal.customers.index', compact(
            'channel', 'customers', 'orderCounts', 'orderCountsNum'
        ));
    }

    /**
     * Show customer details with order history for this channel.
     */
    public function show(Request $request, $channel, $customerId)
    {
        $channel = $request->resolved_channel;

        $customer = Customer::findOrFail($customerId);

        $orders = Order::forChannel($channel->id)
            ->where('customer_id', $customerId)
            ->with('items')
            ->latest()
            ->paginate(15);

        $totalSpent = Order::forChannel($channel->id)
            ->where('customer_id', $customerId)
            ->sum('total');

        return view('eshop360::channel-portal.customers.show', compact(
            'channel', 'customer', 'orders', 'totalSpent'
        ));
    }
}

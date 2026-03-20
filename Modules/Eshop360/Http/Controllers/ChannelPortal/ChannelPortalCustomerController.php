<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
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

    /**
     * Store a new customer for this channel.
     */
    public function store(Request $request)
    {
        $channel = $request->resolved_channel;
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        Customer::create(array_merge($validated, [
            'instance_id' => $instance->id,
            'channel_id' => $channel->id,
            'is_active' => true,
            'wallet_balance' => 0,
        ]));

        return back()->with('success', 'Client créé avec succès.');
    }

    /**
     * Update an existing customer (must belong to the channel).
     */
    public function update(Request $request, $channelParam, $customerId)
    {
        $channel = $request->resolved_channel;

        $customer = Customer::where('channel_id', $channel->id)->findOrFail($customerId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? $customer->is_active);
        $customer->update($validated);

        return back()->with('success', 'Client mis à jour.');
    }

    /**
     * Delete a customer (only if no orders exist).
     */
    public function destroy(Request $request, $channelParam, $customerId)
    {
        $channel = $request->resolved_channel;

        $customer = Customer::where('channel_id', $channel->id)->findOrFail($customerId);

        $hasOrders = Order::forChannel($channel->id)
            ->where('customer_id', $customer->id)
            ->exists();

        if ($hasOrders) {
            return back()->with('error', 'Impossible de supprimer un client ayant des commandes.');
        }

        $customer->delete();

        return back()->with('success', 'Client supprimé.');
    }

    /**
     * Credit wallet balance for a customer.
     */
    public function walletTopup(Request $request, $channelParam, $customerId)
    {
        $channel = $request->resolved_channel;

        $customer = Customer::where('channel_id', $channel->id)->findOrFail($customerId);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        $customer->increment('wallet_balance', $validated['amount']);

        return back()->with('success', 'Portefeuille crédité de ' . number_format($validated['amount'], 2) . '.');
    }
}

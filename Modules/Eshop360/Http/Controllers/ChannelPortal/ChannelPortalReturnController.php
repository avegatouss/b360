<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Services\StockService;

class ChannelPortalReturnController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(Request $request)
    {
        $channel = $request->resolved_channel;
        $returns = Order::forChannel($channel->id)
            ->where('status', 'refunded')
            ->with('customer', 'items')
            ->latest()
            ->paginate(20);
        return view('eshop360::channel-portal.returns.index', compact('channel', 'returns'));
    }

    public function store(Request $request)
    {
        $channel = $request->resolved_channel;
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'order_id' => 'required|integer',
            'reason' => 'nullable|string|max:500',
        ]);

        $order = Order::forChannel($channel->id)
            ->where('status', 'completed')
            ->findOrFail($validated['order_id']);

        // Mark as refunded
        $order->update(['status' => 'refunded']);

        // Re-stock items in channel's warehouse
        if ($channel->warehouse_id) {
            $order->load('items.product');
            foreach ($order->items as $item) {
                if ($item->product) {
                    $this->stockService->adjustStock(
                        $item->product,
                        $channel->warehouse_id,
                        $item->quantity,
                        'return',
                        "Retour canal: Commande #{$order->order_number}" . ($validated['reason'] ? " - {$validated['reason']}" : ''),
                        auth()->id(),
                        Order::class,
                        $order->id,
                    );
                }
            }
        }

        return back()->with('success', 'Retour traité et stock ajusté.');
    }
}

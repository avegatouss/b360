<?php

namespace Modules\Eshop360\Http\Controllers\OnlineOrder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\OnlineOrder;
use Modules\Eshop360\Services\InvoiceService;
use Modules\Eshop360\Services\OnlineOrderService;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Services\StockService;

class OnlineOrderController extends Controller
{
    public function __construct(private OnlineOrderService $onlineOrderService) {}

    public function index()
    {
        $instance = CurrentInstance::get();
        $orders = OnlineOrder::where('instance_id', $instance->id)
            ->with('customer', 'items.product')
            ->latest()
            ->paginate(20);
        return view('eshop360::online-orders.index', compact('orders'));
    }

    public function show(string $slug, OnlineOrder $onlineOrder)
    {
        $onlineOrder->load('customer', 'items.product');
        return view('eshop360::online-orders.show', compact('onlineOrder'));
    }

    public function updateStatus(Request $request, string $slug, OnlineOrder $onlineOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:validated,preparing,prepared,shipping,delivered,received,invoiced,cancelled',
        ]);

        try {
            $this->onlineOrderService->advanceStatus($onlineOrder, $validated['status']);

            // If status is 'prepared', deduct stock
            if ($validated['status'] === 'prepared') {
                $stockService = app(StockService::class);
                foreach ($onlineOrder->items as $item) {
                    $stockService->adjustStock($item->product, null, -$item->quantity, 'out', "Online order #{$onlineOrder->reference}");
                }
            }

            // If received/invoiced, convert to regular order
            if ($validated['status'] === 'invoiced') {
                $orderService = app(OrderService::class);
                $order = $this->onlineOrderService->convertToOrder($onlineOrder, $orderService);
                $order->loadMissing('invoice', 'items');

                if (! $order->invoice) {
                    app(InvoiceService::class)->createFromOrder($order);
                }
            }

            return redirect()->back()->with('success', 'Order status updated.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $slug, OnlineOrder $onlineOrder)
    {
        if ($onlineOrder->status !== 'pending_validation') {
            return redirect()->back()->with('error', 'Only pending orders can be deleted.');
        }
        $onlineOrder->delete();
        return redirect()->back()->with('success', 'Order deleted.');
    }
}

<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Services\MarginService;
use Modules\Eshop360\Services\StockService;

class ChannelPortalOrderController extends Controller
{
    public function __construct(
        private MarginService $marginService,
        private StockService $stockService,
    ) {}

    /**
     * List orders for this channel with optional status/date filters.
     */
    public function index(Request $request)
    {
        $channel = $request->resolved_channel;

        $query = Order::forChannel($channel->id)
            ->with('customer', 'items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('eshop360::channel-portal.orders.index', compact('channel', 'orders'));
    }

    /**
     * Show the form to create an order with channel product catalog.
     */
    public function create(Request $request)
    {
        $channel = $request->resolved_channel;

        $products = $channel->products()
            ->with('category')
            ->orderBy('name')
            ->get();

        $customers = \Modules\Eshop360\Models\Customer::where(
            'instance_id', $channel->instance_id
        )->orderBy('name')->get();

        return view('eshop360::channel-portal.orders.create', compact('channel', 'products', 'customers'));
    }

    /**
     * Store a new order with channel pricing.
     */
    public function store(Request $request)
    {
        $channel = $request->resolved_channel;
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'customer_id' => 'nullable|integer|exists:eshop_customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:eshop_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $subtotal = 0;
        $itemsData = [];

        foreach ($validated['items'] as $item) {
            $channelPrice = $channel->productPrices()
                ->where('product_id', $item['product_id'])
                ->first();

            $price = $channelPrice ? (float) $channelPrice->sale_price : 0;
            $lineTotal = $price * $item['quantity'];
            $subtotal += $lineTotal;

            $itemsData[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $price,
                'total' => $lineTotal,
            ];
        }

        $order = Order::create([
            'instance_id' => $instance->id,
            'customer_id' => $validated['customer_id'] ?? null,
            'channel_id' => $channel->id,
            'warehouse_id' => $channel->warehouse_id,
            'order_number' => 'CP-' . strtoupper($channel->slug) . '-' . now()->format('ymdHis'),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'due_amount' => $subtotal,
            'paid_amount' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'source' => 'channel_portal',
            'notes' => $validated['notes'] ?? null,
            'biller_id' => auth()->id(),
        ]);

        foreach ($itemsData as $itemData) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'total' => $itemData['total'],
            ]);
        }

        return redirect()->route('eshop360.channel-portal.orders.show', [
            $instance->slug,
            $channel->slug ?? $channel->id,
            $order->id,
        ])->with('success', 'Commande creee avec succes.');
    }

    /**
     * Show order details.
     */
    public function show(Request $request, $channel, $orderId)
    {
        $channel = $request->resolved_channel;

        $order = Order::forChannel($channel->id)
            ->with('customer', 'items.product')
            ->findOrFail($orderId);

        return view('eshop360::channel-portal.orders.show', compact('channel', 'order'));
    }

    /**
     * Confirm reception of an order (triggers margin calculation).
     */
    public function confirmReception(Request $request, $channel, $orderId)
    {
        $channel = $request->resolved_channel;
        $instance = CurrentInstance::get();

        $order = Order::forChannel($channel->id)->findOrFail($orderId);

        $order->update([
            'status' => 'completed',
            'delivered_at' => now(),
        ]);

        // Trigger margin calculation
        $this->marginService->syncOrderMargins($order);

        // Auto-increment stock in channel's warehouse
        if ($channel->warehouse_id) {
            $order->load('items.product');
            foreach ($order->items as $item) {
                if ($item->product) {
                    $this->stockService->adjustStock(
                        $item->product,
                        $channel->warehouse_id,
                        $item->quantity,
                        'in',
                        "Réception canal: Commande #{$order->order_number}",
                        auth()->id(),
                        Order::class,
                        $order->id,
                    );
                }
            }
        }

        return redirect()->route('eshop360.channel-portal.orders.show', [
            $instance->slug,
            $channel->slug ?? $channel->id,
            $order->id,
        ])->with('success', 'Reception confirmee. Les marges ont ete calculees.');
    }
}

<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Sales\Models\Order;
use Modules\Eshop360\Domain\Sales\Models\OrderItem;
use Modules\Eshop360\Services\ChannelB2BService;

class ChannelPortalOrderController extends Controller
{
    public function __construct(
        private readonly ChannelB2BService $channelB2BService,
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
        $hubCustomer = $this->channelB2BService->resolveHubCustomer($channel);

        $products = $channel->products()
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('eshop360::channel-portal.orders.create', compact('channel', 'products', 'hubCustomer'));
    }

    /**
     * Store a new order with channel pricing.
     */
    public function store(Request $request)
    {
        $channel = $request->resolved_channel;
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:eshop_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $subtotal = 0;
        $itemsData = [];

        foreach ($validated['items'] as $item) {
            $product = $channel->products()
                ->where('eshop_products.id', $item['product_id'])
                ->firstOrFail();

            $channelPrice = $channel->productPrices()
                ->where('product_id', $item['product_id'])
                ->first();

            $price = $channelPrice ? (float) $channelPrice->sale_price : 0;
            $lineTotal = $price * $item['quantity'];
            $subtotal += $lineTotal;

            $itemsData[] = [
                'product_id' => $item['product_id'],
                'product_name' => $product->name,
                'sku' => $product->sku ?? '',
                'quantity' => $item['quantity'],
                'price' => $price,
                'total' => $lineTotal,
            ];
        }

        $hubCustomer = $this->channelB2BService->resolveHubCustomer($channel);

        $order = Order::create([
            'instance_id' => $instance->id,
            'customer_id' => $hubCustomer?->id,
            'channel_id' => $channel->id,
            'warehouse_id' => $channel->warehouse_id,
            'order_number' => $this->channelB2BService->buildSupplyOrderNumber($channel),
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
                'product_name' => $itemData['product_name'],
                'sku' => $itemData['sku'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['price'],
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

        try {
            $this->channelB2BService->receiveSupplyOrder($order, $channel, auth()->id());
        } catch (\RuntimeException $e) {
            return redirect()->route('eshop360.channel-portal.orders.show', [
                $instance->slug,
                $channel->slug ?? $channel->id,
                $order->id,
            ])->with('error', $e->getMessage());
        }

        return redirect()->route('eshop360.channel-portal.orders.show', [
            $instance->slug,
            $channel->slug ?? $channel->id,
            $order->id,
        ])->with('success', 'Reception confirmee. Le stock du canal a ete alimente depuis son approvisionnement Saphir Plus.');
    }
}

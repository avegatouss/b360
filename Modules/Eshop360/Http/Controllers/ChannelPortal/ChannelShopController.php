<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Catalog\Models\Category;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Channel\Models\ChannelProductPrice;
use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\Sales\Models\OnlineOrder;
use Modules\Eshop360\Services\OnlineOrderService;
use Modules\Eshop360\Services\ProductPricingService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ChannelShopController extends Controller
{
    public function __construct(
        private readonly ProductPricingService $pricingService,
        private readonly OnlineOrderService $onlineOrderService,
    ) {}

    /**
     * GET /channel-shop/{channel}/catalog — product catalog (channel products only)
     */
    public function catalog(Request $request, string $slug)
    {
        $channel = $this->resolveChannel($request);
        $instanceId = CurrentInstance::get()?->id;

        // Only products that have a ChannelProductPrice for this channel
        $channelProductIds = ChannelProductPrice::where('channel_id', $channel->id)
            ->pluck('product_id');

        $products = Product::query()
            ->with(['category', 'brand', 'stocks'])
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->whereIn('id', $channelProductIds)
            ->active()
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = (string) $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(18)
            ->withQueryString();

        $products->getCollection()->transform(function (Product $product) use ($channel) {
            $pricing = $this->pricingService->resolve($product, $channel->id, true);
            $product->setAttribute('display_price', $pricing['unit_price']);
            $product->setAttribute('display_original_price', $pricing['original_price']);
            $product->setAttribute('display_price_source', $pricing['price_source']);
            $product->setAttribute('display_stock', (int) $product->stocks->sum('quantity'));

            return $product;
        });

        $categories = Category::query()
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->whereIn('id', $products->getCollection()->pluck('category_id')->unique()->filter())
            ->orderBy('name')
            ->get();

        $cart = $this->getCart($channel);
        $totals = $this->calculateTotals($cart);

        return view('eshop360::channel-shop.catalog', compact(
            'channel',
            'products',
            'categories',
            'cart',
            'totals',
        ));
    }

    /**
     * GET /channel-shop/{channel}/product/{product} — product detail
     */
    public function product(Request $request, string $slug, $productId)
    {
        $channel = $this->resolveChannel($request);
        $instanceId = CurrentInstance::get()?->id;

        // Ensure this product belongs to the channel
        $channelPrice = ChannelProductPrice::where('channel_id', $channel->id)
            ->where('product_id', $productId)
            ->first();

        abort_if(! $channelPrice, 404);

        $product = Product::query()
            ->with(['category', 'brand', 'stocks'])
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->active()
            ->findOrFail($productId);

        $pricing = $this->pricingService->resolve($product, $channel->id, true);
        $product->setAttribute('display_price', $pricing['unit_price']);
        $product->setAttribute('display_original_price', $pricing['original_price']);
        $product->setAttribute('display_price_source', $pricing['price_source']);
        $product->setAttribute('display_stock', (int) $product->stocks->sum('quantity'));

        $cart = $this->getCart($channel);
        $totals = $this->calculateTotals($cart);

        return view('eshop360::channel-shop.product', compact(
            'channel',
            'product',
            'cart',
            'totals',
        ));
    }

    /**
     * POST /channel-shop/{channel}/cart/add — add to cart (channel-scoped cart)
     */
    public function addToCart(Request $request, string $slug): RedirectResponse
    {
        $channel = $this->resolveChannel($request);
        $this->resolveCustomer();
        $instanceId = CurrentInstance::get()?->id;

        $validated = $request->validate([
            'product_id' => 'required|exists:eshop_products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        // Ensure product is in this channel
        $channelPrice = ChannelProductPrice::where('channel_id', $channel->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if (! $channelPrice) {
            return redirect()->back()->with('error', __('This product is not available in this channel.'));
        }

        $product = Product::query()
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->active()
            ->findOrFail($validated['product_id']);

        $quantity = max(1, (int) ($validated['quantity'] ?? 1));

        $pricing = $this->pricingService->resolve($product, $channel->id, true);

        $cart = $this->getCart($channel);
        $key = (string) $product->id;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
            $cart[$key]['total'] = round($cart[$key]['quantity'] * $cart[$key]['unit_price'], 2);
        } else {
            $cart[$key] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'image' => $product->image,
                'unit_price' => $pricing['unit_price'],
                'original_price' => $pricing['original_price'],
                'tax_rate' => (float) $product->tax_rate,
                'quantity' => $quantity,
                'total' => round($pricing['unit_price'] * $quantity, 2),
                'channel_id' => $channel->id,
                'price_source' => $pricing['price_source'],
            ];
        }

        $this->storeCart($channel, $cart);

        return redirect()->back()->with('success', __('Product added to cart.'));
    }

    /**
     * GET /channel-shop/{channel}/cart — view cart
     */
    public function cart(Request $request, string $slug)
    {
        $channel = $this->resolveChannel($request);
        $customer = $this->resolveCustomer();
        $cart = $this->getCart($channel);
        $totals = $this->calculateTotals($cart);

        return view('eshop360::channel-shop.cart', compact('channel', 'customer', 'cart', 'totals'));
    }

    /**
     * POST /channel-shop/{channel}/cart/update — update quantities
     */
    public function updateCart(Request $request, string $slug): RedirectResponse
    {
        $channel = $this->resolveChannel($request);
        $this->resolveCustomer();

        $validated = $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart($channel);
        $key = (string) $validated['product_id'];

        if (! isset($cart[$key])) {
            return redirect()->back()->with('error', __('Product not found in cart.'));
        }

        $cart[$key]['quantity'] = (int) $validated['quantity'];
        $cart[$key]['total'] = round($cart[$key]['quantity'] * $cart[$key]['unit_price'], 2);

        $this->storeCart($channel, $cart);

        return redirect()->back()->with('success', __('Cart updated.'));
    }

    /**
     * POST /channel-shop/{channel}/cart/remove — remove item
     */
    public function removeFromCart(Request $request, string $slug): RedirectResponse
    {
        $channel = $this->resolveChannel($request);
        $this->resolveCustomer();

        $validated = $request->validate([
            'product_id' => 'required',
        ]);

        $cart = $this->getCart($channel);
        unset($cart[(string) $validated['product_id']]);

        $this->storeCart($channel, $cart);

        return redirect()->back()->with('success', __('Product removed from cart.'));
    }

    /**
     * GET /channel-shop/{channel}/checkout — checkout page
     */
    public function checkout(Request $request, string $slug)
    {
        $channel = $this->resolveChannel($request);
        $customer = $this->resolveCustomer();
        $cart = $this->getCart($channel);
        $totals = $this->calculateTotals($cart);

        if (empty($cart)) {
            return redirect()->route('eshop360.channel-shop.catalog', [$slug, $channel->slug ?? $channel->id])
                ->with('error', __('Your cart is empty.'));
        }

        return view('eshop360::channel-shop.checkout', compact('channel', 'customer', 'cart', 'totals'));
    }

    /**
     * POST /channel-shop/{channel}/checkout — place order
     */
    public function placeOrder(Request $request, string $slug): RedirectResponse
    {
        $channel = $this->resolveChannel($request);
        $customer = $this->resolveCustomer();
        $cart = $this->getCart($channel);

        if (empty($cart)) {
            return redirect()->route('eshop360.channel-shop.catalog', [$slug, $channel->slug ?? $channel->id])
                ->with('error', __('Add at least one product before checkout.'));
        }

        $validated = $request->validate([
            'delivery_address' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $onlineOrder = $this->onlineOrderService->createOrder(
            $customer->instance_id,
            $customer->id,
            array_map(fn (array $item): array => [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ], array_values($cart)),
            $validated['delivery_address'],
            $validated['notes'] ?? null,
            $channel->id,
        );

        $this->clearCart($channel);

        return redirect()->route('eshop360.channel-shop.orders.show', [$slug, $channel->slug ?? $channel->id, $onlineOrder])
            ->with('success', __('Order submitted successfully.'));
    }

    /**
     * GET /channel-shop/{channel}/orders — customer's order list
     */
    public function orders(Request $request, string $slug)
    {
        $channel = $this->resolveChannel($request);
        $customer = $this->resolveCustomer();

        $orders = OnlineOrder::query()
            ->where('instance_id', $customer->instance_id)
            ->where('customer_id', $customer->id)
            ->where('channel_id', $channel->id)
            ->with(['items.product', 'channel'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', (string) $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('eshop360::channel-shop.orders.index', compact('channel', 'customer', 'orders'));
    }

    /**
     * GET /channel-shop/{channel}/orders/{order} — order detail + tracking
     */
    public function orderDetail(Request $request, string $slug, $channelParam, $orderId)
    {
        $channel = $this->resolveChannel($request);
        $customer = $this->resolveCustomer();

        $onlineOrder = OnlineOrder::findOrFail($orderId);
        $this->ensureCustomerOrder($onlineOrder, $customer, $channel);
        $onlineOrder->loadMissing(['items.product', 'channel']);

        $statusTimeline = $this->statusTimeline();

        return view('eshop360::channel-shop.orders.show', compact('channel', 'customer', 'onlineOrder', 'statusTimeline'));
    }

    /**
     * POST /channel-shop/{channel}/orders/{order}/confirm — confirm reception
     */
    public function confirmReception(Request $request, string $slug, $channelParam, $orderId): RedirectResponse
    {
        $channel = $this->resolveChannel($request);
        $customer = $this->resolveCustomer();

        $onlineOrder = OnlineOrder::findOrFail($orderId);
        $this->ensureCustomerOrder($onlineOrder, $customer, $channel);

        if ($onlineOrder->status !== 'delivered') {
            throw new HttpException(422, 'Only delivered orders can be confirmed as received.');
        }

        $this->onlineOrderService->advanceStatus($onlineOrder, 'received');

        return redirect()->route('eshop360.channel-shop.orders.show', [$slug, $channel->slug ?? $channel->id, $onlineOrder])
            ->with('success', __('Order reception confirmed.'));
    }

    // ─── Private helpers ────────────────────────────────

    private function resolveChannel(Request $request): DistributionChannel
    {
        $channel = $request->resolved_channel;
        abort_if(! $channel, 404);
        abort_if(! $channel->portal_enabled, 403, 'This channel shop is not enabled.');

        return $channel;
    }

    private function resolveCustomer(): Customer
    {
        $instance = CurrentInstance::get();
        $user = auth()->user();
        abort_if(! $instance || ! $user, 403);

        $customer = Customer::query()
            ->where('instance_id', $instance->id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);
                if (! empty($user->email)) {
                    $query->orWhere('email', $user->email);
                }
            })
            ->first();

        abort_if(! $customer, 403, 'No customer profile is linked to this account.');

        return $customer;
    }

    private function ensureCustomerOrder(OnlineOrder $order, Customer $customer, DistributionChannel $channel): void
    {
        abort_unless(
            $order->instance_id === $customer->instance_id
            && $order->customer_id === $customer->id
            && $order->channel_id === $channel->id,
            404
        );
    }

    /**
     * Cart is stored with a channel-specific session key.
     */
    private function cartKey(DistributionChannel $channel): string
    {
        $instanceId = CurrentInstance::idOrFail();

        return "channel_cart_{$channel->id}_instance_{$instanceId}";
    }

    private function getCart(DistributionChannel $channel): array
    {
        return session()->get($this->cartKey($channel), []);
    }

    private function storeCart(DistributionChannel $channel, array $cart): void
    {
        session()->put($this->cartKey($channel), $cart);
    }

    private function clearCart(DistributionChannel $channel): void
    {
        session()->forget($this->cartKey($channel));
    }

    private function calculateTotals(array $cart): array
    {
        $subtotal = 0;
        $tax = 0;
        $discount = 0;

        foreach ($cart as $item) {
            $subtotal += (float) $item['total'];
            $tax += round((float) $item['total'] * (((float) $item['tax_rate']) / 100), 2);

            if (isset($item['original_price']) && (float) $item['original_price'] > (float) $item['unit_price']) {
                $discount += (((float) $item['original_price'] - (float) $item['unit_price']) * (int) $item['quantity']);
            }
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'discount' => round($discount, 2),
            'total' => round($subtotal + $tax, 2),
        ];
    }

    private function statusTimeline(): array
    {
        return [
            ['key' => 'pending_validation', 'label' => 'Pending validation'],
            ['key' => 'validated', 'label' => 'Validated'],
            ['key' => 'preparing', 'label' => 'Preparing'],
            ['key' => 'prepared', 'label' => 'Prepared'],
            ['key' => 'shipping', 'label' => 'Shipping'],
            ['key' => 'delivered', 'label' => 'Delivered'],
            ['key' => 'received', 'label' => 'Received'],
            ['key' => 'invoiced', 'label' => 'Invoiced'],
        ];
    }
}

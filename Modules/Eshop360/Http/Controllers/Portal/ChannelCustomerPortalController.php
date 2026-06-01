<?php

namespace Modules\Eshop360\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Catalog\Models\Brand;
use Modules\Eshop360\Domain\Catalog\Models\Category;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\Sales\Models\OnlineOrder;
use Modules\Eshop360\Services\OnlineOrderService;
use Modules\Eshop360\Services\ProductPricingService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ChannelCustomerPortalController extends Controller
{
    public function __construct(
        private readonly ProductPricingService $pricingService,
        private readonly OnlineOrderService $onlineOrderService,
    ) {}

    /**
     * Show the channel product catalog with channel-specific pricing.
     */
    public function catalog(Request $request, string $slug)
    {
        $channel = $request->resolved_channel;
        $customer = $this->resolveCustomer($channel);
        $instanceId = CurrentInstance::get()?->id;

        // Only products assigned to this channel
        $products = $channel->products()
            ->with(['category', 'brand', 'stocks'])
            ->where('eshop_products.is_active', true)
            ->when($request->filled('category_id'), fn ($query) => $query->where('eshop_products.category_id', $request->integer('category_id')))
            ->when($request->filled('brand_id'), fn ($query) => $query->where('eshop_products.brand_id', $request->integer('brand_id')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = (string) $request->string('search');

                $query->where(function ($q) use ($search) {
                    $q->where('eshop_products.name', 'like', "%{$search}%")
                        ->orWhere('eshop_products.sku', 'like', "%{$search}%")
                        ->orWhere('eshop_products.barcode', 'like', "%{$search}%");
                });
            })
            ->latest('eshop_products.created_at')
            ->paginate(18)
            ->withQueryString();

        // Apply channel pricing
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
            ->orderBy('name')
            ->get();
        $brands = Brand::query()
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->orderBy('name')
            ->get();
        $cart = $this->getCart($channel);
        $totals = $this->calculateTotals($cart);

        return view('eshop360::canal-portal.catalog', compact(
            'customer',
            'products',
            'categories',
            'brands',
            'cart',
            'totals',
            'channel',
        ));
    }

    /**
     * Show the cart scoped to this channel.
     */
    public function cart(Request $request, string $slug)
    {
        $channel = $request->resolved_channel;
        $customer = $this->resolveCustomer($channel);
        $cart = $this->getCart($channel);
        $totals = $this->calculateTotals($cart);

        return view('eshop360::canal-portal.cart', compact('customer', 'cart', 'totals', 'channel'));
    }

    /**
     * Add a product to the channel-scoped cart with channel pricing.
     */
    public function addToCart(Request $request, string $slug): RedirectResponse
    {
        $channel = $request->resolved_channel;
        $this->resolveCustomer($channel);

        $validated = $request->validate([
            'product_id' => 'required|exists:eshop_products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        // Ensure the product belongs to this channel
        $product = $channel->products()
            ->where('eshop_products.is_active', true)
            ->where('eshop_products.id', $validated['product_id'])
            ->firstOrFail();

        $quantity = max(1, (int) ($validated['quantity'] ?? 1));
        $cart = $this->getCart($channel);

        // Channel pricing
        $pricing = $this->pricingService->resolve($product, $channel->id, true);

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
                'price_source' => $pricing['price_source'],
            ];
        }

        $this->storeCart($channel, $cart);

        return redirect()->back()->with('success', __('Product added to online cart.'));
    }

    /**
     * Update item quantity in the channel-scoped cart.
     */
    public function updateCart(Request $request, string $slug, string $itemKey): RedirectResponse
    {
        $channel = $request->resolved_channel;
        $this->resolveCustomer($channel);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart($channel);
        $key = (string) $request->input('product_id', $itemKey);

        if (! isset($cart[$key])) {
            return redirect()->back()->with('error', __('Product not found in online cart.'));
        }

        $cart[$key]['quantity'] = (int) $validated['quantity'];
        $cart[$key]['total'] = round($cart[$key]['quantity'] * $cart[$key]['unit_price'], 2);

        $this->storeCart($channel, $cart);

        return redirect()->back()->with('success', __('Online cart updated.'));
    }

    /**
     * Remove an item from the channel-scoped cart.
     */
    public function removeFromCart(Request $request, string $slug, string $itemKey): RedirectResponse
    {
        $channel = $request->resolved_channel;
        $this->resolveCustomer($channel);

        $cart = $this->getCart($channel);
        $key = (string) $request->input('product_id', $itemKey);

        unset($cart[$key]);

        $this->storeCart($channel, $cart);

        return redirect()->back()->with('success', __('Product removed from online cart.'));
    }

    /**
     * Clear the entire channel-scoped cart.
     */
    public function clearCart(Request $request, string $slug): RedirectResponse
    {
        $channel = $request->resolved_channel;
        $this->resolveCustomer($channel);
        $this->clearCartState($channel);

        return redirect()->route('eshop360.canal-portal.cart', [$slug, $channel->slug ?? $channel->id])
            ->with('success', __('Online cart cleared.'));
    }

    /**
     * Create an OnlineOrder from the channel-scoped cart.
     */
    public function checkout(Request $request, string $slug): RedirectResponse
    {
        $channel = $request->resolved_channel;
        $customer = $this->resolveCustomer($channel);
        $cart = $this->getCart($channel);

        if (empty($cart)) {
            return redirect()->route('eshop360.canal-portal.catalog', [$slug, $channel->slug ?? $channel->id])
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
            $channel->id, // Channel-scoped order
        );

        $this->clearCartState($channel);

        return redirect()->route('eshop360.canal-portal.orders.show', [$slug, $channel->slug ?? $channel->id, $onlineOrder])
            ->with('success', __('Online order submitted successfully.'));
    }

    /**
     * List online orders for the current customer in this channel.
     */
    public function orders(Request $request, string $slug)
    {
        $channel = $request->resolved_channel;
        $customer = $this->resolveCustomer($channel);

        $statusColors = [
            'pending_validation' => 'bg-warning text-dark',
            'validated' => 'bg-info',
            'preparing' => 'bg-primary',
            'prepared' => 'bg-primary',
            'shipping' => 'bg-primary',
            'delivered' => 'bg-success',
            'received' => 'bg-success',
            'invoiced' => 'bg-secondary',
            'cancelled' => 'bg-danger',
        ];

        $orders = OnlineOrder::query()
            ->where('instance_id', $customer->instance_id)
            ->where('customer_id', $customer->id)
            ->where('channel_id', $channel->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('eshop360::canal-portal.orders', compact('customer', 'orders', 'channel', 'statusColors'));
    }

    /**
     * Show a single online order detail with status timeline.
     */
    public function showOrder(Request $request, string $slug, OnlineOrder $onlineOrder)
    {
        $channel = $request->resolved_channel;
        $customer = $this->resolveCustomer($channel);

        $this->authorizeOrder($onlineOrder, $customer, $channel);

        $onlineOrder->loadMissing(['items.product', 'channel']);
        $statusTimeline = $this->statusTimeline();

        return view('eshop360::canal-portal.show-order', compact('customer', 'onlineOrder', 'statusTimeline', 'channel'));
    }

    /**
     * Confirm reception of a delivered order.
     */
    public function confirmReceived(Request $request, string $slug, OnlineOrder $onlineOrder): RedirectResponse
    {
        $channel = $request->resolved_channel;
        $customer = $this->resolveCustomer($channel);

        $this->authorizeOrder($onlineOrder, $customer, $channel);

        if ($onlineOrder->status !== 'delivered') {
            throw new HttpException(422, 'Only delivered orders can be confirmed as received.');
        }

        $this->onlineOrderService->advanceStatus($onlineOrder, 'received');

        return redirect()->route('eshop360.canal-portal.orders.show', [$slug, $channel->slug ?? $channel->id, $onlineOrder])
            ->with('success', __('Order reception confirmed.'));
    }

    /**
     * Cancel a pending/validated order.
     */
    public function cancelOrder(Request $request, string $slug, OnlineOrder $onlineOrder): RedirectResponse
    {
        $channel = $request->resolved_channel;
        $customer = $this->resolveCustomer($channel);

        $this->authorizeOrder($onlineOrder, $customer, $channel);

        if (! in_array($onlineOrder->status, ['pending_validation', 'validated'], true)) {
            throw new HttpException(422, 'Only pending orders can be cancelled.');
        }

        $this->onlineOrderService->advanceStatus($onlineOrder, 'cancelled');

        return redirect()->route('eshop360.canal-portal.orders.show', [$slug, $channel->slug ?? $channel->id, $onlineOrder])
            ->with('success', __('Order cancelled successfully.'));
    }

    // ──────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────

    /**
     * Resolve the current authenticated user to a Customer visible to this channel.
     */
    private function resolveCustomer(DistributionChannel $channel): Customer
    {
        $instance = CurrentInstance::get();
        $user = auth()->user();

        abort_if(! $instance || ! $user, 403);

        $customer = Customer::query()
            ->where('instance_id', $instance->id)
            ->where('channel_id', $channel->id)
            ->where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);

                if (! empty($user->email)) {
                    $query->orWhere('email', $user->email);
                }
            })
            ->first();

        // Admin bypass
        if (! $customer && ($user->hasRole('super-admin') || $user->hasRole('instance-admin'))) {
            $customer = Customer::query()
                ->where('instance_id', $instance->id)
                ->where('channel_id', $channel->id)
                ->where('is_active', true)
                ->first();
        }

        abort_if(! $customer, 403, 'No customer profile is linked to this account. Please create a customer with your email address or user ID.');

        return $customer;
    }

    /**
     * Ensure the order belongs to the customer and to this channel.
     */
    private function authorizeOrder(OnlineOrder $onlineOrder, Customer $customer, DistributionChannel $channel): void
    {
        abort_unless(
            (int) $onlineOrder->instance_id === (int) $customer->instance_id
            && (int) $onlineOrder->customer_id === (int) $customer->id
            && (int) $onlineOrder->channel_id === (int) $channel->id,
            404
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getCart(DistributionChannel $channel): array
    {
        return session()->get($this->scopedCartKey($channel), []);
    }

    /**
     * @param  array<string, array<string, mixed>>  $cart
     */
    private function storeCart(DistributionChannel $channel, array $cart): void
    {
        session()->put($this->scopedCartKey($channel), $cart);
    }

    private function clearCartState(DistributionChannel $channel): void
    {
        session()->forget($this->scopedCartKey($channel));
    }

    private function scopedCartKey(DistributionChannel $channel): string
    {
        $instanceId = CurrentInstance::idOrFail();

        return "eshop_portal_cart_instance_{$instanceId}_channel_{$channel->id}";
    }

    /**
     * @param  array<string, array<string, mixed>>  $cart
     * @return array{subtotal: float, tax: float, discount: float, total: float}
     */
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

    /**
     * @return array<int, array{key: string, label: string}>
     */
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

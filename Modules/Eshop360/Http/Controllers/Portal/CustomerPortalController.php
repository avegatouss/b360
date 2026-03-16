<?php

namespace Modules\Eshop360\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Brand;
use Modules\Eshop360\Models\Category;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\OnlineOrder;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Services\OnlineOrderService;
use Modules\Eshop360\Services\ProductPricingService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomerPortalController extends Controller
{
    public function __construct(
        private readonly ProductPricingService $pricingService,
        private readonly OnlineOrderService $onlineOrderService,
    ) {}

    public function catalog(Request $request, string $slug)
    {
        $customer = $this->resolveCustomer();
        $context = $this->resolvePortalContext($request);
        $instanceId = CurrentInstance::get()?->id;

        $products = Product::query()
            ->with(['category', 'brand', 'stocks'])
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->active()
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('brand_id'), fn ($query) => $query->where('brand_id', $request->integer('brand_id')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = (string) $request->string('search');

                $query->where(function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(18)
            ->withQueryString();

        $products->getCollection()->transform(function (Product $product) use ($context) {
            $pricing = $this->pricingService->resolve(
                $product,
                $context['channel_id'] ?? null,
                (bool) ($context['is_codifarm'] ?? false),
                true,
            );

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
        $channels = $this->activeChannels();
        $cart = $this->getCart();
        $totals = $this->calculateTotals($cart);

        return view('eshop360::portal.catalog', compact(
            'customer',
            'products',
            'categories',
            'brands',
            'channels',
            'context',
            'cart',
            'totals',
        ));
    }

    public function cart(string $slug)
    {
        $customer = $this->resolveCustomer();
        $cart = $this->getCart();
        $totals = $this->calculateTotals($cart);
        $context = $this->getCartContext();
        $channels = $this->activeChannels();

        return view('eshop360::portal.cart', compact('customer', 'cart', 'totals', 'context', 'channels'));
    }

    public function addToCart(Request $request, string $slug): RedirectResponse
    {
        $this->resolveCustomer();
        $instanceId = CurrentInstance::get()?->id;

        $validated = $request->validate([
            'product_id' => 'required|exists:eshop_products,id',
            'quantity' => 'nullable|integer|min:1',
            'channel_id' => 'nullable|exists:eshop_distribution_channels,id',
            'is_codifarm' => 'nullable|boolean',
        ]);

        $product = Product::query()
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->active()
            ->findOrFail($validated['product_id']);
        $quantity = max(1, (int) ($validated['quantity'] ?? 1));
        $cart = $this->getCart();
        $requestedContext = $this->normalizeContext([
            'channel_id' => $validated['channel_id'] ?? null,
            'is_codifarm' => (bool) ($validated['is_codifarm'] ?? false),
        ]);
        $cartContext = $this->resolveContextForMutation($requestedContext, $this->getCartContext(), ! empty($cart));

        if ($cartContext === false) {
            return redirect()->back()->with('error', __('This cart already uses another pricing context. Clear it first.'));
        }

        $pricing = $this->pricingService->resolve(
            $product,
            $cartContext['channel_id'] ?? null,
            (bool) ($cartContext['is_codifarm'] ?? false),
            true,
        );

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
                'channel_id' => $pricing['channel_id'],
                'is_codifarm' => $pricing['is_codifarm'],
                'price_source' => $pricing['price_source'],
            ];
        }

        $this->storeCart($cart);
        $this->storeCartContext($cartContext);

        return redirect()->back()->with('success', __('Product added to online cart.'));
    }

    public function updateCart(Request $request, string $slug, string $itemKey): RedirectResponse
    {
        $this->resolveCustomer();

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart();
        $key = (string) $request->input('product_id', $itemKey);

        if (! isset($cart[$key])) {
            return redirect()->back()->with('error', __('Product not found in online cart.'));
        }

        $cart[$key]['quantity'] = (int) $validated['quantity'];
        $cart[$key]['total'] = round($cart[$key]['quantity'] * $cart[$key]['unit_price'], 2);

        $this->storeCart($cart);

        return redirect()->back()->with('success', __('Online cart updated.'));
    }

    public function removeFromCart(Request $request, string $slug, string $itemKey): RedirectResponse
    {
        $this->resolveCustomer();

        $cart = $this->getCart();
        $key = (string) $request->input('product_id', $itemKey);

        unset($cart[$key]);

        $this->storeCart($cart);

        return redirect()->back()->with('success', __('Product removed from online cart.'));
    }

    public function clearCart(string $slug): RedirectResponse
    {
        $this->resolveCustomer();
        $this->clearCartState();

        return redirect()->route('eshop360.portal.cart', $slug)
            ->with('success', __('Online cart cleared.'));
    }

    public function checkout(Request $request, string $slug): RedirectResponse
    {
        $customer = $this->resolveCustomer();
        $cart = $this->getCart();

        if (empty($cart)) {
            return redirect()->route('eshop360.portal.catalog', $slug)
                ->with('error', __('Add at least one product before checkout.'));
        }

        $validated = $request->validate([
            'delivery_address' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $context = $this->getCartContext();
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
            $context['channel_id'] ?? null,
            (bool) ($context['is_codifarm'] ?? false),
        );

        $this->clearCartState();

        return redirect()->route('eshop360.portal.orders.show', [$slug, $onlineOrder])
            ->with('success', __('Online order submitted successfully.'));
    }

    public function orders(Request $request, string $slug)
    {
        $customer = $this->resolveCustomer();

        $orders = OnlineOrder::query()
            ->where('instance_id', $customer->instance_id)
            ->where('customer_id', $customer->id)
            ->with(['items.product', 'channel'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', (string) $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('eshop360::portal.orders', compact('customer', 'orders'));
    }

    public function showOrder(string $slug, OnlineOrder $onlineOrder)
    {
        $customer = $this->resolveCustomer();
        $onlineOrder = $this->resolveCustomerOrder($onlineOrder, $customer);
        $onlineOrder->loadMissing(['items.product', 'channel']);
        $statusTimeline = $this->statusTimeline();

        return view('eshop360::portal.show-order', compact('customer', 'onlineOrder', 'statusTimeline'));
    }

    public function confirmReceived(string $slug, OnlineOrder $onlineOrder): RedirectResponse
    {
        $customer = $this->resolveCustomer();
        $onlineOrder = $this->resolveCustomerOrder($onlineOrder, $customer);

        if ($onlineOrder->status !== 'delivered') {
            throw new HttpException(422, 'Only delivered orders can be confirmed as received.');
        }

        $this->onlineOrderService->advanceStatus($onlineOrder, 'received');

        return redirect()->route('eshop360.portal.orders.show', [$slug, $onlineOrder])
            ->with('success', __('Order reception confirmed.'));
    }

    public function cancelOrder(string $slug, OnlineOrder $onlineOrder): RedirectResponse
    {
        $customer = $this->resolveCustomer();
        $onlineOrder = $this->resolveCustomerOrder($onlineOrder, $customer);

        if (! in_array($onlineOrder->status, ['pending_validation', 'validated'], true)) {
            throw new HttpException(422, 'Only pending orders can be cancelled.');
        }

        $this->onlineOrderService->advanceStatus($onlineOrder, 'cancelled');

        return redirect()->route('eshop360.portal.orders.show', [$slug, $onlineOrder])
            ->with('success', __('Order cancelled successfully.'));
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

    private function resolveCustomerOrder(OnlineOrder $onlineOrder, Customer $customer): OnlineOrder
    {
        abort_unless(
            $onlineOrder->instance_id === $customer->instance_id && $onlineOrder->customer_id === $customer->id,
            404
        );

        return $onlineOrder;
    }

    /**
     * @return Collection<int, DistributionChannel>
     */
    private function activeChannels(): Collection
    {
        $instanceId = CurrentInstance::get()?->id;

        return DistributionChannel::query()
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getCart(): array
    {
        return session()->get($this->scopedCartKey(), []);
    }

    /**
     * @param  array<string, array<string, mixed>>  $cart
     */
    private function storeCart(array $cart): void
    {
        session()->put($this->scopedCartKey(), $cart);

        if (empty($cart)) {
            $this->storeCartContext(null);
        }
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

    /**
     * @return array{channel_id: int|null, is_codifarm: bool}|null
     */
    private function getCartContext(): ?array
    {
        return $this->normalizeContext(session()->get($this->scopedCartContextKey()));
    }

    /**
     * @return array{channel_id: int|null, is_codifarm: bool}|null
     */
    private function resolvePortalContext(Request $request): ?array
    {
        $requested = $this->normalizeContext([
            'channel_id' => $request->input('channel_id'),
            'is_codifarm' => $request->boolean('is_codifarm'),
        ]);

        return $requested ?? $this->getCartContext();
    }

    /**
     * @param  array{channel_id: int|null, is_codifarm: bool}|null  $context
     */
    private function storeCartContext(?array $context): void
    {
        if ($context === null) {
            session()->forget($this->scopedCartContextKey());

            return;
        }

        session()->put($this->scopedCartContextKey(), $context);
    }

    private function clearCartState(): void
    {
        session()->forget([$this->scopedCartKey(), $this->scopedCartContextKey()]);
    }

    private function scopedCartKey(): string
    {
        return 'eshop_portal_cart_instance_' . (CurrentInstance::get()?->id ?? 0);
    }

    private function scopedCartContextKey(): string
    {
        return 'eshop_portal_cart_context_instance_' . (CurrentInstance::get()?->id ?? 0);
    }

    /**
     * @param  array<string, mixed>|null  $context
     * @return array{channel_id: int|null, is_codifarm: bool}|null
     */
    private function normalizeContext(?array $context): ?array
    {
        $channelId = isset($context['channel_id']) && $context['channel_id'] !== ''
            ? (int) $context['channel_id']
            : null;
        $isCodifarm = (bool) ($context['is_codifarm'] ?? false);

        if ($isCodifarm) {
            return [
                'channel_id' => null,
                'is_codifarm' => true,
            ];
        }

        if ($channelId !== null) {
            return [
                'channel_id' => $channelId,
                'is_codifarm' => false,
            ];
        }

        return null;
    }

    /**
     * @param  array{channel_id: int|null, is_codifarm: bool}|null  $requestedContext
     * @param  array{channel_id: int|null, is_codifarm: bool}|null  $existingContext
     * @return array{channel_id: int|null, is_codifarm: bool}|null|false
     */
    private function resolveContextForMutation(?array $requestedContext, ?array $existingContext, bool $cartHasItems): array|null|false
    {
        if (! $cartHasItems) {
            return $requestedContext;
        }

        if ($requestedContext === null) {
            return $existingContext;
        }

        if ($existingContext === null) {
            return $requestedContext;
        }

        if ($requestedContext === $existingContext) {
            return $existingContext;
        }

        return false;
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

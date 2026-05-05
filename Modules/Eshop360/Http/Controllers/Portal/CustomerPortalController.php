<?php

namespace Modules\Eshop360\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

class CustomerPortalController extends Controller
{
    public function __construct(
        private readonly ProductPricingService $pricingService,
        private readonly OnlineOrderService $onlineOrderService,
    ) {}

    public function catalog(Request $request, string $slug)
    {
        $customer = $this->resolveCustomer();
        $instanceId = CurrentInstance::get()?->id;
        $context = $this->resolvePortalContext($request);
        $channelId = $context['channel_id'] ?? null;

        $this->storeCartContext($context);

        $hasSearch = $request->filled('search');

        $products = Product::withoutChannelScope()
            ->with(['category', 'brand', 'stocks'])
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->active()
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('brand_id'), fn ($query) => $query->where('brand_id', $request->integer('brand_id')))
            ->when($hasSearch, function ($query) use ($request) {
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

        $products->getCollection()->transform(function (Product $product) use ($channelId) {
            $pricing = $this->pricingService->resolve($product, $channelId, true);

            $product->setAttribute('display_price', $pricing['unit_price']);
            $product->setAttribute('display_original_price', $pricing['original_price']);
            $product->setAttribute('display_price_source', $pricing['price_source']);
            $product->setAttribute('display_stock', (int) $product->stocks->sum('quantity'));

            return $product;
        });

        $categories = Category::withoutChannelScope()
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->orderBy('name')
            ->get();
        $brands = Brand::withoutChannelScope()
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->orderBy('name')
            ->get();
        $cart = $this->getCart();
        $totals = $this->calculateTotals($cart);

        return view('eshop360::portal.catalog', compact(
            'customer',
            'products',
            'categories',
            'brands',
            'cart',
            'totals',
        ));
    }

    public function cart(string $slug)
    {
        $customer = $this->resolveCustomer();
        $cart = $this->getCart();
        $totals = $this->calculateTotals($cart);

        return view('eshop360::portal.cart', compact('customer', 'cart', 'totals'));
    }

    public function addToCart(Request $request, string $slug): RedirectResponse
    {
        $this->resolveCustomer();
        $instanceId = CurrentInstance::get()?->id;

        $validated = $request->validate([
            'product_id' => 'required|exists:eshop_products,id',
            'quantity' => 'nullable|integer|min:1',
            'channel_id' => 'nullable|exists:eshop_distribution_channels,id',
        ]);

        $requestedContext = $this->normalizeContext([
            'channel_id' => $validated['channel_id'] ?? null,
        ]);
        $cart = $this->getCart();
        $cartContext = $this->resolveContextForMutation($requestedContext, $this->getCartContext(), ! empty($cart));

        if ($cartContext === false) {
            return redirect()->back()->with('error', __('This cart already uses another pricing context. Clear it first.'));
        }

        $channelId = $cartContext['channel_id'] ?? null;
        $product = Product::withoutChannelScope()
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->active()
            ->findOrFail($validated['product_id']);
        $quantity = max(1, (int) ($validated['quantity'] ?? 1));

        $pricing = $this->pricingService->resolve($product, $channelId, true);

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
                'channel_id' => $pricing['channel_id'],
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
            $this->getCartContext()['channel_id'] ?? null,
        );

        $this->clearCartState();

        return redirect()->route('eshop360.portal.orders.show', [$slug, $onlineOrder])
            ->with('success', __('Online order submitted successfully.'));
    }

    public function orders(Request $request, string $slug)
    {
        $customer = $this->resolveCustomer();
        $source = $request->input('source', 'all');
        $instanceId = $customer->instance_id;
        $customerId = $customer->id;

        // ── Stats globales (toujours sur TOUTES les donnees, pas la page courante) ──
        $statsOnline = \Illuminate\Support\Facades\DB::table('eshop_online_orders')
            ->where('instance_id', $instanceId)
            ->where('customer_id', $customerId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('pending_validation','validated','preparing') THEN 1 ELSE 0 END) as pending,
                COALESCE(SUM(total), 0) as revenue
            ")->first();

        $statsStore = \Illuminate\Support\Facades\DB::table('eshop_orders')
            ->where('instance_id', $instanceId)
            ->where('customer_id', $customerId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                COALESCE(SUM(total), 0) as revenue,
                COALESCE(SUM(paid_amount), 0) as paid,
                COALESCE(SUM(due_amount), 0) as due
            ")->first();

        $globalStats = (object) [
            'total' => (int) $statsOnline->total + (int) $statsStore->total,
            'online_count' => (int) $statsOnline->total,
            'store_count' => (int) $statsStore->total,
            'pending_count' => (int) $statsOnline->pending + (int) $statsStore->pending,
            'total_revenue' => (float) $statsOnline->revenue + (float) $statsStore->revenue,
            'total_paid' => (float) ($statsStore->paid ?? 0),
            'total_due' => (float) ($statsStore->due ?? 0),
        ];

        // ── Union query pour pagination DB ──
        $onlineQuery = \Illuminate\Support\Facades\DB::table('eshop_online_orders as o')
            ->leftJoin('eshop_distribution_channels as ch', 'o.channel_id', '=', 'ch.id')
            ->where('o.instance_id', $instanceId)
            ->where('o.customer_id', $customerId)
            ->when($request->filled('status') && $source !== 'store', fn ($q) => $q->where('o.status', $request->input('status')))
            ->selectRaw("o.id, o.reference as reference, o.total, o.status, 'online' as source_type, NULL as payment_status, ch.name as channel_name, o.created_at");

        $storeQuery = \Illuminate\Support\Facades\DB::table('eshop_orders as o')
            ->where('o.instance_id', $instanceId)
            ->where('o.customer_id', $customerId)
            ->when($request->filled('status') && $source !== 'online', fn ($q) => $q->where('o.status', $request->input('status')))
            ->selectRaw("o.id, COALESCE(o.order_number, CONCAT('ORD-', o.id)) as reference, o.total, o.status, o.source as source_type, o.payment_status, NULL as channel_name, o.created_at");

        if ($source === 'online') {
            $combinedQuery = $onlineQuery;
        } elseif ($source === 'store') {
            $combinedQuery = $storeQuery;
        } else {
            $combinedQuery = $onlineQuery->unionAll($storeQuery);
        }

        $orders = \Illuminate\Support\Facades\DB::query()
            ->fromSub($combinedQuery, 'combined')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Map to objects with proper labels and URLs
        $orders->getCollection()->transform(function ($row) use ($slug) {
            $row->total = (float) $row->total;
            $row->created_at = \Carbon\Carbon::parse($row->created_at);
            $isOnline = $row->source_type === 'online';
            $row->source_label = $isOnline ? __('Portail') : ($row->source_type === 'pos' ? __('Magasin') : __('Manuel'));
            $row->show_url = $isOnline
                ? route('eshop360.portal.orders.show', [$slug, $row->id])
                : route('eshop360.portal.orders.store-show', [$slug, $row->id]);
            $row->source_type = $isOnline ? 'online' : 'store';

            return $row;
        });

        return view('eshop360::portal.orders', compact('customer', 'orders', 'source', 'globalStats'));
    }

    public function account(Request $request, string $slug)
    {
        $customer = $this->resolveCustomer();
        $instanceId = $customer->instance_id;

        // Wallet transactions (deposits & debits)
        $transactions = \Modules\Eshop360\Domain\CRM\Models\CustomerTransaction::withoutChannelScope()->where('customer_id', $customer->id)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Customer dues (credits/debts)
        $dues = \Modules\Eshop360\Domain\CRM\Models\CustomerDue::withoutChannelScope()->where('customer_id', $customer->id)
            ->latest()
            ->get();

        // Payments received on customer orders
        $payments = \Illuminate\Support\Facades\DB::table('eshop_orders')
            ->where('instance_id', $instanceId)
            ->where('customer_id', $customer->id)
            ->where('paid_amount', '>', 0)
            ->select('order_number', 'payment_method', 'paid_amount', 'due_amount', 'total', 'status', 'payment_status', 'created_at')
            ->latest('created_at')
            ->limit(50)
            ->get();

        // KPIs
        $totalDeposits = \Modules\Eshop360\Domain\CRM\Models\CustomerTransaction::withoutChannelScope()->where('customer_id', $customer->id)
            ->where('type', 'credit')->sum('amount');
        $totalDebits = \Modules\Eshop360\Domain\CRM\Models\CustomerTransaction::withoutChannelScope()->where('customer_id', $customer->id)
            ->where('type', 'debit')->sum('amount');
        $totalDueAmount = $dues->whereIn('status', ['pending', 'partial'])
            ->sum(fn ($d) => (float) $d->amount_due - (float) $d->paid_amount);
        $totalPaid = $payments->sum('paid_amount');

        // Total du from partially paid orders
        $totalOrderDue = \Illuminate\Support\Facades\DB::table('eshop_orders')
            ->where('instance_id', $instanceId)
            ->where('customer_id', $customer->id)
            ->where('due_amount', '>', 0)
            ->whereIn('payment_status', ['partial', 'unpaid'])
            ->sum('due_amount');

        // Payments grouped by method
        $paymentsByMethod = \Illuminate\Support\Facades\DB::table('eshop_orders')
            ->where('instance_id', $instanceId)
            ->where('customer_id', $customer->id)
            ->where('paid_amount', '>', 0)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(paid_amount) as total_paid, SUM(due_amount) as total_due')
            ->groupBy('payment_method')
            ->orderByDesc('total_paid')
            ->get();

        $settings = app(\Modules\Eshop360\Services\EshopSettingsService::class)->get('invoice');

        return view('eshop360::portal.account', compact(
            'customer', 'transactions', 'dues', 'payments',
            'totalDeposits', 'totalDebits', 'totalDueAmount', 'totalPaid',
            'totalOrderDue', 'paymentsByMethod', 'settings'
        ));
    }

    public function accountExport(Request $request, string $slug, string $format)
    {
        $customer = $this->resolveCustomer();
        $settings = app(\Modules\Eshop360\Services\EshopSettingsService::class)->get('invoice');

        $transactions = \Modules\Eshop360\Domain\CRM\Models\CustomerTransaction::withoutChannelScope()->where('customer_id', $customer->id)->latest()->get();
        $dues = \Modules\Eshop360\Domain\CRM\Models\CustomerDue::withoutChannelScope()->where('customer_id', $customer->id)->latest()->get();
        $payments = \Illuminate\Support\Facades\DB::table('eshop_orders')
            ->where('instance_id', $customer->instance_id)
            ->where('customer_id', $customer->id)
            ->where('paid_amount', '>', 0)
            ->select('order_number', 'payment_method', 'paid_amount', 'due_amount', 'total', 'payment_status', 'created_at')
            ->latest('created_at')->get();

        if ($format === 'print') {
            return view('eshop360::portal.account-print', compact('customer', 'transactions', 'dues', 'payments', 'settings'));
        }

        // CSV export
        $filename = 'releve-compte-'.$customer->code.'-'.now()->format('Ymd').'.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        $callback = function () use ($transactions, $dues, $payments, $customer) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fputcsv($f, ['Releve de compte - '.$customer->name.' ('.$customer->code.')'], ';');
            fputcsv($f, [], ';');

            fputcsv($f, ['=== TRANSACTIONS PORTEFEUILLE ==='], ';');
            fputcsv($f, ['Date', 'Type', 'Montant', 'Notes'], ';');
            foreach ($transactions as $t) {
                fputcsv($f, [$t->created_at->format('d/m/Y H:i'), $t->type === 'credit' ? 'Depot' : 'Retrait', $t->amount, $t->notes], ';');
            }

            fputcsv($f, [], ';');
            fputcsv($f, ['=== CREDITS / DETTES ==='], ';');
            fputcsv($f, ['Date', 'Montant', 'Paye', 'Reste', 'Statut', 'Echeance'], ';');
            foreach ($dues as $d) {
                fputcsv($f, [$d->created_at->format('d/m/Y'), $d->amount_due, $d->paid_amount, (float) $d->amount_due - (float) $d->paid_amount, $d->status, $d->due_date?->format('d/m/Y')], ';');
            }

            fputcsv($f, [], ';');
            fputcsv($f, ['=== PAIEMENTS COMMANDES ==='], ';');
            fputcsv($f, ['Date', 'Commande', 'Methode', 'Total', 'Paye', 'Restant', 'Statut'], ';');
            foreach ($payments as $p) {
                fputcsv($f, [\Carbon\Carbon::parse($p->created_at)->format('d/m/Y'), $p->order_number, $p->payment_method, $p->total, $p->paid_amount, $p->due_amount, $p->payment_status], ';');
            }

            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printStoreOrder(string $slug, \Modules\Eshop360\Domain\Sales\Models\Order $order)
    {
        $customer = $this->resolveCustomer();
        abort_unless(
            (int) $order->customer_id === (int) $customer->id
            && (int) $order->instance_id === (int) $customer->instance_id,
            403
        );
        $order->loadMissing(['items.product', 'store', 'customer']);

        $settings = app(\Modules\Eshop360\Services\EshopSettingsService::class)->get('invoice');
        $instance = CurrentInstance::get();

        return view('eshop360::portal.print-order', compact('order', 'customer', 'settings', 'instance'));
    }

    public function printOnlineOrder(string $slug, OnlineOrder $onlineOrder)
    {
        $customer = $this->resolveCustomer();
        abort_unless(
            (int) $onlineOrder->customer_id === (int) $customer->id
            && (int) $onlineOrder->instance_id === (int) $customer->instance_id,
            403
        );
        $onlineOrder->loadMissing(['items.product', 'channel']);

        $settings = app(\Modules\Eshop360\Services\EshopSettingsService::class)->get('invoice');
        $instance = CurrentInstance::get();

        return view('eshop360::portal.print-online-order', compact('onlineOrder', 'customer', 'settings', 'instance'));
    }

    public function showStoreOrder(string $slug, \Modules\Eshop360\Domain\Sales\Models\Order $order)
    {
        $customer = $this->resolveCustomer();

        abort_unless(
            (int) $order->customer_id === (int) $customer->id && (int) $order->instance_id === (int) $customer->instance_id,
            403,
            'This order does not belong to you.'
        );

        $order->loadMissing(['items.product', 'store', 'warehouse']);

        return view('eshop360::portal.show-store-order', compact('customer', 'order'));
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

        // Bypass all scopes: we already filter by instance_id explicitly,
        // and ChannelScope would exclude hub-level customers from portal context.
        $customer = Customer::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);

                if (! empty($user->email)) {
                    $query->orWhere('email', $user->email);
                }
            })
            ->first();

        // Admin bypass: if user is admin, use first active customer or create a virtual context
        if (! $customer && ($user->hasRole('super-admin') || $user->hasRole('instance-admin'))) {
            $customer = Customer::withoutGlobalScopes()
                ->where('instance_id', $instance->id)
                ->where('is_active', true)
                ->first();
        }

        abort_if(! $customer, 403, 'No customer profile is linked to this account. Please create a customer with your email address or user ID.');

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
     * @return array{channel_id: int|null}|null
     */
    private function getCartContext(): ?array
    {
        return $this->normalizeContext(session()->get($this->scopedCartContextKey()));
    }

    /**
     * @return array{channel_id: int|null}|null
     */
    private function resolvePortalContext(Request $request): ?array
    {
        $requested = $this->normalizeContext([
            'channel_id' => $request->input('channel_id'),
        ]);

        return $requested ?? $this->getCartContext();
    }

    /**
     * @param  array{channel_id: int|null}|null  $context
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
        return 'eshop_portal_cart_instance_'.(CurrentInstance::idOrFail());
    }

    private function scopedCartContextKey(): string
    {
        return 'eshop_portal_cart_context_instance_'.(CurrentInstance::idOrFail());
    }

    /**
     * @param  array<string, mixed>|null  $context
     * @return array{channel_id: int|null}|null
     */
    private function normalizeContext(?array $context): ?array
    {
        $channelId = isset($context['channel_id']) && $context['channel_id'] !== ''
            ? (int) $context['channel_id']
            : null;

        if ($channelId !== null) {
            return [
                'channel_id' => $channelId,
            ];
        }

        return null;
    }

    /**
     * @param  array{channel_id: int|null}|null  $requestedContext
     * @param  array{channel_id: int|null}|null  $existingContext
     * @return array{channel_id: int|null}|null|false
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

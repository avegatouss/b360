<?php

namespace Modules\Eshop360\Http\Controllers\Pos;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Services\EshopSettingsService;
use Modules\Eshop360\Models\CashRegister;
use Modules\Eshop360\Models\Holding;
use Modules\Eshop360\Models\Brand;
use Modules\Eshop360\Models\Category;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Store;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Services\CartService;
use Modules\Eshop360\Services\CashRegisterService;
use Modules\Eshop360\Services\HoldingService;
use Modules\Eshop360\Services\ProductPricingService;
use Modules\Eshop360\Services\UserResourceScopeService;

class PosController extends Controller
{
    public function __construct(
        private readonly CashRegisterService $cashRegisterService,
        private readonly HoldingService $holdingService,
        private readonly EshopSettingsService $eshopSettings,
        private readonly CartService $cartService,
    ) {
    }

    public function index(string $slug, Request $request)
    {
        $data = $this->posData($request);

        return view('eshop360::pos.index', $data);
    }

    public function layout2(string $slug, Request $request)
    {
        $data = $this->posData($request);

        return view('eshop360::pos.layout2', $data);
    }

    public function layout3(string $slug, Request $request)
    {
        $data = $this->posData($request);

        return view('eshop360::pos.layout3', $data);
    }

    public function layout4(string $slug, Request $request)
    {
        $data = $this->posData($request);

        return view('eshop360::pos.layout4', $data);
    }

    public function layout5(string $slug, Request $request)
    {
        $data = $this->posData($request);

        return view('eshop360::pos.layout5', $data);
    }

    public function settings(string $slug)
    {
        $settings = $this->eshopSettings->get('pos');

        return view('eshop360::pos.settings', compact('settings'));
    }

    public function updateSettings(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'default_layout'       => 'required|in:layout1,layout2,layout3,layout4,layout5',
            'default_warehouse_id' => 'nullable|exists:eshop_warehouses,id',
            'default_customer_id'  => 'nullable|exists:eshop_customers,id',
            'payment_methods'      => 'required|array|min:1',
            'payment_methods.*'    => 'string|in:cash,card,cheque,paypal,bank_transfer,points,deposit,gift_card,wallet,external',
            'tax_inclusive'         => 'boolean',
            'sound_enabled'        => 'boolean',
            'print_receipt'        => 'boolean',
            'products_per_page'    => 'nullable|integer|min:10|max:100',
            'register_required'        => 'boolean',
            'customer_account_enabled' => 'boolean',
            'allow_walkin_customer'    => 'boolean',
        ]);

        // Ensure boolean fields default to false when unchecked
        $validated['register_required'] = $validated['register_required'] ?? false;
        $validated['customer_account_enabled'] = $validated['customer_account_enabled'] ?? false;
        $validated['allow_walkin_customer'] = $validated['allow_walkin_customer'] ?? false;
        $validated['tax_inclusive'] = $validated['tax_inclusive'] ?? false;
        $validated['sound_enabled'] = $validated['sound_enabled'] ?? false;
        $validated['print_receipt'] = $validated['print_receipt'] ?? false;

        $this->eshopSettings->set('pos', $validated);

        return redirect()->route('eshop360.pos.settings', ['slug' => $slug])
            ->with('success', __('POS settings updated successfully.'));
    }

    public function orders(string $slug, Request $request)
    {
        $user = auth()->user();

        $query = Order::with(['customer', 'items.product', 'cashRegister', 'store', 'cashier'])
            ->where('source', 'pos')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->payment_status, fn ($q, $s) => $q->where('payment_status', $s))
            ->when($request->search, fn ($q, $s) => $q->where(function ($searchQuery) use ($s) {
                $searchQuery->where('order_number', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$s}%"));
            }))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('eshop360::pos.orders', compact('orders'));
    }

    public function openRegister(Request $request, string $slug): RedirectResponse
    {
        if ($this->cashRegisterService->getCurrentRegister()) {
            return redirect()->route('eshop360.pos.index', ['slug' => $slug])
                ->with('error', __('A cash register is already open for your session.'));
        }

        $validated = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
            'store_id' => 'nullable|exists:eshop_stores,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $instanceId = CurrentInstance::idOrFail();

        $register = $this->cashRegisterService->open(
            $instanceId,
            (float) $validated['opening_amount'],
            $validated['store_id'] ?? null,
        );

        if (! empty($validated['notes'])) {
            $register->update(['notes' => $validated['notes']]);
        }

        return redirect()->route('eshop360.pos.index', ['slug' => $slug])
            ->with('success', __('Cash register opened successfully.'));
    }

    public function closeRegister(Request $request, string $slug, CashRegister $register): RedirectResponse
    {
        $validated = $request->validate([
            'closing_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($register->user_id !== auth()->id()) {
            abort(403);
        }

        if ($register->status !== 'open') {
            return redirect()->route('eshop360.pos.index', ['slug' => $slug])
                ->with('error', __('This cash register is already closed.'));
        }

        $closedRegister = $this->cashRegisterService->close($register, (float) $validated['closing_amount']);

        if (! empty($validated['notes'])) {
            $closedRegister->update(['notes' => $validated['notes']]);
        }

        return redirect()->route('eshop360.pos.index', ['slug' => $slug])
            ->with('success', __('Cash register closed. Difference: :amount', [
                'amount' => number_format((float) $closedRegister->difference, 2),
            ]));
    }

    public function storeHolding(Request $request, string $slug): RedirectResponse
    {
        $cart = $this->cartService->getCart();

        if (empty($cart)) {
            return redirect()->route('eshop360.pos.index', ['slug' => $slug])
                ->with('error', __('Your cart is empty.'));
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:eshop_customers,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $instanceId = CurrentInstance::idOrFail();
        $coupon = $this->cartService->getCoupon();
        $cartContext = $this->cartService->getContext();
        $totals = $this->cartService->calculateTotals($cart, $coupon);

        $holding = $this->holdingService->createFromSnapshot(
            $cart,
            $coupon,
            $cartContext,
            [
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax'],
                'discount_amount' => $totals['discount'],
                'total' => $totals['total'],
            ],
            $instanceId,
            $validated['customer_id'] ?? null,
            $validated['notes'] ?? null,
        );

        $this->cartService->clear();

        return redirect()->route('eshop360.pos.index', ['slug' => $slug])
            ->with('success', __('Cart saved on hold as :reference.', ['reference' => $holding->reference]));
    }

    public function resumeHolding(string $slug, Holding $holding): RedirectResponse
    {
        $this->holdingService->restoreSnapshotToSession($holding);

        return redirect()->route('eshop360.pos.index', ['slug' => $slug])
            ->with('success', __('Holding restored to the current cart.'));
    }

    public function customerWalletInfo(string $slug, Customer $customer): JsonResponse
    {
        return response()->json([
            'wallet_balance' => round((float) $customer->wallet_balance, 2),
            'credit_limit'   => round((float) $customer->credit_limit, 2),
            'available'      => round((float) $customer->wallet_balance + (float) $customer->credit_limit, 2),
        ]);
    }

    /**
     * Gather common data needed by all POS layout views.
     */
    private function posData(Request $request): array
    {
        $instanceId = CurrentInstance::idOrFail();
        $settings = $this->eshopSettings->get('pos');
        $perPage = $request->integer('per_page', (int) ($settings['products_per_page'] ?? 24));

        $scope = app(UserResourceScopeService::class);

        $products = Product::with(['category', 'brand', 'stocks', 'variations' => fn ($q) => $q->where('is_active', true)])
            ->active()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%"))
            ->when($request->category_id, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->brand_id, fn ($q, $b) => $q->where('brand_id', $b))
            ->when(
                $scope->hasWarehouseAssignments() || $scope->hasStoreAssignments(),
                function ($query) use ($scope) {
                    $query->whereHas('stocks', function ($sq) use ($scope) {
                        $scope->applyWarehouseScope($sq, 'warehouse_id');
                        if ($scope->hasStoreAssignments()) {
                            $scope->applyStoreScope($sq, 'store_id');
                        }
                    });
                }
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        // Cache reference data (categories, brands, warehouses, stores) — 10 min TTL
        // Include user ID in cache keys to avoid returning unscoped data to restricted users
        $userId = auth()->id() ?? 0;
        $categories = Cache::remember("pos:categories:{$instanceId}:{$userId}", 600, fn () =>
            Category::active()->roots()->orderBy('sort_order')->orderBy('name')->get()
        );
        $brands = Cache::remember("pos:brands:{$instanceId}:{$userId}", 600, fn () =>
            Brand::where('is_active', true)->orderBy('name')->get()
        );
        $warehouses = Cache::remember("pos:warehouses:{$instanceId}:{$userId}", 600, function () use ($scope) {
            $query = Warehouse::where('is_active', true)->orderBy('name');
            if ($scope->hasWarehouseAssignments()) {
                $query->whereIn('id', $scope->warehouseIds());
            }
            return $query->get();
        });
        $stores = Cache::remember("pos:stores:{$instanceId}:{$userId}", 600, function () use ($scope) {
            $query = Store::where('is_active', true)->orderBy('name');
            if ($scope->hasStoreAssignments()) {
                $query->whereIn('id', $scope->storeIds());
            }
            return $query->get();
        });
        $channels = Cache::remember("pos:channels:{$instanceId}:{$userId}", 600, fn () =>
            DistributionChannel::where('instance_id', $instanceId)->where('is_active', true)->orderBy('name')->get()
        );

        $cart = $this->cartService->getCart();
        $coupon = $this->cartService->getCoupon();
        $cartContext = $this->cartService->getContext();
        $activeChannelId = $request->filled('channel_id')
            ? $request->integer('channel_id')
            : ($cartContext['channel_id'] ?? null);

        // Customers scoped by active channel
        $customers = Customer::where('instance_id', $instanceId)->where('is_active', true)
            ->when($activeChannelId, fn($q) => $q->where('channel_id', $activeChannelId))
            ->orderBy('name')->limit(500)->get();

        $products->getCollection()->transform(function (Product $product) use ($activeChannelId) {
            $pricing = app(ProductPricingService::class)->resolve($product, $activeChannelId, true);
            $product->setAttribute('display_price', (float) $pricing['unit_price']);
            $product->setAttribute('display_original_price', (float) $pricing['original_price']);
            $product->setAttribute('display_price_source', $pricing['price_source']);

            return $product;
        });

        $totals = $this->cartService->calculateTotals($cart, $coupon);
        $currentRegister = $this->cashRegisterService->getCurrentRegister();
        $currentRegisterExpected = $currentRegister ? $this->cashRegisterService->expectedAmount($currentRegister) : null;
        $holdings = $this->holdingService->getActiveHoldings($instanceId);
        $paymentMethods = collect($settings['payment_methods'] ?? ['cash', 'card'])
            ->mapWithKeys(fn (string $method): array => [$method => $this->paymentMethodLabel($method)])
            ->all();

        return compact(
            'products',
            'categories',
            'brands',
            'customers',
            'channels',
            'warehouses',
            'stores',
            'settings',
            'cart',
            'coupon',
            'cartContext',
            'totals',
            'paymentMethods',
            'currentRegister',
            'currentRegisterExpected',
            'holdings',
        );
    }

    private function paymentMethodLabel(string $method): string
    {
        $key = "eshop::eshop.payment_{$method}";
        $translated = __($key);

        return $translated !== $key ? $translated : ucfirst(str_replace('_', ' ', $method));
    }
}

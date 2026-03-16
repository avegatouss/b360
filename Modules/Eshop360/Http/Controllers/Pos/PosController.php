<?php

namespace Modules\Eshop360\Http\Controllers\Pos;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Support\CurrentInstance;
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
use Modules\Eshop360\Services\CashRegisterService;
use Modules\Eshop360\Services\HoldingService;

class PosController extends Controller
{
    public function __construct(
        private readonly CashRegisterService $cashRegisterService,
        private readonly HoldingService $holdingService,
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
        $instanceId = CurrentInstance::get()?->id ?? 0;
        $settings = Cache::get("eshop_pos_settings_{$instanceId}", $this->defaultPosSettings());

        return view('eshop360::pos.settings', compact('settings'));
    }

    public function updateSettings(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'default_layout'       => 'required|in:layout1,layout2,layout3,layout4,layout5',
            'default_warehouse_id' => 'nullable|exists:eshop_warehouses,id',
            'default_customer_id'  => 'nullable|exists:eshop_customers,id',
            'payment_methods'      => 'required|array|min:1',
            'payment_methods.*'    => 'string|in:cash,card,cheque,paypal,bank_transfer,points,deposit,gift_card,external',
            'tax_inclusive'         => 'boolean',
            'sound_enabled'        => 'boolean',
            'print_receipt'        => 'boolean',
            'products_per_page'    => 'nullable|integer|min:10|max:100',
        ]);

        $instanceId = CurrentInstance::get()?->id ?? 0;
        Cache::put("eshop_pos_settings_{$instanceId}", $validated);

        return redirect()->route('eshop360.pos.settings', ['slug' => $slug])
            ->with('success', __('POS settings updated successfully.'));
    }

    public function orders(string $slug, Request $request)
    {
        $orders = Order::with(['customer', 'items.product', 'cashRegister', 'store', 'cashier'])
            ->where('source', 'pos')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->payment_status, fn ($q, $s) => $q->where('payment_status', $s))
            ->when($request->search, fn ($q, $s) => $q->where(function ($searchQuery) use ($s) {
                $searchQuery->where('order_number', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$s}%"));
            }))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(20)
            ->withQueryString();

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

        $instanceId = CurrentInstance::get()?->id ?? 0;

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
        $cart = $this->getCart();

        if (empty($cart)) {
            return redirect()->route('eshop360.pos.index', ['slug' => $slug])
                ->with('error', __('Your cart is empty.'));
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:eshop_customers,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $instanceId = CurrentInstance::get()?->id ?? 0;
        $coupon = $this->getCoupon();
        $cartContext = $this->getCartContext();
        $totals = $this->calculateTotals($cart, $coupon);

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

        $this->clearCart();

        return redirect()->route('eshop360.pos.index', ['slug' => $slug])
            ->with('success', __('Cart saved on hold as :reference.', ['reference' => $holding->reference]));
    }

    public function resumeHolding(string $slug, Holding $holding): RedirectResponse
    {
        $this->holdingService->restoreSnapshotToSession($holding);

        return redirect()->route('eshop360.pos.index', ['slug' => $slug])
            ->with('success', __('Holding restored to the current cart.'));
    }

    /**
     * Gather common data needed by all POS layout views.
     */
    private function posData(Request $request): array
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;
        $settings = Cache::get("eshop_pos_settings_{$instanceId}", $this->defaultPosSettings());
        $perPage = $request->integer('per_page', (int) ($settings['products_per_page'] ?? 24));

        $products = Product::with(['category', 'brand', 'stocks'])
            ->active()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%"))
            ->when($request->category_id, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->brand_id, fn ($q, $b) => $q->where('brand_id', $b))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $categories = Category::active()->roots()->orderBy('sort_order')->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $channels = DistributionChannel::where('instance_id', $instanceId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();

        $cart = $this->getCart();
        $coupon = $this->getCoupon();
        $cartContext = $this->getCartContext();
        $totals = $this->calculateTotals($cart, $coupon);
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

    private function defaultPosSettings(): array
    {
        return [
            'default_layout'       => 'layout1',
            'default_warehouse_id' => null,
            'default_customer_id'  => null,
            'payment_methods'      => ['cash', 'card'],
            'tax_inclusive'         => false,
            'sound_enabled'        => true,
            'print_receipt'        => true,
            'products_per_page'    => 24,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getCart(): array
    {
        if (session()->has($this->scopedCartKey())) {
            return session()->get($this->scopedCartKey(), []);
        }

        $legacyCart = session()->get('eshop_cart', []);

        if (! empty($legacyCart)) {
            session()->put($this->scopedCartKey(), $legacyCart);
        }

        return $legacyCart;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getCoupon(): ?array
    {
        if (session()->has($this->scopedCouponKey())) {
            return session()->get($this->scopedCouponKey());
        }

        $legacyCoupon = session()->get('eshop_cart_coupon');

        if ($legacyCoupon !== null) {
            session()->put($this->scopedCouponKey(), $legacyCoupon);
        }

        return $legacyCoupon;
    }

    /**
     * @return array{channel_id: int|null, is_codifarm: bool}|null
     */
    private function getCartContext(): ?array
    {
        if (session()->has($this->scopedCartContextKey())) {
            return session()->get($this->scopedCartContextKey());
        }

        $legacyContext = session()->get('eshop_cart_context');

        if ($legacyContext !== null) {
            session()->put($this->scopedCartContextKey(), $legacyContext);
        }

        return $legacyContext;
    }

    /**
     * @param  array<string, array<string, mixed>>  $cart
     * @param  array<string, mixed>|null  $coupon
     * @return array{subtotal: float, tax: float, discount: float, total: float}
     */
    private function calculateTotals(array $cart, ?array $coupon): array
    {
        $subtotal = 0.0;
        $tax = 0.0;

        foreach ($cart as $item) {
            $lineTotal = (float) ($item['total'] ?? ((float) ($item['unit_price'] ?? $item['price'] ?? 0) * (int) ($item['quantity'] ?? 0)));
            $subtotal += $lineTotal;
            $tax += round($lineTotal * (((float) ($item['tax_rate'] ?? 0)) / 100), 2);
        }

        $discount = 0.0;

        if ($coupon) {
            if (($coupon['type'] ?? null) === 'percentage') {
                $discount = round($subtotal * (((float) ($coupon['value'] ?? 0)) / 100), 2);
            } else {
                $discount = min((float) ($coupon['value'] ?? 0), $subtotal);
            }
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'discount' => round($discount, 2),
            'total' => round(max(0, $subtotal + $tax - $discount), 2),
        ];
    }

    private function scopedCartKey(): string
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;

        return 'eshop_cart_instance_' . $instanceId;
    }

    private function scopedCouponKey(): string
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;

        return 'eshop_cart_coupon_instance_' . $instanceId;
    }

    private function scopedCartContextKey(): string
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;

        return 'eshop_cart_context_instance_' . $instanceId;
    }

    private function clearCart(): void
    {
        session()->forget([
            $this->scopedCartKey(),
            $this->scopedCouponKey(),
            $this->scopedCartContextKey(),
            'eshop_cart',
            'eshop_cart_coupon',
            'eshop_cart_context',
        ]);
    }

    private function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'cash' => 'Especes',
            'card' => 'Carte',
            'cheque' => 'Cheque',
            'paypal' => 'PayPal',
            'bank_transfer' => 'Virement',
            'points' => 'Points',
            'deposit' => 'Depot',
            'gift_card' => 'Carte cadeau',
            'external' => 'Externe',
            default => ucfirst(str_replace('_', ' ', $method)),
        };
    }
}

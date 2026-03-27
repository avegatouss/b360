<?php

namespace Modules\Eshop360\Http\Controllers\Sales;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Coupon;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\GiftCard;
use Modules\Eshop360\Http\Controllers\Traits\ResolvesPosContext;
use Modules\Eshop360\Services\CashRegisterService;
use Modules\Eshop360\Services\EshopSettingsService;
use Modules\Eshop360\Services\FinanceService;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\OrderService;

class CheckoutController extends Controller
{
    use ResolvesPosContext;
    public function __construct(
        private readonly OrderService $orderService,
        private readonly FinanceService $financeService,
    ) {
    }

    public function index(string $slug)
    {
        $instance = CurrentInstance::get();
        $cart = $this->getCart();

        if (empty($cart)) {
            return redirect()->route('eshop360.pos.index', ['slug' => $instance?->slug])
                ->with('error', __('Your cart is empty.'));
        }

        $coupon = $this->getCoupon();
        $cartContext = $this->getCartContext();
        $totals = $this->calculateTotals($cart, $coupon);
        $channelId = $cartContext['channel_id'] ?? null;
        $customers = Customer::where('instance_id', $instance->id)
            ->where('is_active', true)
            ->when($channelId, fn ($q, $id) => $q->where('channel_id', $id))
            ->orderBy('name')
            ->get();
        $contextChannel = isset($cartContext['channel_id'])
            ? DistributionChannel::find($cartContext['channel_id'])
            : null;

        return view('eshop360::sales.checkout', compact('cart', 'coupon', 'totals', 'customers', 'cartContext', 'contextChannel'));
    }

    public function process(Request $request, string $slug): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $cart = $this->getCart();

        if (empty($cart)) {
            return redirect()->route('eshop360.pos.index', ['slug' => $instance?->slug])
                ->with('error', __('Your cart is empty.'));
        }

        // Block sale if register is required but not open
        $posSettings = app(EshopSettingsService::class)->get('pos');
        if (! empty($posSettings['register_required'])) {
            $currentRegister = app(CashRegisterService::class)->getCurrentRegister();
            if (! $currentRegister) {
                return redirect()->route('eshop360.pos.index', ['slug' => $instance?->slug])
                    ->with('error', __('Vous devez ouvrir une caisse avant de valider une vente.'));
            }
        }

        // Block wallet payment if customer account is not enabled
        if ($request->input('payment_method') === 'wallet' && empty($posSettings['customer_account_enabled'])) {
            return back()->withErrors([
                'payment_method' => __('Le paiement par compte client n\'est pas active.'),
            ])->withInput();
        }

        // Require customer when walk-in is disabled
        if (empty($posSettings['allow_walkin_customer']) && ! $request->filled('customer_id')) {
            return redirect()->route('eshop360.pos.index', ['slug' => $instance?->slug])
                ->with('error', __('Veuillez selectionner un client avant de valider la vente.'));
        }

        $validated = $request->validate([
            'customer_id'     => 'nullable|exists:eshop_customers,id',
            'channel_id'      => 'nullable|exists:eshop_distribution_channels,id',
            'customer_name'   => 'required_without:customer_id|nullable|string|max:255',
            'customer_email'  => 'nullable|email|max:255',
            'customer_phone'  => 'nullable|string|max:30',
            'customer_address' => 'nullable|string|max:500',
            'payment_method'  => 'required|in:cash,card,cheque,paypal,bank_transfer,points,deposit,gift_card,wallet,external',
            'gift_card_code'  => 'nullable|string|max:20',
            'paid_amount'     => 'required|numeric|min:0',
            'shipping_amount' => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $cartContext = $this->getCartContext();
        $channelId = $cartContext['channel_id'] ?? $validated['channel_id'] ?? null;

        if ($channelId && ! app(ChannelAccessService::class)->canAccessChannel(auth()->user(), $channelId)) {
            abort(403, 'No access to this channel.');
        }

        $coupon = $this->getCoupon();
        $totals = $this->calculateTotals($cart, $coupon);
        $shippingAmount = (float) ($validated['shipping_amount'] ?? 0);
        $expectedTotal = round($totals['total'] + $shippingAmount, 2);

        if ((float) $validated['paid_amount'] > $expectedTotal) {
            $validated['paid_amount'] = $expectedTotal;
        }

        $customerId = $this->resolveCustomerId($validated);

        // Validate wallet/gift-card before creating order
        if ($validated['payment_method'] === 'wallet') {
            $customer = $customerId ? Customer::find($customerId) : null;
            if (! $customer) {
                return back()->withErrors(['payment_method' => __('Veuillez selectionner un client pour le paiement par compte.')])->withInput();
            }
            $available = (float) $customer->wallet_balance + (float) $customer->credit_limit;
            if ($available < (float) $validated['paid_amount']) {
                return back()->withErrors(['payment_method' => __('Solde insuffisant. Disponible: :amount', [
                    'amount' => number_format($available, 0, ',', ' '),
                ])])->withInput();
            }
        }

        $giftCard = null;
        if ($validated['payment_method'] === 'gift_card' && ! empty($validated['gift_card_code'])) {
            $giftCard = GiftCard::valid()
                ->where('instance_id', $instance?->id)
                ->where('code', strtoupper(trim($validated['gift_card_code'])))
                ->first();

            if (! $giftCard || (float) $giftCard->balance < (float) $validated['paid_amount']) {
                return back()->withErrors(['gift_card_code' => __('Carte cadeau invalide ou solde insuffisant.')])->withInput();
            }
        }

        $order = $this->orderService->createFromItems($cart, array_merge([
            'instance_id' => $instance?->id,
            'customer_id' => $customerId,
            'status' => 'completed',
            'payment_method' => $validated['payment_method'],
            'paid_amount' => (float) $validated['paid_amount'],
            'discount_amount' => (float) $totals['discount'],
            'shipping_amount' => $shippingAmount,
            'coupon_code' => $coupon['code'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'source' => 'pos',
            'biller_id' => auth()->id(),
            'channel_id' => $cartContext['channel_id'] ?? $validated['channel_id'] ?? null,
        ], $this->resolvePosOperationalData()));

        if ($coupon) {
            $orderChannelId = $cartContext['channel_id'] ?? $validated['channel_id'] ?? null;
            Coupon::whereKey($coupon['id'])
                ->visibleToChannel($orderChannelId)
                ->increment('used_count');
        }

        // Debit wallet if payment method is wallet
        if ($validated['payment_method'] === 'wallet' && $customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $this->financeService->debitWallet($customer, (float) $validated['paid_amount'], $order->id);
            }
        }

        // Debit gift card if payment method is gift_card
        if ($giftCard) {
            $this->financeService->useGiftCard($giftCard, (float) $validated['paid_amount'], $order->id);
        }

        $this->clearCart();

        return redirect()->route('eshop360.orders.show', [
            'slug' => $instance?->slug,
            'order' => $order,
        ])
            ->with('success', __('Order :number placed successfully.', ['number' => $order->order_number]));
    }

    private function calculateTotals(array $cart, ?array $coupon): array
    {
        $subtotal = 0;
        $tax = 0;

        foreach ($cart as $item) {
            $subtotal += $item['total'];
            $tax += round($item['total'] * ($item['tax_rate'] / 100), 2);
        }

        $discount = 0;
        if ($coupon) {
            if ($coupon['type'] === 'percentage') {
                $discount = round($subtotal * ($coupon['value'] / 100), 2);
            } else {
                $discount = min($coupon['value'], $subtotal);
            }
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax'      => round($tax, 2),
            'discount' => round($discount, 2),
            'total'    => round($subtotal + $tax - $discount, 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveCustomerId(array $validated): ?int
    {
        $customerId = $validated['customer_id'] ?? null;

        if ($customerId) {
            return (int) $customerId;
        }

        if (empty($validated['customer_name'])) {
            return null;
        }

        $instance = CurrentInstance::get();
        $customerCount = Customer::where('instance_id', $instance?->id)->count() + 1;

        $cartContext = $this->getCartContext();

        $customer = Customer::create([
            'instance_id' => $instance?->id,
            'channel_id' => $cartContext['channel_id'] ?? $validated['channel_id'] ?? null,
            'code' => 'CUS-' . str_pad((string) $customerCount, 6, '0', STR_PAD_LEFT),
            'name' => $validated['customer_name'],
            'email' => $validated['customer_email'] ?? null,
            'phone' => $validated['customer_phone'] ?? null,
            'address' => $validated['customer_address'] ?? null,
            'is_active' => true,
        ]);

        return $customer->id;
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

    private function clearCart(): void
    {
        session()->forget($this->scopedCartKey());
        session()->forget($this->scopedCouponKey());
        session()->forget($this->scopedCartContextKey());
        session()->forget('eshop_cart');
        session()->forget('eshop_cart_coupon');
        session()->forget('eshop_cart_context');
    }

    private function scopedCartKey(): string
    {
        $instanceId = CurrentInstance::idOrFail();

        return 'eshop_cart_instance_' . $instanceId;
    }

    private function scopedCouponKey(): string
    {
        $instanceId = CurrentInstance::idOrFail();

        return 'eshop_cart_coupon_instance_' . $instanceId;
    }

    /**
     * @return array{channel_id: int|null}|null
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

    private function scopedCartContextKey(): string
    {
        $instanceId = CurrentInstance::idOrFail();

        return 'eshop_cart_context_instance_' . $instanceId;
    }

    /**
     * @return array<string, int|null>
     */
}

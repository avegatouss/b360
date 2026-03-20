<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Services\CartService;
use Modules\Eshop360\Services\CashRegisterService;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Services\EshopSettingsService;

class ChannelPortalCheckoutController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private CashRegisterService $registerService,
        private EshopSettingsService $settingsService,
    ) {}

    public function process(Request $request)
    {
        $channel = $request->resolved_channel;
        $instance = CurrentInstance::get();
        $cart = CartService::forChannel($channel->id);

        if ($cart->isEmpty()) {
            return back()->with('error', 'Le panier est vide.');
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|integer|exists:eshop_customers,id',
            'payment_method' => 'required|string|in:cash,card,cheque,bank_transfer,wallet,external',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $cartItems = $cart->getCart();
        $coupon = $cart->getCoupon();
        $totals = $cart->calculateTotals();
        $currentRegister = $this->registerService->getCurrentRegister($channel->id);

        $orderData = [
            'instance_id' => $instance->id,
            'channel_id' => $channel->id,
            'customer_id' => $validated['customer_id'] ?? null,
            'warehouse_id' => $channel->warehouse_id,
            'store_id' => $currentRegister?->store_id,
            'cash_register_id' => $currentRegister?->id,
            'payment_method' => $validated['payment_method'],
            'paid_amount' => (float) $validated['paid_amount'],
            'source' => 'channel_portal',
            'notes' => $validated['notes'] ?? null,
            'biller_id' => auth()->id(),
        ];

        $order = $this->orderService->createFromItems($cartItems, $orderData, $coupon, $totals);

        $cart->clear();

        return redirect()->route('eshop360.channel-portal.orders.show', [
            $instance->slug, $channel->slug ?? $channel->id, $order->id,
        ])->with('success', 'Commande créée avec succès.');
    }
}

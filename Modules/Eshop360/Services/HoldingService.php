<?php

namespace Modules\Eshop360\Services;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Holding;
use Illuminate\Support\Str;

class HoldingService
{
    /**
     * Create a holding from current cart
     */
    public function createFromCart(CartService $cart, int $instanceId, ?int $customerId = null, ?string $notes = null, ?int $channelId = null): Holding
    {
        return $this->createFromSnapshot(
            $cart->getCart(),
            null,
            null,
            [
                'subtotal' => $cart->getSubtotal(),
                'tax_amount' => $cart->getTax(),
                'discount_amount' => $cart->getDiscount(),
                'total' => $cart->getTotal(),
            ],
            $instanceId,
            $customerId,
            $notes,
            $channelId,
        );
    }

    /**
     * @param  array<string, array<string, mixed>>  $cart
     * @param  array<string, mixed>|null  $coupon
     * @param  array<string, mixed>|null  $context
     * @param  array{subtotal: float, tax_amount: float, discount_amount: float, total: float}  $totals
     */
    public function createFromSnapshot(
        array $cart,
        ?array $coupon,
        ?array $context,
        array $totals,
        int $instanceId,
        ?int $customerId = null,
        ?string $notes = null,
        ?int $channelId = null,
    ): Holding {
        return Holding::create([
            'instance_id' => $instanceId,
            'channel_id' => $channelId,
            'customer_id' => $customerId,
            'reference' => 'HLD-' . strtoupper(Str::random(6)),
            'items' => [
                'lines' => $cart,
                'coupon' => $coupon,
                'context' => $context,
            ],
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax_amount'],
            'discount_amount' => $totals['discount_amount'],
            'total' => $totals['total'],
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Restore holding to cart
     */
    public function restoreToCart(Holding $holding, CartService $cart): void
    {
        $this->restoreSnapshotToSession($holding);
    }

    /**
     * @return array{cart: array<string, array<string, mixed>>, coupon: array<string, mixed>|null, context: array<string, mixed>|null}
     */
    public function restoreSnapshotToSession(Holding $holding): array
    {
        $snapshot = $this->extractSnapshot($holding);

        $this->storeSnapshotInSession($snapshot['cart'], $snapshot['coupon'], $snapshot['context']);
        $holding->delete();

        return $snapshot;
    }

    /**
     * Get all active holdings for an instance
     */
    public function getActiveHoldings(int $instanceId, ?int $channelId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Holding::where('instance_id', $instanceId)
            ->with('customer')
            ->latest();

        if ($channelId) {
            $query->where('channel_id', $channelId);
        } else {
            $query->whereNull('channel_id');
        }

        return $query->get();
    }

    /**
     * @return array{cart: array<string, array<string, mixed>>, coupon: array<string, mixed>|null, context: array<string, mixed>|null}
     */
    private function extractSnapshot(Holding $holding): array
    {
        $items = $holding->items ?? [];

        if (isset($items['lines']) && is_array($items['lines'])) {
            return [
                'cart' => $items['lines'],
                'coupon' => isset($items['coupon']) && is_array($items['coupon']) ? $items['coupon'] : null,
                'context' => isset($items['context']) && is_array($items['context']) ? $items['context'] : null,
            ];
        }

        return [
            'cart' => is_array($items) ? $items : [],
            'coupon' => null,
            'context' => null,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $cart
     * @param  array<string, mixed>|null  $coupon
     * @param  array<string, mixed>|null  $context
     */
    private function storeSnapshotInSession(array $cart, ?array $coupon, ?array $context): void
    {
        $instanceId = CurrentInstance::idOrFail();

        session()->put('eshop_cart', $cart);
        session()->put('eshop_cart_instance_' . $instanceId, $cart);

        if ($coupon !== null) {
            session()->put('eshop_cart_coupon', $coupon);
            session()->put('eshop_cart_coupon_instance_' . $instanceId, $coupon);
        } else {
            session()->forget([
                'eshop_cart_coupon',
                'eshop_cart_coupon_instance_' . $instanceId,
            ]);
        }

        if ($context !== null) {
            session()->put('eshop_cart_context', $context);
            session()->put('eshop_cart_context_instance_' . $instanceId, $context);
        } else {
            session()->forget([
                'eshop_cart_context',
                'eshop_cart_context_instance_' . $instanceId,
            ]);
        }
    }
}

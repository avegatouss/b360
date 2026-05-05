<?php

namespace Modules\Eshop360\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Events\ReportDataChanged;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Models\Payment;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\ProductVariation;

class OrderService
{
    /**
     * Create an order from the current cart contents.
     */
    public function createFromCart(
        CartService $cart,
        array $billingData,
        string $paymentMethod,
        ?int $customerId = null,
    ): Order {
        $order = $this->createFromItems(
            array_values($cart->getCart()),
            [
                'customer_id' => $customerId,
                'status' => $billingData['status'] ?? 'pending',
                'payment_method' => $paymentMethod,
                'paid_amount' => (float) ($billingData['paid_amount'] ?? 0),
                'discount_amount' => (float) ($billingData['discount_amount'] ?? 0),
                'shipping_amount' => (float) ($billingData['shipping_amount'] ?? 0),
                'coupon_code' => $billingData['coupon_code'] ?? null,
                'notes' => $billingData['notes'] ?? null,
                'source' => $billingData['source'] ?? 'pos',
                'biller_id' => auth()->id(),
            ],
        );

        $cart->clear();

        return $order;
    }

    /**
     * Create an order from normalized items.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $orderData
     */
    public function createFromItems(array $items, array $orderData, bool $adjustStock = true): Order
    {
        $instance = CurrentInstance::get();
        $stockService = app(StockService::class);
        $pricingService = app(ProductPricingService::class);

        return DB::transaction(function () use ($items, $orderData, $instance, $stockService, $adjustStock, $pricingService) {
            $channel = $this->resolveChannel($orderData, $orderData['instance_id'] ?? $instance?->id);
            $normalizedItems = array_map(
                fn (array $item): array => $this->normalizeItem($item, $pricingService, $channel?->id),
                $items
            );

            $subtotal = collect($normalizedItems)->sum('line_subtotal');
            $taxAmount = collect($normalizedItems)->sum('line_tax');
            $lineDiscountAmount = collect($normalizedItems)->sum('line_discount');
            $orderLevelDiscount = (float) ($orderData['discount_amount'] ?? 0);
            $shippingAmount = (float) ($orderData['shipping_amount'] ?? 0);
            $totalDiscount = round($lineDiscountAmount + $orderLevelDiscount, 2);
            $total = round($subtotal + $taxAmount - $totalDiscount + $shippingAmount, 2);
            $targetPaidAmount = round((float) ($orderData['paid_amount'] ?? 0), 2);

            $prefix = (string) ($orderData['number_prefix'] ?? 'ORD');
            $orderNumber = $orderData['order_number'] ?? $this->generateOrderNumber($prefix);
            $order = null;

            for ($attempt = 1; $attempt <= self::MAX_NUMBER_ATTEMPTS; $attempt++) {
                try {
                    $order = Order::create([
                        'instance_id' => $orderData['instance_id'] ?? $instance?->id,
                        'customer_id' => $orderData['customer_id'] ?? null,
                        'order_number' => $orderNumber,
                        'status' => $orderData['status'] ?? 'pending',
                        'payment_status' => $this->resolvePaymentStatus($targetPaidAmount, $total),
                        'payment_method' => $orderData['payment_method'] ?? null,
                        'store_id' => $orderData['store_id'] ?? null,
                        'warehouse_id' => $orderData['warehouse_id'] ?? null,
                        'cash_register_id' => $orderData['cash_register_id'] ?? null,
                        'holding_id' => $orderData['holding_id'] ?? null,
                        'channel_id' => $channel?->id,
                        'payment_terms' => $orderData['payment_terms'] ?? null,
                        'delivery_date' => $orderData['delivery_date'] ?? null,
                        'delivered_at' => $orderData['delivered_at'] ?? null,
                        'subtotal' => round($subtotal, 2),
                        'tax_amount' => round($taxAmount, 2),
                        'discount_amount' => $totalDiscount,
                        'shipping_amount' => round($shippingAmount, 2),
                        'total' => $total,
                        'paid_amount' => $targetPaidAmount,
                        'due_amount' => round(max(0, $total - $targetPaidAmount), 2),
                        'coupon_code' => $orderData['coupon_code'] ?? null,
                        'notes' => $orderData['notes'] ?? null,
                        'source' => $orderData['source'] ?? 'manual',
                        'biller_id' => $orderData['biller_id'] ?? auth()->id(),
                    ]);

                    break; // Success — exit retry loop
                } catch (QueryException $e) {
                    // MySQL error 1062 = duplicate entry
                    if ($attempt >= self::MAX_NUMBER_ATTEMPTS || (int) $e->errorInfo[1] !== 1062) {
                        throw $e;
                    }

                    // Regenerate order number and retry
                    $orderNumber = $this->generateOrderNumber($prefix);
                }
            }

            foreach ($normalizedItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'variation_id' => $item['variation_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'variation_name' => $item['variation_name'] ?? null,
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['line_discount'],
                    'tax' => $item['line_tax'],
                    'total' => $item['line_total'],
                ]);

                if ($adjustStock) {
                    $stockService->adjustStock(
                        $item['product'],
                        $orderData['warehouse_id'] ?? null,
                        $item['quantity'],
                        'out',
                        'Order #'.$order->order_number,
                        auth()->id(),
                        Order::class,
                        $order->id,
                    );
                }
            }

            if ($targetPaidAmount !== 0.0) {
                $this->syncPaidAmount(
                    $order,
                    $targetPaidAmount,
                    (string) ($orderData['payment_method'] ?? 'cash'),
                    'ORD-PAY'
                );
            } else {
                $this->calculateTotals($order);
            }

            $this->syncMarginArtifacts($order);

            // Multi-currency snapshot (if feature enabled for this instance)
            $this->snapshotCurrencyIfEnabled($order);

            ReportDataChanged::dispatch($order->instance_id, 'sales');

            // Dispatch webhook
            app(WebhookService::class)->dispatch('order.created', [
                'id' => $order->id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total' => (float) $order->total,
                'customer_id' => $order->customer_id,
                'source' => $order->source,
            ]);

            return $order->fresh(['items', 'payments', 'channel', 'channelMarginLogs']);
        });
    }

    /**
     * Update the status of an order.
     */
    public function updateStatus(Order $order, string $status): void
    {
        $order->update(['status' => $status]);

        if ($status === 'completed') {
            app(\Modules\Eshop360\Services\HRService::class)->calculateCommissionForSale($order);
        }
    }

    /**
     * Record a payment against an order.
     */
    public function processPayment(Order $order, float $amount, string $method): Payment
    {
        $instance = CurrentInstance::get();

        $payment = $order->payments()->create([
            'instance_id' => $instance?->id,
            'amount' => $amount,
            'method' => $method,
            'reference' => Str::upper(Str::random(12)),
            'status' => 'completed',
            'received_by' => auth()->id(),
        ]);

        $this->calculateTotals($order);

        return $payment;
    }

    /**
     * Synchronize an order to a target paid amount by creating a delta payment.
     */
    public function syncPaidAmount(
        Order $order,
        float $targetPaidAmount,
        ?string $method = null,
        string $referencePrefix = 'ORD-ADJ',
        ?string $notes = null,
    ): ?Payment {
        $currentPaidAmount = (float) $order->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $delta = round($targetPaidAmount - $currentPaidAmount, 2);

        if ($delta === 0.0) {
            $this->calculateTotals($order);

            return null;
        }

        $instance = CurrentInstance::get();

        $payment = $order->payments()->create([
            'instance_id' => $order->instance_id ?? $instance?->id,
            'amount' => $delta,
            'method' => $method ?? $order->payment_method ?? 'cash',
            'reference' => $referencePrefix.'-'.$order->id.'-'.($order->payments()->count() + 1),
            'status' => 'completed',
            'notes' => $notes ?? 'Payment adjustment',
            'received_by' => auth()->id(),
        ]);

        $this->calculateTotals($order);

        return $payment;
    }

    /**
     * Recalculate paid/due amounts and update payment status.
     */
    public function calculateTotals(Order $order): void
    {
        $paidAmount = (float) $order->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $dueAmount = (float) $order->total - $paidAmount;

        $order->update([
            'paid_amount' => round($paidAmount, 2),
            'due_amount' => round(max(0, $dueAmount), 2),
            'payment_status' => $this->resolvePaymentStatus($paidAmount, (float) $order->total),
        ]);
    }

    /**
     * Generate a unique order number (e.g. ORD-20260312-A1B2C3).
     *
     * Uniqueness is enforced by a DB unique index. The method retries
     * up to MAX_NUMBER_ATTEMPTS times on duplicate key collisions.
     */
    public function generateOrderNumber(string $prefix = 'ORD'): string
    {
        $date = now()->format('Ymd');
        $random = Str::upper(Str::random(6));

        return "{$prefix}-{$date}-{$random}";
    }

    private const MAX_NUMBER_ATTEMPTS = 5;

    /**
     * @param  array<string, mixed>  $item
     * @return array{
     *     product: Product,
     *     product_name: string,
     *     sku: string,
     *     quantity: int,
     *     unit_price: float,
     *     line_subtotal: float,
     *     line_discount: float,
     *     line_tax: float,
     *     line_total: float
     * }
     */
    private function normalizeItem(
        array $item,
        ProductPricingService $pricingService,
        ?int $channelId = null,
    ): array {
        $product = Product::findOrFail($item['product_id']);
        $variationId = $item['variation_id'] ?? null;
        $variation = $variationId ? ProductVariation::find($variationId) : null;
        $quantity = max(1, (int) ($item['quantity'] ?? 1));
        $pricing = $pricingService->resolve($product, $channelId);

        // Variation price takes precedence over channel/product pricing
        // Use ?: (truthy) instead of !== null to avoid treating price=0 as a valid override
        $unitPrice = $variation && $variation->price
            ? round((float) $variation->price, 2)
            : round((float) ($item['unit_price'] ?? $item['price'] ?? $pricing['unit_price']), 2);
        $lineSubtotal = $this->usesPrediscountedUnitPrice($item, $unitPrice)
            ? round((float) $item['original_price'] * $quantity, 2)
            : round($unitPrice * $quantity, 2);
        $taxRate = (float) ($item['tax_rate'] ?? $product->tax_rate ?? 0);
        $lineDiscount = round($this->resolveLineDiscount($item, $unitPrice, $quantity), 2);
        $taxableAmount = $this->usesPrediscountedUnitPrice($item, $unitPrice)
            ? round($unitPrice * $quantity, 2)
            : round(max(0, $lineSubtotal - $lineDiscount), 2);
        $lineTax = array_key_exists('tax', $item)
            ? round((float) $item['tax'], 2)
            : round($taxableAmount * ($taxRate / 100), 2);
        $lineTotal = round($taxableAmount + $lineTax, 2);

        return [
            'product' => $product,
            'variation_id' => $variation?->id,
            'product_name' => (string) ($item['product_name'] ?? $item['name'] ?? $product->name),
            'variation_name' => $variation?->name ?? ($item['variation_name'] ?? null),
            'sku' => (string) ($item['sku'] ?? $variation?->sku ?? $product->sku ?? ''),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_subtotal' => $lineSubtotal,
            'line_discount' => $lineDiscount,
            'line_tax' => $lineTax,
            'line_total' => $lineTotal,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function resolveLineDiscount(array $item, float $unitPrice, int $quantity): float
    {
        if (array_key_exists('discount', $item) && $item['discount'] !== null) {
            return (float) $item['discount'];
        }

        if (
            array_key_exists('original_price', $item)
            && (float) $item['original_price'] > $unitPrice
        ) {
            return ((float) $item['original_price'] - $unitPrice) * $quantity;
        }

        $discountValue = (float) ($item['discount_value'] ?? 0);
        $discountType = $item['discount_type'] ?? null;

        if ($discountValue <= 0 || ! is_string($discountType)) {
            return 0;
        }

        if ($discountType === 'percentage') {
            return ($unitPrice * $quantity) * ($discountValue / 100);
        }

        if (in_array($discountType, ['fixed', 'amount'], true)) {
            return $discountValue * $quantity;
        }

        return 0;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function usesPrediscountedUnitPrice(array $item, float $unitPrice): bool
    {
        return array_key_exists('original_price', $item)
            && (float) $item['original_price'] > $unitPrice
            && ! array_key_exists('discount', $item)
            && ! array_key_exists('discount_type', $item);
    }

    private function resolvePaymentStatus(float $paidAmount, float $totalAmount): string
    {
        if ($paidAmount >= $totalAmount && $totalAmount > 0) {
            return 'paid';
        }

        if ($paidAmount > 0) {
            return 'partial';
        }

        return 'unpaid';
    }

    /**
     * @param  array<string, mixed>  $orderData
     */
    private function resolveChannel(array $orderData, ?int $instanceId): ?DistributionChannel
    {
        $channelId = $orderData['channel_id'] ?? null;

        if ($channelId === null || $channelId === '') {
            return null;
        }

        return DistributionChannel::query()
            ->whereKey($channelId)
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->first();
    }

    private function syncMarginArtifacts(Order $order): void
    {
        app(MarginService::class)->syncOrderMargins($order->loadMissing(['items.product', 'channel']));
    }

    /**
     * Create a currency snapshot on the order if multi-currency is enabled.
     *
     * Delegates to {@see \Modules\Currency\Services\SnapshotService::snapshotIfEnabled()}
     * which centralises the multi-currency activation check, the base/display
     * currency resolution and the polymorphic snapshot creation.
     *
     * Backward compatible: does nothing if Currency module is not loaded.
     */
    private function snapshotCurrencyIfEnabled(Order $order): void
    {
        if (! app()->bound(\Modules\Currency\Services\SnapshotService::class)) {
            return;
        }

        app(\Modules\Currency\Services\SnapshotService::class)->snapshotIfEnabled(
            $order,
            $order->instance_id,
            $order->biller_id ?? auth()->id(),
        );
    }
}

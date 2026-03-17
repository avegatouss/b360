<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\OnlineOrder;
use Modules\Eshop360\Models\OnlineOrderItem;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Illuminate\Support\Str;

class OnlineOrderService
{
    /**
     * Create an online order from cart data
     */
    public function createOrder(
        int $instanceId,
        int $customerId,
        array $items,
        ?string $deliveryAddress = null,
        ?string $notes = null,
        ?int $channelId = null,
    ): OnlineOrder
    {
        return DB::transaction(function () use ($instanceId, $customerId, $items, $deliveryAddress, $notes, $channelId) {
            $subtotal = 0;
            $taxAmount = 0;
            $pricingService = app(ProductPricingService::class);

            $orderItems = [];
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $pricing = $pricingService->resolve($product, $channelId);
                $unitPrice = round((float) ($item['unit_price'] ?? $pricing['unit_price']), 2);
                $total = $unitPrice * $item['quantity'];
                $tax = $total * ($product->tax_rate / 100);
                $subtotal += $total;
                $taxAmount += $tax;
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total' => $total,
                ];
            }

            $order = OnlineOrder::create([
                'instance_id' => $instanceId,
                'customer_id' => $customerId,
                'channel_id' => $channelId,
                'reference' => 'ONL-' . strtoupper(Str::random(8)),
                'status' => 'pending_validation',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'delivery_address' => $deliveryAddress,
                'notes' => $notes,
            ]);

            foreach ($orderItems as $item) {
                $item['online_order_id'] = $order->id;
                OnlineOrderItem::create($item);
            }

            return $order->load('items.product');
        });
    }

    /**
     * Advance order through workflow statuses
     */
    public function advanceStatus(OnlineOrder $order, string $newStatus): OnlineOrder
    {
        $validTransitions = [
            'pending_validation' => ['validated', 'cancelled'],
            'validated' => ['preparing', 'cancelled'],
            'preparing' => ['prepared', 'cancelled'],
            'prepared' => ['shipping'],
            'shipping' => ['delivered'],
            'delivered' => ['received'],
            'received' => ['invoiced'],
        ];

        $allowed = $validTransitions[$order->status] ?? [];
        if (!in_array($newStatus, $allowed)) {
            throw new \InvalidArgumentException("Cannot transition from {$order->status} to {$newStatus}");
        }

        $updates = ['status' => $newStatus];
        if ($newStatus === 'delivered') $updates['delivered_at'] = now();
        if ($newStatus === 'received') $updates['received_at'] = now();
        if ($newStatus === 'validated') $updates['confirmed_at'] = now();

        $order->update($updates);
        return $order->fresh();
    }

    /**
     * Convert online order to regular order for invoicing
     */
    public function convertToOrder(OnlineOrder $onlineOrder, OrderService $orderService): \Modules\Eshop360\Models\Order
    {
        return DB::transaction(function () use ($onlineOrder, $orderService) {
            $existingOrder = Order::where('instance_id', $onlineOrder->instance_id)
                ->where('source', 'online')
                ->where('notes', 'like', '%online order #' . $onlineOrder->reference . '%')
                ->first();

            if ($existingOrder) {
                return $existingOrder->loadMissing('items', 'payments');
            }

            $order = $orderService->createFromItems(
                $onlineOrder->items->map(fn (OnlineOrderItem $item): array => [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'product_name' => $item->product?->name,
                    'sku' => $item->product?->sku,
                ])->all(),
                [
                    'instance_id' => $onlineOrder->instance_id,
                    'customer_id' => $onlineOrder->customer_id,
                    'channel_id' => $onlineOrder->channel_id,
                    'status' => 'completed',
                    'source' => 'online',
                    'notes' => 'From online order #' . $onlineOrder->reference,
                ],
                false,
            );

            $onlineOrder->update(['status' => 'invoiced']);

            return $order;
        });
    }
}

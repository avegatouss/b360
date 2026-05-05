<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\Sales\Models\OnlineOrder;
use Modules\Eshop360\Domain\Sales\Models\OnlineOrderItem;
use Modules\Eshop360\Domain\Sales\Models\Order;
use Modules\Eshop360\Domain\Sales\Models\OrderItem;

final class DemoOrdersSeeder
{
    public function run(int $instanceId): void
    {
        $products = Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->limit(10)
            ->get();

        $customers = Customer::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('code', 'like', 'DEMO-%')
            ->get();

        if ($products->isEmpty() || $customers->isEmpty()) {
            return;
        }

        $this->seedPosOrders($instanceId, $products, $customers);
        $this->seedOnlineOrders($instanceId, $products, $customers);
    }

    public function reset(int $instanceId): void
    {
        $orderIds = Order::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('order_number', 'like', 'DEMO-%')
            ->pluck('id');

        OrderItem::whereIn('order_id', $orderIds)->delete();
        Order::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('order_number', 'like', 'DEMO-%')
            ->forceDelete();

        $onlineIds = OnlineOrder::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('reference', 'like', 'DEMO-%')
            ->pluck('id');

        OnlineOrderItem::whereIn('online_order_id', $onlineIds)->delete();
        OnlineOrder::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('reference', 'like', 'DEMO-%')
            ->delete();
    }

    private function seedPosOrders(int $instanceId, $products, $customers): void
    {
        $orders = [
            [
                'order_number' => 'DEMO-ORD-001',
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'source' => 'pos',
                'notes' => '[DEMO] Vente comptoir matin',
                'items' => [
                    ['product_index' => 0, 'quantity' => 3],
                    ['product_index' => 1, 'quantity' => 2],
                ],
            ],
            [
                'order_number' => 'DEMO-ORD-002',
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'card',
                'source' => 'pos',
                'notes' => '[DEMO] Vente comptoir carte bancaire',
                'items' => [
                    ['product_index' => 2, 'quantity' => 1],
                    ['product_index' => 3, 'quantity' => 5],
                    ['product_index' => 4, 'quantity' => 2],
                ],
            ],
            [
                'order_number' => 'DEMO-ORD-003',
                'status' => 'processing',
                'payment_status' => 'partial',
                'payment_method' => 'cash',
                'source' => 'pos',
                'notes' => '[DEMO] Commande en cours de preparation',
                'items' => [
                    ['product_index' => 5, 'quantity' => 10],
                    ['product_index' => 6, 'quantity' => 4],
                ],
            ],
            [
                'order_number' => 'DEMO-ORD-004',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => 'mobile_money',
                'source' => 'online',
                'notes' => '[DEMO] Commande en ligne en attente de paiement',
                'items' => [
                    ['product_index' => 7, 'quantity' => 2],
                    ['product_index' => 0, 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'DEMO-ORD-005',
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'mobile_money',
                'source' => 'pos',
                'notes' => '[DEMO] Vente Orange Money',
                'items' => [
                    ['product_index' => 1, 'quantity' => 6],
                ],
            ],
            [
                'order_number' => 'DEMO-ORD-006',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => 'cash',
                'source' => 'manual',
                'notes' => '[DEMO] Commande manuelle grossiste',
                'items' => [
                    ['product_index' => 2, 'quantity' => 20],
                    ['product_index' => 3, 'quantity' => 15],
                    ['product_index' => 8, 'quantity' => 8],
                ],
            ],
            [
                'order_number' => 'DEMO-ORD-007',
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'card',
                'source' => 'online',
                'notes' => '[DEMO] Commande web completee',
                'items' => [
                    ['product_index' => 4, 'quantity' => 3],
                    ['product_index' => 9, 'quantity' => 1],
                ],
            ],
        ];

        foreach ($orders as $index => $o) {
            $customer = $customers->values()->get($index % $customers->count());

            $items = $o['items'];
            unset($o['items']);

            $subtotal = 0;
            $itemRows = [];

            foreach ($items as $item) {
                $product = $products->values()->get($item['product_index'] % $products->count());
                if (! $product) {
                    continue;
                }

                $unitPrice = (float) $product->price;
                $qty = $item['quantity'];
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                $itemRows[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku ?? '',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $lineTotal,
                ];
            }

            $paidAmount = $o['payment_status'] === 'paid' ? $subtotal : ($o['payment_status'] === 'partial' ? round($subtotal * 0.5, 2) : 0);

            $order = Order::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'order_number' => $o['order_number']],
                array_merge($o, [
                    'instance_id' => $instanceId,
                    'customer_id' => $customer->id,
                    'subtotal' => $subtotal,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'shipping_amount' => 0,
                    'total' => $subtotal,
                    'paid_amount' => $paidAmount,
                    'due_amount' => $subtotal - $paidAmount,
                ])
            );

            // Delete existing items and re-create
            OrderItem::where('order_id', $order->id)->delete();
            foreach ($itemRows as $row) {
                OrderItem::create(array_merge($row, ['order_id' => $order->id]));
            }
        }
    }

    private function seedOnlineOrders(int $instanceId, $products, $customers): void
    {
        $onlineOrders = [
            [
                'reference' => 'DEMO-WEB-001',
                'status' => 'pending_validation',
                'delivery_address' => 'Cocody, Rue des Jardins, Abidjan',
                'delivery_notes' => 'Appeler avant livraison',
                'notes' => '[DEMO] Commande en ligne en attente de validation',
            ],
            [
                'reference' => 'DEMO-WEB-002',
                'status' => 'preparing',
                'delivery_address' => 'Plateau, Avenue Terrasson, Abidjan',
                'notes' => '[DEMO] Commande en cours de preparation',
                'confirmed_at' => now()->subDays(1),
            ],
            [
                'reference' => 'DEMO-WEB-003',
                'status' => 'delivered',
                'delivery_address' => 'Marcory, Boulevard VGE, Abidjan',
                'notes' => '[DEMO] Commande livree',
                'confirmed_at' => now()->subDays(5),
                'delivered_at' => now()->subDays(2),
            ],
        ];

        foreach ($onlineOrders as $index => $o) {
            $customer = $customers->values()->get($index % $customers->count());

            // Pick 2-3 products for each online order
            $selectedProducts = $products->values()->slice($index * 2, 3);
            $subtotal = 0;
            $itemRows = [];

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 5);
                $unitPrice = (float) $product->price;
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                $itemRows[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ];
            }

            $taxAmount = round($subtotal * 0.18, 2);

            $onlineOrder = OnlineOrder::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'reference' => $o['reference']],
                array_merge($o, [
                    'instance_id' => $instanceId,
                    'customer_id' => $customer->id,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $subtotal + $taxAmount,
                ])
            );

            OnlineOrderItem::where('online_order_id', $onlineOrder->id)->delete();
            foreach ($itemRows as $row) {
                OnlineOrderItem::create(array_merge($row, ['online_order_id' => $onlineOrder->id]));
            }
        }
    }
}

<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\Sales\Models\OnlineOrder;
use Modules\Eshop360\Domain\Sales\Models\OnlineOrderItem;
use Modules\Eshop360\Domain\Sales\Models\Order;
use Modules\Eshop360\Domain\Sales\Models\OrderItem;

final class DemoClientOrdersSeeder
{
    public function run(int $instanceId): void
    {
        $customers = Customer::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->whereNotNull('user_id')
            ->get();

        if ($customers->isEmpty()) {
            return;
        }

        $products = Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('is_active', true)
            ->limit(30)
            ->get();

        if ($products->count() < 5) {
            return;
        }

        foreach ($customers as $customer) {
            $this->seedCustomerOrders($instanceId, $customer, $products);
        }
    }

    public function reset(int $instanceId): void
    {
        // Delete demo regular orders
        $orderIds = Order::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('notes', 'like', '[DEMO-CLIENT]%')
            ->pluck('id');
        OrderItem::whereIn('order_id', $orderIds)->delete();
        Order::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('notes', 'like', '[DEMO-CLIENT]%')
            ->forceDelete();

        // Delete demo online orders
        $onlineIds = OnlineOrder::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('notes', 'like', '[DEMO-CLIENT]%')
            ->pluck('id');
        OnlineOrderItem::whereIn('online_order_id', $onlineIds)->delete();
        OnlineOrder::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('notes', 'like', '[DEMO-CLIENT]%')
            ->delete();
    }

    private function seedCustomerOrders(int $instanceId, Customer $customer, $products): void
    {
        // Generate orders for each of the last 3 months
        for ($monthsAgo = 0; $monthsAgo < 3; $monthsAgo++) {
            $baseDate = now()->subMonths($monthsAgo);

            // 3-5 regular orders (POS/manual) per month
            $orderCount = rand(3, 5);
            for ($i = 0; $i < $orderCount; $i++) {
                $this->createRegularOrder($instanceId, $customer, $products, $baseDate, $i);
            }

            // 1-2 online orders per month
            $onlineCount = rand(1, 2);
            for ($i = 0; $i < $onlineCount; $i++) {
                $this->createOnlineOrder($instanceId, $customer, $products, $baseDate, $i);
            }
        }
    }

    private function createRegularOrder(int $instanceId, Customer $customer, $products, $baseDate, int $index): void
    {
        $date = $baseDate->copy()->subDays(rand(0, 25))->setHour(rand(8, 18))->setMinute(rand(0, 59));
        $source = $index % 2 === 0 ? 'pos' : 'manual';
        $paymentMethods = ['cash', 'card', 'bank_transfer', 'wallet'];
        $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

        // Pick 2-5 random products
        $itemCount = rand(2, 5);
        $selectedProducts = $products->random(min($itemCount, $products->count()));

        $subtotal = 0;
        $itemsData = [];
        foreach ($selectedProducts as $product) {
            $qty = rand(1, 20);
            $unitPrice = (float) $product->price;
            $discount = rand(0, 1) ? round($unitPrice * rand(5, 15) / 100, 2) : 0;
            $tax = round(($unitPrice - $discount) * $qty * 0.18, 2);
            $total = round(($unitPrice - $discount) * $qty, 2);
            $subtotal += $total;
            $itemsData[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount' => $discount * $qty,
                'tax' => $tax,
                'total' => $total,
            ];
        }

        $taxAmount = round($subtotal * 0.18, 2);
        $discountAmount = collect($itemsData)->sum('discount');
        $total = round($subtotal + $taxAmount, 2);

        // Some orders partially paid, most fully paid
        $isPaid = rand(1, 10) > 2; // 80% fully paid
        $paidAmount = $isPaid ? $total : round($total * rand(30, 80) / 100, 2);
        $dueAmount = round($total - $paidAmount, 2);
        $paymentStatus = $dueAmount <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid');

        $orderNumber = 'DEMO-CLI-C'.$customer->id.'-'.$date->format('ymdHi').'-'.$index;

        $order = Order::withoutGlobalScopes()->create([
            'instance_id' => $instanceId,
            'customer_id' => $customer->id,
            'order_number' => $orderNumber,
            'status' => 'completed',
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'source' => $source,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'shipping_amount' => 0,
            'total' => $total,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'notes' => '[DEMO-CLIENT] Achat '.($source === 'pos' ? 'au comptoir' : 'manuel'),
            'biller_id' => 1,
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        foreach ($itemsData as $item) {
            OrderItem::create(array_merge($item, ['order_id' => $order->id]));
        }
    }

    private function createOnlineOrder(int $instanceId, Customer $customer, $products, $baseDate, int $index): void
    {
        $date = $baseDate->copy()->subDays(rand(0, 25))->setHour(rand(8, 22))->setMinute(rand(0, 59));

        // Different statuses for variety
        $statuses = ['received', 'delivered', 'shipping', 'validated', 'pending_validation'];
        $statusWeights = [40, 20, 15, 15, 10]; // weighted random
        $statusIndex = $this->weightedRandom($statusWeights);
        $status = $statuses[$statusIndex];

        $itemCount = rand(2, 6);
        $selectedProducts = $products->random(min($itemCount, $products->count()));

        $subtotal = 0;
        $itemsData = [];
        foreach ($selectedProducts as $product) {
            $qty = rand(5, 50);
            $unitPrice = (float) $product->price;
            $total = $unitPrice * $qty;
            $subtotal += $total;
            $itemsData[] = [
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total' => $total,
            ];
        }

        $taxAmount = round($subtotal * 0.18, 2);
        $totalAmount = round($subtotal + $taxAmount, 2);
        $reference = 'DEMO-ONL-C'.$customer->id.'-'.$date->format('ymdHi').'-'.$index;

        $confirmedAt = in_array($status, ['validated', 'preparing', 'shipping', 'delivered', 'received', 'invoiced']) ? $date->copy()->addHours(rand(1, 48)) : null;
        $deliveredAt = in_array($status, ['delivered', 'received']) ? $date->copy()->addDays(rand(2, 7)) : null;
        $receivedAt = $status === 'received' ? $deliveredAt?->copy()->addDays(rand(0, 2)) : null;

        $onlineOrder = OnlineOrder::withoutGlobalScopes()->create([
            'instance_id' => $instanceId,
            'customer_id' => $customer->id,
            'reference' => $reference,
            'status' => $status,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $totalAmount,
            'delivery_address' => $customer->address ?? 'Abidjan, Cote d\'Ivoire',
            'notes' => '[DEMO-CLIENT] Commande en ligne',
            'confirmed_at' => $confirmedAt,
            'delivered_at' => $deliveredAt,
            'received_at' => $receivedAt,
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        foreach ($itemsData as $item) {
            OnlineOrderItem::create(array_merge($item, ['online_order_id' => $onlineOrder->id]));
        }
    }

    private function weightedRandom(array $weights): int
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $sum = 0;
        foreach ($weights as $i => $w) {
            $sum += $w;
            if ($rand <= $sum) {
                return $i;
            }
        }

        return 0;
    }
}

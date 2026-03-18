<?php

namespace Modules\Eshop360\Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\ChannelMarginLog;
use Modules\Eshop360\Models\ChannelUser;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Models\Product;

/**
 * Demo seeder: channel portal users + channel orders with margin calculations.
 * Depends on DemoChannelsSeeder (creates [DEMO] CODIFARM and [DEMO] PHARMAPLUS).
 */
final class DemoChannelPortalSeeder
{
    public function run(int $instanceId): void
    {
        $channels = DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')
            ->get();

        if ($channels->isEmpty()) {
            return;
        }

        $products = Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('is_active', true)
            ->limit(10)
            ->get();

        if ($products->count() < 3) {
            return;
        }

        foreach ($channels as $channel) {
            $this->seedChannelUsers($instanceId, $channel);
            $this->seedChannelOrders($instanceId, $channel, $products);
        }
    }

    public function reset(int $instanceId): void
    {
        $channelIds = DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')
            ->pluck('id');

        // Remove channel orders
        $orderIds = Order::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('order_number', 'like', 'DEMO-CH-%')
            ->pluck('id');

        OrderItem::whereIn('order_id', $orderIds)->delete();
        ChannelMarginLog::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->whereIn('order_id', $orderIds)
            ->delete();
        Order::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('order_number', 'like', 'DEMO-CH-%')
            ->forceDelete();

        // Remove channel portal users
        $portalUsers = ChannelUser::whereIn('channel_id', $channelIds)
            ->get();

        foreach ($portalUsers as $cu) {
            // Only remove demo users (check email pattern)
            $user = User::find($cu->user_id);
            if ($user && str_contains($user->email, 'demo-channel-')) {
                DB::connection('system')->table('instance_user')
                    ->where('user_id', $user->id)
                    ->where('instance_id', $instanceId)
                    ->delete();
                $user->forceDelete();
            }
        }

        ChannelUser::whereIn('channel_id', $channelIds)->delete();
    }

    private function seedChannelUsers(int $instanceId, DistributionChannel $channel): void
    {
        $slug = $channel->slug;

        $users = [
            [
                'email' => "demo-channel-manager-{$slug}@b360.test",
                'full_name' => "[DEMO] Gérant {$channel->name}",
                'role' => 'manager',
            ],
            [
                'email' => "demo-channel-agent-{$slug}@b360.test",
                'full_name' => "[DEMO] Agent {$channel->name}",
                'role' => 'agent',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'full_name' => $data['full_name'],
                    'password' => 'password',
                    'is_active' => true,
                    'is_blocked' => false,
                ]
            );

            DB::connection('system')->table('instance_user')->updateOrInsert(
                ['user_id' => $user->id, 'instance_id' => $instanceId],
                ['status' => 'active', 'created_at' => now(), 'updated_at' => now()]
            );

            ChannelUser::updateOrCreate(
                ['channel_id' => $channel->id, 'user_id' => $user->id],
                ['role' => $data['role']]
            );
        }
    }

    private function seedChannelOrders(int $instanceId, DistributionChannel $channel, $products): void
    {
        $customers = Customer::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('is_active', true)
            ->limit(5)
            ->get();

        if ($customers->isEmpty()) {
            return;
        }

        $prefix = $channel->slug === 'demo-codifarm' ? 'DEMO-CH-CDF' : 'DEMO-CH-PHP';

        $orders = [
            // Completed sale with full margin calculation
            [
                'order_number' => "{$prefix}-001",
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'bank_transfer',
                'source' => 'channel_portal',
                'notes' => "[DEMO] Vente canal {$channel->name} - completee",
                'customer_index' => 0,
                'items' => [
                    ['index' => 0, 'qty' => 30],
                    ['index' => 1, 'qty' => 20],
                ],
                'days_ago' => 15,
            ],
            [
                'order_number' => "{$prefix}-002",
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'source' => 'channel_portal',
                'notes' => "[DEMO] Vente canal {$channel->name} - completee",
                'customer_index' => 1,
                'items' => [
                    ['index' => 2, 'qty' => 15],
                    ['index' => 3, 'qty' => 25],
                    ['index' => 4, 'qty' => 10],
                ],
                'days_ago' => 8,
            ],
            [
                'order_number' => "{$prefix}-003",
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'bank_transfer',
                'source' => 'channel_portal',
                'notes' => "[DEMO] Grosse commande canal {$channel->name}",
                'customer_index' => 2,
                'items' => [
                    ['index' => 0, 'qty' => 100],
                    ['index' => 5, 'qty' => 50],
                ],
                'days_ago' => 3,
            ],
            // Processing order
            [
                'order_number' => "{$prefix}-004",
                'status' => 'processing',
                'payment_status' => 'partial',
                'payment_method' => 'cash',
                'source' => 'channel_portal',
                'notes' => "[DEMO] Commande en cours - {$channel->name}",
                'customer_index' => 3,
                'items' => [
                    ['index' => 6, 'qty' => 40],
                    ['index' => 7, 'qty' => 20],
                ],
                'days_ago' => 1,
            ],
            // Pending order
            [
                'order_number' => "{$prefix}-005",
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => 'bank_transfer',
                'source' => 'channel_portal',
                'notes' => "[DEMO] Nouvelle commande canal {$channel->name}",
                'customer_index' => 4 % $customers->count(),
                'items' => [
                    ['index' => 1, 'qty' => 50],
                    ['index' => 8, 'qty' => 30],
                    ['index' => 9, 'qty' => 20],
                ],
                'days_ago' => 0,
            ],
        ];

        foreach ($orders as $o) {
            $customer = $customers->values()->get($o['customer_index'] % $customers->count());
            $itemRows = [];
            $subtotal = 0;

            foreach ($o['items'] as $item) {
                $product = $products->values()->get($item['index'] % $products->count());
                if (!$product) continue;

                // Use channel pricing: PGHT * (1 + buy_rate)
                $pght = (float) ($product->sale_price ?? $product->price ?? 0);
                $unitPrice = $channel->calculateSalePrice($pght);
                $lineTotal = round($unitPrice * $item['qty'], 2);
                $subtotal += $lineTotal;

                $itemRows[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku ?? '',
                    'quantity' => $item['qty'],
                    'unit_price' => $unitPrice,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $lineTotal,
                ];
            }

            $paidAmount = match ($o['payment_status']) {
                'paid' => $subtotal,
                'partial' => round($subtotal * 0.6, 2),
                default => 0,
            };

            $createdAt = now()->subDays($o['days_ago']);

            $order = Order::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'order_number' => $o['order_number']],
                [
                    'instance_id' => $instanceId,
                    'customer_id' => $customer->id,
                    'channel_id' => $channel->id,
                    'order_number' => $o['order_number'],
                    'status' => $o['status'],
                    'payment_status' => $o['payment_status'],
                    'payment_method' => $o['payment_method'],
                    'source' => $o['source'],
                    'subtotal' => $subtotal,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'shipping_amount' => 0,
                    'total' => $subtotal,
                    'paid_amount' => $paidAmount,
                    'due_amount' => max(0, $subtotal - $paidAmount),
                    'notes' => $o['notes'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );

            // Items
            OrderItem::where('order_id', $order->id)->delete();
            foreach ($itemRows as $row) {
                OrderItem::create(array_merge($row, ['order_id' => $order->id]));
            }

            // Calculate and log margins for completed orders
            if ($o['status'] === 'completed') {
                $this->logMargin($instanceId, $channel, $order, $itemRows, $products);
            }
        }
    }

    private function logMargin(int $instanceId, DistributionChannel $channel, Order $order, array $itemRows, $products): void
    {
        // Delete existing margin log for this order
        ChannelMarginLog::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('channel_id', $channel->id)
            ->where('order_id', $order->id)
            ->delete();

        // Calculate total margin: sum of (unit_price - PGHT) * qty for each item
        $totalMargin = 0;
        foreach ($itemRows as $row) {
            $product = $products->firstWhere('id', $row['product_id']);
            if (!$product) continue;

            $pght = (float) ($product->sale_price ?? $product->price ?? 0);
            $marginPerUnit = $row['unit_price'] - $pght;
            $totalMargin += $marginPerUnit * $row['quantity'];
        }

        $totalMargin = round($totalMargin, 2);
        if ($totalMargin <= 0) return;

        $debtPart = round($totalMargin * (float) $channel->debt_share, 2);
        $channelPart = round($totalMargin * (float) $channel->channel_share, 2);
        $ownerPart = $totalMargin - $debtPart - $channelPart; // remainder to avoid rounding issues

        ChannelMarginLog::withoutGlobalScopes()->create([
            'instance_id' => $instanceId,
            'channel_id' => $channel->id,
            'order_id' => $order->id,
            'total_margin' => $totalMargin,
            'debt_part' => $debtPart,
            'channel_part' => $channelPart,
            'owner_part' => $ownerPart,
        ]);
    }
}

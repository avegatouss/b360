<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\StockMovement;

final class ChannelB2BService
{
    private const HUB_CUSTOMER_CODE_PREFIX = 'CHANNEL-HUB-';

    public function __construct(
        private readonly StockService $stockService,
        private readonly MarginService $marginService,
    ) {}

    public function resolveHubCustomer(DistributionChannel $channel, bool $createIfMissing = true): ?Customer
    {
        $customer = Customer::query()
            ->where('instance_id', $channel->instance_id)
            ->whereNull('channel_id')
            ->where('code', $this->hubCustomerCode($channel))
            ->first();

        if ($customer || !$createIfMissing) {
            return $customer;
        }

        return Customer::create([
            'instance_id' => $channel->instance_id,
            'channel_id' => null,
            'code' => $this->hubCustomerCode($channel),
            'name' => $channel->name,
            'company_name' => $channel->name,
            'notes' => 'Compte client hub genere automatiquement pour le canal ' . $channel->name . '.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);
    }

    public function syncHubCustomer(DistributionChannel $channel): Customer
    {
        $customer = $this->resolveHubCustomer($channel, true);

        $customer->update([
            'name' => $channel->name,
            'company_name' => $channel->name,
            'is_active' => (bool) $channel->is_active,
        ]);

        return $customer->fresh();
    }

    public function receiveSupplyOrder(Order $order, DistributionChannel $channel, ?int $performedBy = null): void
    {
        DB::transaction(function () use ($order, $channel, $performedBy) {
            if ($this->hasAlreadyBeenReceived($order, $channel)) {
                throw new \RuntimeException('Cette commande d\'approvisionnement a deja ete receptionnee.');
            }

            if (!$channel->warehouse_id) {
                throw new \RuntimeException('Aucun entrepot n\'est configure pour ce canal.');
            }

            if (!in_array($order->status, ['pending', 'processing'], true)) {
                throw new \RuntimeException('Seules les commandes d\'approvisionnement en attente peuvent etre receptionnees.');
            }

            $order->loadMissing('items.product');

            foreach ($order->items as $item) {
                if (!$item->product) {
                    continue;
                }

                $this->stockService->adjustStock(
                    $item->product,
                    $channel->warehouse_id,
                    (int) $item->quantity,
                    'in',
                    "Approvisionnement canal: Commande #{$order->order_number}",
                    $performedBy,
                    Order::class,
                    $order->id,
                );
            }

            $order->update([
                'status' => 'completed',
                'warehouse_id' => $channel->warehouse_id,
                'delivered_at' => now(),
            ]);

            $this->marginService->syncOrderMargins($order->fresh(['items.product', 'channel']));
        });
    }

    public function buildSupplyOrderNumber(DistributionChannel $channel): string
    {
        return 'SUP-' . strtoupper($channel->slug) . '-' . now()->format('ymdHis');
    }

    private function hasAlreadyBeenReceived(Order $order, DistributionChannel $channel): bool
    {
        return StockMovement::query()
            ->where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('warehouse_id', $channel->warehouse_id)
            ->where('type', 'in')
            ->exists();
    }

    private function hubCustomerCode(DistributionChannel $channel): string
    {
        return self::HUB_CUSTOMER_CODE_PREFIX . $channel->id;
    }
}

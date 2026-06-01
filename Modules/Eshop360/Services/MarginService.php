<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Domain\Channel\Models\ChannelMarginLog;
use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;
use Modules\Eshop360\Domain\Sales\Models\Order;

class MarginService
{
    public function syncOrderMargins(Order $order): void
    {
        $order->loadMissing(['items.product', 'channel']);

        if ($order->channel_id && $order->channel) {
            $this->syncTripartiteMargin($order, $order->channel);
        } else {
            $order->channelMarginLogs()->delete();
        }
    }

    /**
     * Calculate and log tripartite margin distribution for a channel sale.
     * margin = sale_price - PGHT
     * Split: debt, channel, owner (configurable shares per channel)
     */
    public function calculateTripartiteMargin(Order $order, DistributionChannel $channel): ?ChannelMarginLog
    {
        if (! $order->channel_id) {
            return null;
        }

        $totalMargin = $this->calculateTotalMargin($order);

        if ($totalMargin <= 0) {
            return null;
        }

        return DB::transaction(function () use ($order, $channel, $totalMargin) {
            return ChannelMarginLog::create([
                'instance_id' => $order->instance_id,
                'channel_id' => $channel->id,
                'order_id' => $order->id,
                'total_margin' => $totalMargin,
                'debt_part' => round($totalMargin * $channel->debt_share, 2),
                'channel_part' => round($totalMargin * $channel->channel_share, 2),
                'owner_part' => round($totalMargin * $channel->owner_share, 2),
            ]);
        });
    }

    public function syncTripartiteMargin(Order $order, DistributionChannel $channel): ?ChannelMarginLog
    {
        $order->channelMarginLogs()->delete();

        return $this->calculateTripartiteMargin($order, $channel);
    }

    /**
     * Get margin summary for a period, optionally filtered by channel.
     */
    public function getMarginSummary(int $instanceId, ?string $from = null, ?string $to = null, ?int $channelId = null): array
    {
        $query = ChannelMarginLog::where('instance_id', $instanceId);

        if ($channelId) {
            $query->where('channel_id', $channelId);
        }
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return [
            'total_margin' => $query->sum('total_margin'),
            'total_debt' => $query->sum('debt_part'),
            'total_channel' => $query->sum('channel_part'),
            'total_owner' => $query->sum('owner_part'),
            'count' => $query->count(),
        ];
    }

    private function calculateTotalMargin(Order $order): float
    {
        $order->loadMissing('items.product');

        $totalMargin = 0.0;

        foreach ($order->items as $item) {
            $product = $item->product;
            $pght = (float) ($product?->pght ?? 0);

            if ($pght <= 0) {
                continue;
            }

            $marginPerUnit = (float) $item->unit_price - $pght;
            $totalMargin += $marginPerUnit * (int) $item->quantity;
        }

        return round($totalMargin, 2);
    }
}

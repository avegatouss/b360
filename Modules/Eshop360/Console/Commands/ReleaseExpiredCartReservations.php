<?php

namespace Modules\Eshop360\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\PersistentCart;
use Modules\Eshop360\Models\Stock;

/**
 * Release reserved stock from expired persistent carts.
 * Scheduled every 15 minutes to prevent stale reservations.
 */
class ReleaseExpiredCartReservations extends Command
{
    protected $signature = 'eshop:release-expired-carts';
    protected $description = 'Release reserved stock from expired persistent carts';

    public function handle(): int
    {
        $expired = PersistentCart::withoutGlobalScopes()
            ->where('expires_at', '<', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired carts found.');
            return self::SUCCESS;
        }

        $released = 0;

        foreach ($expired as $cart) {
            $items = is_array($cart->items) ? $cart->items : json_decode($cart->items, true);

            if (!is_array($items)) {
                $cart->delete();
                continue;
            }

            DB::transaction(function () use ($items, &$released) {
                foreach ($items as $item) {
                    $productId = $item['product_id'] ?? null;
                    $quantity = $item['quantity'] ?? 0;

                    if (!$productId || $quantity <= 0) {
                        continue;
                    }

                    $stock = Stock::withoutGlobalScopes()
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();

                    if ($stock && $stock->reserved_quantity > 0) {
                        $newReserved = max(0, $stock->reserved_quantity - $quantity);
                        $stock->update(['reserved_quantity' => $newReserved]);
                        $released++;
                    }
                }
            });

            $cart->delete();
        }

        $this->info("Released {$released} stock reservations from {$expired->count()} expired cart(s).");

        return self::SUCCESS;
    }
}

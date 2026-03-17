<?php

namespace Modules\Eshop360\Console\Commands;

use Illuminate\Console\Command;
use App\Instances\Instance;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Notifications\LowStockNotification;

class CheckLowStock extends Command
{
    protected $signature = 'eshop:check-low-stock';
    protected $description = 'Check products with low stock and notify instance admins/managers via in-app notifications';

    public function handle(): int
    {
        $instances = Instance::where('is_active', true)->get();

        foreach ($instances as $instance) {
            $threshold = config('eshop360.stock.low_stock_threshold', 10);

            $lowStockProducts = Product::where('instance_id', $instance->id)
                ->whereHas('stocks', function ($q) use ($threshold) {
                    $q->where('quantity', '<=', $threshold);
                })
                ->with(['stocks.warehouse'])
                ->get();

            if ($lowStockProducts->isEmpty()) {
                continue;
            }

            // Get instance admins and managers
            $admins = $instance->users()
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['instance-admin', 'manager']))
                ->get();

            foreach ($lowStockProducts as $product) {
                foreach ($product->stocks as $stock) {
                    if ($stock->quantity > $threshold) {
                        continue;
                    }

                    $warehouseName = $stock->warehouse?->name ?? $stock->store?->name ?? 'N/A';

                    foreach ($admins as $admin) {
                        $admin->notify(new LowStockNotification(
                            productName: $product->name,
                            productId: $product->id,
                            currentQty: $stock->quantity,
                            alertThreshold: $threshold,
                            warehouseName: $warehouseName,
                            instanceSlug: $instance->slug,
                        ));
                    }
                }
            }

            $this->info("Low stock notifications sent for instance: {$instance->name}");
        }

        return self::SUCCESS;
    }
}

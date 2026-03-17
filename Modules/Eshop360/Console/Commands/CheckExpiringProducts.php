<?php

namespace Modules\Eshop360\Console\Commands;

use Illuminate\Console\Command;
use App\Instances\Instance;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Notifications\ExpiryAlertNotification;

class CheckExpiringProducts extends Command
{
    protected $signature = 'eshop:check-expiry';
    protected $description = 'Check products nearing expiry and notify instance admins/managers via in-app notifications';

    public function handle(): int
    {
        $instances = Instance::where('is_active', true)->get();

        foreach ($instances as $instance) {
            // Query stocks expiring within 30 days (includes 7 days and 0 days)
            $expiringStocks = Stock::where('instance_id', $instance->id)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>=', now()->subDay()) // include just-expired
                ->where('quantity', '>', 0)
                ->with(['product', 'warehouse'])
                ->orderBy('expiry_date')
                ->get();

            if ($expiringStocks->isEmpty()) {
                continue;
            }

            $admins = $instance->users()
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['instance-admin', 'manager']))
                ->get();

            foreach ($expiringStocks as $stock) {
                $daysRemaining = (int) now()->startOfDay()->diffInDays($stock->expiry_date->startOfDay(), false);
                $warehouseName = $stock->warehouse?->name ?? $stock->store?->name ?? 'N/A';

                foreach ($admins as $admin) {
                    $admin->notify(new ExpiryAlertNotification(
                        productName: $stock->product->name,
                        productId: $stock->product_id,
                        expiryDate: $stock->expiry_date->format('d/m/Y'),
                        daysRemaining: max(0, $daysRemaining),
                        warehouseName: $warehouseName,
                        instanceSlug: $instance->slug,
                    ));
                }
            }

            $this->info("Expiry notifications sent for instance: {$instance->name}");
        }

        return self::SUCCESS;
    }
}

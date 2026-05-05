<?php

namespace Modules\Eshop360\Console\Commands;

use App\Instances\Instance;
use Illuminate\Console\Command;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Notifications\ExpiryAlertNotification;

class CheckExpiringProducts extends Command
{
    protected $signature = 'eshop:check-expiry';

    protected $description = 'Check products nearing expiry and notify instance admins/managers via in-app notifications';

    public function handle(): int
    {
        // Check if expiry alerts are enabled in settings
        if (! setting('notifications.alert_expiry_enabled', true)) {
            $this->info('Expiry alerts are disabled in settings.');

            return self::SUCCESS;
        }

        $alertDays = (int) setting('notifications.alert_expiry_days', 30);

        $instances = Instance::where('is_active', true)->get();

        foreach ($instances as $instance) {
            // Query stocks expiring within the configured alert period
            $expiringStocks = Stock::where('instance_id', $instance->id)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays($alertDays))
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

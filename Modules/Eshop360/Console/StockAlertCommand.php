<?php

namespace Modules\Eshop360\Console;

use App\Instances\Instance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Modules\Eshop360\Domain\Catalog\Models\Product;

class StockAlertCommand extends Command
{
    protected $signature = 'eshop360:stock-alerts';

    protected $description = 'Send alerts for low stock products across all instances';

    public function handle(): int
    {
        $instances = Instance::all();

        foreach ($instances as $instance) {
            $threshold = config('eshop360.stock.low_stock_threshold', 10);

            $lowStockProducts = Product::where('instance_id', $instance->id)
                ->whereHas('stocks', function ($q) use ($threshold) {
                    $q->where('quantity', '<=', $threshold)->where('quantity', '>', 0);
                })
                ->with('stocks')
                ->get();

            $outOfStock = Product::where('instance_id', $instance->id)
                ->whereHas('stocks', fn ($q) => $q->where('quantity', '<=', 0))
                ->get();

            if ($lowStockProducts->isEmpty() && $outOfStock->isEmpty()) {
                continue;
            }

            // Get instance admin emails
            $admins = $instance->users()
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['instance-admin', 'manager']))
                ->get();

            foreach ($admins as $admin) {
                if (! $admin->email) {
                    continue;
                }

                Mail::raw(
                    $this->buildAlertMessage($instance, $lowStockProducts, $outOfStock),
                    function ($message) use ($admin, $instance) {
                        $message->to($admin->email)
                            ->subject("[{$instance->name}] Alerte Stock - ".now()->format('d/m/Y'));
                    }
                );
            }

            $this->info("Stock alerts sent for instance: {$instance->name}");
        }

        return self::SUCCESS;
    }

    private function buildAlertMessage($instance, $lowStock, $outOfStock): string
    {
        $msg = "Rapport Stock - {$instance->name}\n";
        $msg .= 'Date: '.now()->format('d/m/Y H:i')."\n\n";

        if ($outOfStock->isNotEmpty()) {
            $msg .= "=== RUPTURE DE STOCK ({$outOfStock->count()}) ===\n";
            foreach ($outOfStock as $p) {
                $msg .= "- {$p->name} (SKU: {$p->sku})\n";
            }
            $msg .= "\n";
        }

        if ($lowStock->isNotEmpty()) {
            $msg .= "=== STOCK FAIBLE ({$lowStock->count()}) ===\n";
            foreach ($lowStock as $p) {
                $qty = $p->stocks->sum('quantity');
                $msg .= "- {$p->name} (SKU: {$p->sku}) — Qté: {$qty}\n";
            }
        }

        return $msg;
    }
}

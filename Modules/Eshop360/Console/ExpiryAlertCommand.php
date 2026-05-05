<?php

namespace Modules\Eshop360\Console;

use App\Instances\Instance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Modules\Eshop360\Domain\Inventory\Models\Stock;

class ExpiryAlertCommand extends Command
{
    protected $signature = 'eshop360:expiry-alerts {--days=30 : Days before expiry to alert}';

    protected $description = 'Send alerts for products nearing expiry date';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $threshold = now()->addDays($days);
        $instances = Instance::all();

        foreach ($instances as $instance) {
            $expiringStocks = Stock::where('instance_id', $instance->id)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', $threshold)
                ->where('expiry_date', '>=', now())
                ->where('quantity', '>', 0)
                ->with('product')
                ->orderBy('expiry_date')
                ->get();

            $expiredStocks = Stock::where('instance_id', $instance->id)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<', now())
                ->where('quantity', '>', 0)
                ->with('product')
                ->get();

            if ($expiringStocks->isEmpty() && $expiredStocks->isEmpty()) {
                continue;
            }

            $admins = $instance->users()
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['instance-admin', 'manager']))
                ->get();

            foreach ($admins as $admin) {
                if (! $admin->email) {
                    continue;
                }

                Mail::raw(
                    $this->buildMessage($instance, $expiringStocks, $expiredStocks, $days),
                    function ($message) use ($admin, $instance) {
                        $message->to($admin->email)
                            ->subject("[{$instance->name}] Alerte Expiration Produits");
                    }
                );
            }

            $this->info("Expiry alerts sent for: {$instance->name}");
        }

        return self::SUCCESS;
    }

    private function buildMessage($instance, $expiring, $expired, $days): string
    {
        $msg = "Rapport Expiration - {$instance->name}\n";
        $msg .= 'Date: '.now()->format('d/m/Y H:i')."\n\n";

        if ($expired->isNotEmpty()) {
            $msg .= "=== PRODUITS EXPIRÉS ({$expired->count()}) ===\n";
            foreach ($expired as $s) {
                $msg .= "- {$s->product->name} | Lot: {$s->batch_number} | Expiré le: {$s->expiry_date->format('d/m/Y')} | Qté: {$s->quantity}\n";
            }
            $msg .= "\n";
        }

        if ($expiring->isNotEmpty()) {
            $msg .= "=== EXPIRATION DANS {$days} JOURS ({$expiring->count()}) ===\n";
            foreach ($expiring as $s) {
                $daysLeft = now()->diffInDays($s->expiry_date);
                $msg .= "- {$s->product->name} | Lot: {$s->batch_number} | Expire le: {$s->expiry_date->format('d/m/Y')} ({$daysLeft}j) | Qté: {$s->quantity}\n";
            }
        }

        return $msg;
    }
}

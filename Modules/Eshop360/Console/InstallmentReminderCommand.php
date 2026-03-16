<?php

namespace Modules\Eshop360\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Modules\Eshop360\Models\InstallmentPayment;
use App\Instances\Instance;

class InstallmentReminderCommand extends Command
{
    protected $signature = 'eshop360:installment-reminders {--days=3 : Days before due date to remind}';
    protected $description = 'Send reminders for upcoming installment payments';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $instances = Instance::all();

        foreach ($instances as $instance) {
            $upcomingPayments = InstallmentPayment::whereHas('installmentPlan', fn($q) => $q->where('instance_id', $instance->id))
                ->where('status', 'pending')
                ->where('due_date', '<=', now()->addDays($days))
                ->where('due_date', '>=', now())
                ->with('installmentPlan.order.customer')
                ->get();

            $overduePayments = InstallmentPayment::whereHas('installmentPlan', fn($q) => $q->where('instance_id', $instance->id))
                ->where('status', 'pending')
                ->where('due_date', '<', now())
                ->with('installmentPlan.order.customer')
                ->get();

            if ($upcomingPayments->isEmpty() && $overduePayments->isEmpty()) {
                continue;
            }

            // Notify admins
            $admins = $instance->users()
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['instance-admin', 'manager']))
                ->get();

            foreach ($admins as $admin) {
                if (!$admin->email) continue;

                Mail::raw(
                    $this->buildMessage($instance, $upcomingPayments, $overduePayments),
                    function ($message) use ($admin, $instance) {
                        $message->to($admin->email)
                            ->subject("[{$instance->name}] Rappel Échéances");
                    }
                );
            }

            // Notify customers with upcoming payments
            foreach ($upcomingPayments as $payment) {
                $customer = $payment->installmentPlan->order->customer ?? null;
                if ($customer && $customer->email) {
                    Mail::raw(
                        "Bonjour {$customer->name},\n\nRappel : un paiement de {$payment->amount} est dû le {$payment->due_date->format('d/m/Y')}.\nRéférence commande : {$payment->installmentPlan->order->reference}\n\nMerci.",
                        function ($message) use ($customer, $instance) {
                            $message->to($customer->email)
                                ->subject("[{$instance->name}] Rappel de paiement");
                        }
                    );
                }
            }

            $this->info("Installment reminders sent for: {$instance->name}");
        }

        return self::SUCCESS;
    }

    private function buildMessage($instance, $upcoming, $overdue): string
    {
        $msg = "Rapport Échéances - {$instance->name}\n";
        $msg .= "Date: " . now()->format('d/m/Y') . "\n\n";

        if ($overdue->isNotEmpty()) {
            $msg .= "=== EN RETARD ({$overdue->count()}) ===\n";
            foreach ($overdue as $p) {
                $customer = $p->installmentPlan->order->customer->name ?? 'N/A';
                $daysLate = $p->due_date->diffInDays(now());
                $msg .= "- Client: {$customer} | Montant: {$p->amount} | Échéance: {$p->due_date->format('d/m/Y')} ({$daysLate}j de retard)\n";
            }
            $msg .= "\n";
        }

        if ($upcoming->isNotEmpty()) {
            $msg .= "=== À VENIR ({$upcoming->count()}) ===\n";
            foreach ($upcoming as $p) {
                $customer = $p->installmentPlan->order->customer->name ?? 'N/A';
                $msg .= "- Client: {$customer} | Montant: {$p->amount} | Échéance: {$p->due_date->format('d/m/Y')}\n";
            }
        }

        return $msg;
    }
}

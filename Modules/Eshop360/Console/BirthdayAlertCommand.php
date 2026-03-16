<?php

namespace Modules\Eshop360\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Modules\Eshop360\Models\Customer;
use App\Instances\Instance;

class BirthdayAlertCommand extends Command
{
    protected $signature = 'eshop360:birthday-alerts';
    protected $description = 'Send birthday greetings and alerts for customer birthdays';

    public function handle(): int
    {
        $instances = Instance::all();

        foreach ($instances as $instance) {
            $todayBirthdays = Customer::where('instance_id', $instance->id)
                ->whereNotNull('date_of_birth')
                ->whereMonth('date_of_birth', now()->month)
                ->whereDay('date_of_birth', now()->day)
                ->get();

            if ($todayBirthdays->isEmpty()) {
                continue;
            }

            // Send birthday email to customers
            foreach ($todayBirthdays as $customer) {
                if ($customer->email) {
                    Mail::raw(
                        "Cher(e) {$customer->name},\n\nJoyeux anniversaire ! 🎂\nToute l'équipe de {$instance->name} vous souhaite une merveilleuse journée.\n\nCordialement,\n{$instance->name}",
                        function ($message) use ($customer, $instance) {
                            $message->to($customer->email)
                                ->subject("🎂 Joyeux anniversaire {$customer->name} !");
                        }
                    );
                }
            }

            // Notify admins about birthdays
            $admins = $instance->users()
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['instance-admin', 'manager']))
                ->get();

            $names = $todayBirthdays->pluck('name')->join(', ');

            foreach ($admins as $admin) {
                if (!$admin->email) continue;

                Mail::raw(
                    "Anniversaires du jour ({$instance->name}) :\n\n" .
                    $todayBirthdays->map(fn($c) => "- {$c->name} ({$c->phone})")->join("\n"),
                    function ($message) use ($admin, $instance) {
                        $message->to($admin->email)
                            ->subject("[{$instance->name}] Anniversaires du jour");
                    }
                );
            }

            $this->info("Birthday alerts sent for {$instance->name}: {$todayBirthdays->count()} birthdays");
        }

        return self::SUCCESS;
    }
}

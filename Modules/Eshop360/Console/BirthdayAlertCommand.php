<?php

namespace Modules\Eshop360\Console;

use Illuminate\Console\Command;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Services\EmailService;
use Modules\Eshop360\Services\SmsService;
use Modules\Core\Support\CurrentInstance;
use App\Instances\Instance;

class BirthdayAlertCommand extends Command
{
    protected $signature = 'eshop360:birthday-alerts';
    protected $description = 'Send birthday greetings and alerts for customer birthdays';

    public function handle(EmailService $emailService, SmsService $smsService): int
    {
        $instances = Instance::all();

        foreach ($instances as $instance) {
            // Set the current instance context so services work correctly
            CurrentInstance::set($instance);

            $todayBirthdays = Customer::where('instance_id', $instance->id)
                ->whereNotNull('date_of_birth')
                ->whereMonth('date_of_birth', now()->month)
                ->whereDay('date_of_birth', now()->day)
                ->get();

            if ($todayBirthdays->isEmpty()) {
                continue;
            }

            $emailsSent = 0;
            $smsSent = 0;

            // Send birthday greetings to customers
            foreach ($todayBirthdays as $customer) {
                // Try email template first, fall back to raw email
                if ($customer->email) {
                    $sent = $emailService->sendFromTemplate('birthday', $customer->email, [
                        'name' => $customer->name,
                        'instance_name' => $instance->name,
                    ]);

                    if (!$sent) {
                        // Fallback: send raw email
                        $sent = $emailService->send(
                            $customer->email,
                            "Joyeux anniversaire {$customer->name} !",
                            "<p>Cher(e) {$customer->name},</p>"
                            . "<p>Joyeux anniversaire ! Toute l'equipe de <strong>{$instance->name}</strong> vous souhaite une merveilleuse journee.</p>"
                            . "<p>Cordialement,<br>{$instance->name}</p>",
                        );
                    }

                    if ($sent) {
                        $emailsSent++;
                    }
                }

                // Send SMS if phone is available
                if ($customer->phone) {
                    $message = "Joyeux anniversaire {$customer->name} ! Toute l'equipe de {$instance->name} vous souhaite une excellente journee.";
                    $sent = $smsService->send($customer->phone, $message);

                    if ($sent) {
                        $smsSent++;
                    }
                }
            }

            // Notify admins about today's birthdays
            $admins = $instance->users()
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['instance-admin', 'manager']))
                ->get();

            $adminBody = "<h3>Anniversaires du jour - {$instance->name}</h3>"
                . "<p>Date : " . now()->format('d/m/Y') . "</p>"
                . "<ul>";
            foreach ($todayBirthdays as $c) {
                $adminBody .= "<li><strong>{$c->name}</strong> ({$c->phone}) - {$c->email}</li>";
            }
            $adminBody .= "</ul>"
                . "<p>Emails envoyes : {$emailsSent} | SMS envoyes : {$smsSent}</p>";

            foreach ($admins as $admin) {
                if (!$admin->email) {
                    continue;
                }

                $emailService->send(
                    $admin->email,
                    "[{$instance->name}] Anniversaires du jour",
                    $adminBody,
                );
            }

            $this->info("Birthday alerts sent for {$instance->name}: {$todayBirthdays->count()} birthdays ({$emailsSent} emails, {$smsSent} SMS)");
        }

        return self::SUCCESS;
    }
}

<?php

namespace Modules\Eshop360\Services;

use Modules\Eshop360\Services\Sms\SmsManager;

/**
 * SMS sending service for Eshop360.
 *
 * This is a thin facade over SmsManager for backward compatibility.
 * New code should inject SmsManager directly.
 */
final class SmsService
{
    protected SmsManager $manager;

    public function __construct()
    {
        $this->manager = new SmsManager;
    }

    /**
     * Send an SMS message via the default or specified driver.
     */
    public function send(string $to, string $message, ?int $gatewayId = null): bool
    {
        // If a specific gatewayId is provided, resolve the gateway and use its driver
        if ($gatewayId) {
            $gateway = \Modules\Eshop360\Domain\Communication\Models\SmsGateway::find($gatewayId);
            if (! $gateway) {
                return false;
            }
            $driver = $this->manager->driverFromGateway($gateway);

            return $driver->send($to, $message);
        }

        return $this->manager->send($to, $message);
    }

    /**
     * Send SMS to multiple recipients.
     */
    public function sendBulk(array $recipients, string $message, ?int $gatewayId = null): array
    {
        $results = [];
        foreach ($recipients as $to) {
            $results[$to] = $this->send($to, $message, $gatewayId);
        }

        return $results;
    }
}

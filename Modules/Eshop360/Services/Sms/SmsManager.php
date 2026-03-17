<?php

namespace Modules\Eshop360\Services\Sms;

use Illuminate\Support\Facades\Log;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\SmsGateway;
use Modules\Eshop360\Models\SmsLog;
use Modules\Eshop360\Services\Sms\Drivers\BulkSmsDriver;
use Modules\Eshop360\Services\Sms\Drivers\ClockworkDriver;
use Modules\Eshop360\Services\Sms\Drivers\GenericWebhookDriver;
use Modules\Eshop360\Services\Sms\Drivers\Msg91Driver;
use Modules\Eshop360\Services\Sms\Drivers\TextLocalDriver;
use Modules\Eshop360\Services\Sms\Drivers\TwilioDriver;
use Modules\Eshop360\Services\Sms\Drivers\VonageDriver;

class SmsManager
{
    /**
     * Map of driver names to their implementation classes.
     */
    protected array $drivers = [
        'twilio' => TwilioDriver::class,
        'vonage' => VonageDriver::class,
        'textlocal' => TextLocalDriver::class,
        'clockwork' => ClockworkDriver::class,
        'msg91' => Msg91Driver::class,
        'bulksms' => BulkSmsDriver::class,
        'generic' => GenericWebhookDriver::class,
    ];

    /**
     * Resolve a driver instance from a gateway model or driver name.
     */
    public function driver(?string $name = null): SmsDriverInterface
    {
        $instance = CurrentInstance::get();

        if ($name) {
            // Look up the gateway by driver name for the current instance
            $gateway = SmsGateway::where('instance_id', $instance?->id)
                ->where('driver', $name)
                ->where('is_active', true)
                ->first();
        } else {
            // Use the default gateway
            $gateway = SmsGateway::where('instance_id', $instance?->id)
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();
        }

        if (!$gateway) {
            throw new \RuntimeException("No active SMS gateway found" . ($name ? " for driver [{$name}]" : ' (default)'));
        }

        return $this->resolveDriver($gateway->driver, $gateway->config ?? []);
    }

    /**
     * Resolve a driver from a SmsGateway model.
     */
    public function driverFromGateway(SmsGateway $gateway): SmsDriverInterface
    {
        return $this->resolveDriver($gateway->driver, $gateway->config ?? []);
    }

    /**
     * Send an SMS via the specified or default driver, logging the result.
     */
    public function send(string $to, string $message, ?string $driverName = null): bool
    {
        $instance = CurrentInstance::get();
        if (!$instance) {
            Log::warning('SmsManager::send called without an active instance');
            return false;
        }

        // Resolve the gateway
        if ($driverName) {
            $gateway = SmsGateway::where('instance_id', $instance->id)
                ->where('driver', $driverName)
                ->where('is_active', true)
                ->first();
        } else {
            $gateway = SmsGateway::where('instance_id', $instance->id)
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();
        }

        if (!$gateway) {
            Log::warning('No SMS gateway configured for instance ' . $instance->id);
            return false;
        }

        $driverInstance = $this->resolveDriver($gateway->driver, $gateway->config ?? []);

        $success = $driverInstance->send($to, $message);

        // Log the SMS
        SmsLog::create([
            'instance_id' => $instance->id,
            'gateway_id' => $gateway->id,
            'to' => $to,
            'message' => $message,
            'status' => $success ? 'sent' : 'failed',
            'error' => $success ? null : 'Send returned false',
            'sent_at' => $success ? now() : null,
        ]);

        return $success;
    }

    /**
     * Return the list of available driver names with their config field definitions.
     */
    public function getAvailableDrivers(): array
    {
        $result = [];

        foreach ($this->drivers as $name => $class) {
            $result[$name] = [
                'name' => $name,
                'label' => $this->driverLabel($name),
                'fields' => $class::getConfigFields(),
            ];
        }

        return $result;
    }

    /**
     * Test a driver configuration by attempting a getBalance() call.
     */
    public function testConnection(string $driverName, array $config): bool
    {
        if (!isset($this->drivers[$driverName])) {
            return false;
        }

        try {
            $driver = $this->resolveDriver($driverName, $config);
            $balance = $driver->getBalance();
            // If getBalance returns null, it means the driver doesn't support it —
            // we consider the connection valid if no exception was thrown.
            return true;
        } catch (\Throwable $e) {
            Log::error('SMS driver test failed', [
                'driver' => $driverName,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Instantiate the driver class with the given config.
     */
    protected function resolveDriver(string $name, array $config): SmsDriverInterface
    {
        $class = $this->drivers[$name] ?? null;

        if (!$class || !class_exists($class)) {
            throw new \InvalidArgumentException("Unknown SMS driver [{$name}]");
        }

        return new $class($config);
    }

    /**
     * Human-readable label for a driver.
     */
    protected function driverLabel(string $name): string
    {
        return match ($name) {
            'twilio' => 'Twilio',
            'vonage' => 'Vonage (Nexmo)',
            'textlocal' => 'TextLocal',
            'clockwork' => 'Clockwork SMS',
            'msg91' => 'MSG91',
            'bulksms' => 'BulkSMS',
            'generic' => 'Generic Webhook',
            default => ucfirst($name),
        };
    }
}

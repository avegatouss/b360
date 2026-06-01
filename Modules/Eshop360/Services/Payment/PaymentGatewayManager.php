<?php

namespace Modules\Eshop360\Services\Payment;

use InvalidArgumentException;
use Modules\Eshop360\Services\Payment\Drivers\AuthorizeNetDriver;
use Modules\Eshop360\Services\Payment\Drivers\CheckoutComDriver;
use Modules\Eshop360\Services\Payment\Drivers\CinetPayDriver;
use Modules\Eshop360\Services\Payment\Drivers\PayPalDriver;
use Modules\Eshop360\Services\Payment\Drivers\PayUMoneyDriver;
use Modules\Eshop360\Services\Payment\Drivers\PinPayDriver;
use Modules\Eshop360\Services\Payment\Drivers\RazorpayDriver;
use Modules\Eshop360\Services\Payment\Drivers\SecurePayDriver;
use Modules\Eshop360\Services\Payment\Drivers\StripeDriver;
use Modules\Eshop360\Services\Payment\Drivers\WalletDriver;

final class PaymentGatewayManager
{
    /**
     * Registry of driver name => class.
     */
    private const DRIVERS = [
        'stripe' => StripeDriver::class,
        'paypal' => PayPalDriver::class,
        'razorpay' => RazorpayDriver::class,
        'authorize_net' => AuthorizeNetDriver::class,
        'payu_money' => PayUMoneyDriver::class,
        'checkout_com' => CheckoutComDriver::class,
        'securepay' => SecurePayDriver::class,
        'pinpay' => PinPayDriver::class,
        'wallet' => WalletDriver::class,
        'cinetpay' => CinetPayDriver::class,
    ];

    /**
     * Human-readable display names.
     */
    private const DISPLAY_NAMES = [
        'stripe' => 'Stripe',
        'paypal' => 'PayPal',
        'razorpay' => 'Razorpay',
        'authorize_net' => 'Authorize.Net',
        'payu_money' => 'PayU Money',
        'checkout_com' => 'Checkout.com',
        'securepay' => 'SecurePay',
        'pinpay' => 'Pin Payments',
        'wallet' => 'Portefeuille client',
        'cinetpay' => 'CinetPay',
    ];

    /**
     * Resolve a driver instance with the given config.
     */
    public function driver(string $name, array $config = []): PaymentGatewayInterface
    {
        $class = self::DRIVERS[$name] ?? null;

        if (! $class) {
            throw new InvalidArgumentException("Unknown payment gateway driver: {$name}");
        }

        return new $class($config);
    }

    /**
     * Initiate a payment through a named driver.
     */
    public function initiate(string $driverName, array $config, float $amount, string $currency, array $meta = []): array
    {
        return $this->driver($driverName, $config)->initiate($amount, $currency, $meta);
    }

    /**
     * Verify a payment through a named driver.
     */
    public function verify(string $driverName, array $config, string $transactionId): array
    {
        return $this->driver($driverName, $config)->verify($transactionId);
    }

    /**
     * Get all available gateway driver names with display info.
     *
     * @return array<string, array{name: string, driver: string, fields: array}>
     */
    public function getAvailableGateways(): array
    {
        $gateways = [];

        foreach (self::DRIVERS as $key => $class) {
            $gateways[$key] = [
                'name' => self::DISPLAY_NAMES[$key] ?? $key,
                'driver' => $key,
                'fields' => $class::getConfigFields(),
            ];
        }

        return $gateways;
    }

    /**
     * Check if a driver name is valid.
     */
    public function isValidDriver(string $name): bool
    {
        return isset(self::DRIVERS[$name]);
    }

    /**
     * Get the display name for a driver.
     */
    public function displayName(string $name): string
    {
        return self::DISPLAY_NAMES[$name] ?? $name;
    }

    /**
     * Get config fields for a specific driver.
     */
    public function configFields(string $name): array
    {
        $class = self::DRIVERS[$name] ?? null;

        return $class ? $class::getConfigFields() : [];
    }
}

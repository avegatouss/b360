<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Collection;
use Modules\Billing\Contracts\PaymentGatewayInterface;
use Modules\Billing\Contracts\PaymentRequest;
use Modules\Billing\Contracts\PaymentResponse;
use Modules\Billing\Contracts\WebhookResult;
use Modules\Core\Hooks\DTO\PaymentGatewayDefinition;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Settings\Services\SettingsManager;
use RuntimeException;

/**
 * Orchestrates payment gateways registered via hooks.
 *
 * Gateways are registered as PaymentGatewayDefinition DTOs.
 * Credentials are stored per-instance in the settings table.
 *
 * Note : non-final pour permettre le mocking dans WebhookIdempotenceTest
 * (GatewayManager est injecté dans WebhookController — un double de test
 * est requis pour exercer le chemin idempotence sans toucher à un vrai
 * driver de paiement).
 */
class GatewayManager
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $resolved = [];

    public function __construct(
        private readonly HookRegistry $hookRegistry,
        private readonly SettingsManager $settings,
    ) {}

    /**
     * Resolve and configure a gateway for a specific instance.
     */
    public function resolve(string $gatewayId, int $instanceId): PaymentGatewayInterface
    {
        $cacheKey = "{$gatewayId}:{$instanceId}";

        if (isset($this->resolved[$cacheKey])) {
            return $this->resolved[$cacheKey];
        }

        $definition = $this->definition($gatewayId);
        if (! $definition) {
            throw new RuntimeException("Payment gateway '{$gatewayId}' is not registered.");
        }

        $driverClass = $definition->driverClass;
        if (! class_exists($driverClass)) {
            throw new RuntimeException("Gateway driver class '{$driverClass}' not found.");
        }

        /** @var PaymentGatewayInterface $driver */
        $driver = app($driverClass);

        // Load credentials from settings
        $credentials = $this->credentials($gatewayId, $instanceId);
        $driver->configure($credentials);

        return $this->resolved[$cacheKey] = $driver;
    }

    /**
     * Get a gateway definition by ID.
     */
    public function definition(string $gatewayId): ?PaymentGatewayDefinition
    {
        return $this->hookRegistry->paymentGateways()->firstWhere('id', $gatewayId);
    }

    /**
     * Get all registered gateway definitions.
     */
    public function all(): Collection
    {
        return $this->hookRegistry->paymentGateways();
    }

    /**
     * Get gateways enabled for a specific instance.
     */
    public function enabledFor(int $instanceId): Collection
    {
        return $this->all()->filter(function (PaymentGatewayDefinition $def) use ($instanceId) {
            $enabled = $this->settings->get(
                "billing.gateway.{$def->id}.enabled",
                false,
                $instanceId
            );

            return (bool) $enabled;
        })->values();
    }

    /**
     * Check if a gateway is enabled for an instance.
     */
    public function isEnabled(string $gatewayId, int $instanceId): bool
    {
        return (bool) $this->settings->get(
            "billing.gateway.{$gatewayId}.enabled",
            false,
            $instanceId
        );
    }

    /**
     * Enable or disable a gateway for an instance.
     */
    public function toggle(string $gatewayId, int $instanceId, bool $enabled): void
    {
        $this->settings->set(
            "billing.gateway.{$gatewayId}.enabled",
            $enabled,
            $instanceId,
            'boolean'
        );
    }

    /**
     * Get credentials for a gateway/instance pair.
     */
    public function credentials(string $gatewayId, int $instanceId): array
    {
        $value = $this->settings->get(
            "billing.gateway.{$gatewayId}.credentials",
            null,
            $instanceId
        );

        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Save credentials for a gateway/instance pair.
     */
    public function saveCredentials(string $gatewayId, int $instanceId, array $credentials): void
    {
        $this->settings->set(
            "billing.gateway.{$gatewayId}.credentials",
            $credentials,
            $instanceId,
            'json'
        );

        // Clear cached resolved driver
        unset($this->resolved["{$gatewayId}:{$instanceId}"]);
    }

    /**
     * Initiate a payment through a specific gateway.
     */
    public function pay(string $gatewayId, int $instanceId, PaymentRequest $request): PaymentResponse
    {
        $driver = $this->resolve($gatewayId, $instanceId);

        if (! $driver->isConfigured()) {
            return new PaymentResponse(
                success: false,
                error: "La passerelle '{$gatewayId}' n'est pas configuree.",
            );
        }

        return $driver->initiate($request);
    }

    /**
     * Process a webhook for a specific gateway.
     */
    public function handleWebhook(string $gatewayId, array $payload, array $headers = []): WebhookResult
    {
        // For webhooks, we need to resolve without instance context initially
        $definition = $this->definition($gatewayId);
        if (! $definition) {
            return new WebhookResult(valid: false, error: "Unknown gateway: {$gatewayId}");
        }

        $driverClass = $definition->driverClass;
        if (! class_exists($driverClass)) {
            return new WebhookResult(valid: false, error: "Driver not found: {$driverClass}");
        }

        /** @var PaymentGatewayInterface $driver */
        $driver = app($driverClass);

        return $driver->verifyWebhook($payload, $headers);
    }

    /**
     * Test a gateway connection with current credentials.
     */
    public function testConnection(string $gatewayId, int $instanceId): array
    {
        try {
            $driver = $this->resolve($gatewayId, $instanceId);

            if (! $driver->isConfigured()) {
                return ['success' => false, 'message' => 'Passerelle non configuree. Veuillez saisir les identifiants.'];
            }

            return $driver->testConnection();
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

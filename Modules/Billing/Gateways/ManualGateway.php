<?php

namespace Modules\Billing\Gateways;

use Modules\Billing\Contracts\PaymentGatewayInterface;
use Modules\Billing\Contracts\PaymentRequest;
use Modules\Billing\Contracts\PaymentResponse;
use Modules\Billing\Contracts\PaymentStatus;
use Modules\Billing\Contracts\RefundResult;
use Modules\Billing\Contracts\WebhookResult;

/**
 * Manual payment gateway — bank transfer, cash, cheque.
 * Always configured, no external API calls.
 */
final class ManualGateway implements PaymentGatewayInterface
{
    private array $config = [];

    public function id(): string { return 'manual'; }

    public function name(): string { return 'Virement / Paiement manuel'; }

    public function configure(array $credentials): void
    {
        $this->config = $credentials;
    }

    public function isConfigured(): bool
    {
        return true; // Manual is always available
    }

    public function testConnection(): array
    {
        return ['success' => true, 'message' => 'Le paiement manuel est toujours disponible.'];
    }

    public function initiate(PaymentRequest $request): PaymentResponse
    {
        return new PaymentResponse(
            success: true,
            gatewayReference: 'MANUAL-' . $request->reference,
            status: 'pending',
            metadata: [
                'instructions' => $this->config['instructions'] ?? 'Effectuez le virement puis confirmez le paiement.',
                'bank_name' => $this->config['bank_name'] ?? null,
                'account_number' => $this->config['account_number'] ?? null,
                'iban' => $this->config['iban'] ?? null,
            ],
        );
    }

    public function verifyWebhook(array $payload, array $headers = []): WebhookResult
    {
        return new WebhookResult(valid: false, error: 'Manual gateway does not support webhooks.');
    }

    public function checkStatus(string $gatewayReference): PaymentStatus
    {
        return new PaymentStatus(status: 'pending', gatewayReference: $gatewayReference);
    }

    public function supportedCurrencies(): array
    {
        return ['XOF', 'XAF', 'EUR', 'USD', 'GBP', 'GNF', 'MAD', 'TND', 'CAD', 'CHF'];
    }

    public function supportsRefunds(): bool { return false; }

    public function refund(string $gatewayReference, float $amount, string $currency): RefundResult
    {
        return new RefundResult(success: false, error: 'Les remboursements manuels doivent etre traites hors systeme.');
    }
}

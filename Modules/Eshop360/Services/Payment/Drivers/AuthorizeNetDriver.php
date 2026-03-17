<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Eshop360\Services\Payment\PaymentGatewayInterface;

final class AuthorizeNetDriver implements PaymentGatewayInterface
{
    public function __construct(private array $config = []) {}

    private function baseUrl(): string
    {
        return ($this->config['mode'] ?? 'sandbox') === 'live'
            ? 'https://api.authorize.net/xml/v1/request.api'
            : 'https://apitest.authorize.net/xml/v1/request.api';
    }

    public function initiate(float $amount, string $currency, array $meta = []): array
    {
        try {
            $response = Http::timeout(30)->post($this->baseUrl(), [
                'createTransactionRequest' => [
                    'merchantAuthentication' => [
                        'name' => $this->config['api_login_id'] ?? '',
                        'transactionKey' => $this->config['transaction_key'] ?? '',
                    ],
                    'transactionRequest' => [
                        'transactionType' => 'authCaptureTransaction',
                        'amount' => number_format($amount, 2, '.', ''),
                        'order' => ['invoiceNumber' => $meta['reference'] ?? '', 'description' => $meta['description'] ?? ''],
                    ],
                ],
            ]);

            $data = $response->json();
            $result = $data['transactionResponse'] ?? [];

            if (($result['responseCode'] ?? '') === '1') {
                return ['success' => true, 'redirect_url' => null, 'transaction_id' => $result['transId'] ?? null];
            }

            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null,
                'error' => $result['errors'][0]['errorText'] ?? 'Authorize.Net error'];
        } catch (\Throwable $e) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function verify(string $transactionId): array
    {
        try {
            $response = Http::timeout(15)->post($this->baseUrl(), [
                'getTransactionDetailsRequest' => [
                    'merchantAuthentication' => [
                        'name' => $this->config['api_login_id'] ?? '',
                        'transactionKey' => $this->config['transaction_key'] ?? '',
                    ],
                    'transId' => $transactionId,
                ],
            ]);

            $txn = $response->json('transaction') ?? [];
            $status = match ($txn['transactionStatus'] ?? '') {
                'settledSuccessfully', 'capturedPendingSettlement' => 'completed',
                'declined', 'void' => 'failed',
                default => 'pending',
            };

            return ['success' => $status === 'completed', 'amount' => (float) ($txn['settleAmount'] ?? 0), 'status' => $status];
        } catch (\Throwable $e) {
            return ['success' => false, 'amount' => null, 'status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        return ['success' => false, 'refund_id' => null, 'error' => 'Refund requires card details — use Authorize.net dashboard.'];
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->all();
        $eventType = $payload['eventType'] ?? '';
        $status = str_contains($eventType, 'authcapture') ? 'completed' : 'pending';

        return ['valid' => true, 'transaction_id' => $payload['payload']['id'] ?? null, 'status' => $status];
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'api_login_id', 'label' => 'API Login ID', 'type' => 'text', 'required' => true],
            ['name' => 'transaction_key', 'label' => 'Transaction Key', 'type' => 'password', 'required' => true],
            ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'required' => true, 'options' => ['sandbox' => 'Sandbox', 'live' => 'Live']],
        ];
    }
}

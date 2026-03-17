<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Eshop360\Services\Payment\PaymentGatewayInterface;

final class CinetPayDriver implements PaymentGatewayInterface
{
    private const BASE = 'https://api-checkout.cinetpay.com/v2';

    public function __construct(private array $config = []) {}

    public function initiate(float $amount, string $currency, array $meta = []): array
    {
        try {
            $txnId = $meta['reference'] ?? 'CNPY-' . uniqid();

            $response = Http::timeout(30)->post(self::BASE . '/payment', [
                'apikey' => $this->config['api_key'] ?? '',
                'site_id' => $this->config['site_id'] ?? '',
                'transaction_id' => $txnId,
                'amount' => (int) $amount,
                'currency' => $currency ?: 'XOF',
                'description' => $meta['description'] ?? 'Paiement',
                'notify_url' => $meta['webhook_url'] ?? '',
                'return_url' => $meta['return_url'] ?? '',
                'channels' => 'ALL',
                'customer_name' => $meta['customer_name'] ?? '',
                'customer_email' => $meta['customer_email'] ?? '',
                'customer_phone_number' => $meta['customer_phone'] ?? '',
            ]);

            $data = $response->json();

            if (isset($data['code']) && $data['code'] === '201') {
                return [
                    'success' => true,
                    'redirect_url' => $data['data']['payment_url'] ?? null,
                    'transaction_id' => $data['data']['payment_token'] ?? $txnId,
                ];
            }

            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $data['message'] ?? 'CinetPay error'];
        } catch (\Throwable $e) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function verify(string $transactionId): array
    {
        try {
            $response = Http::timeout(15)->post(self::BASE . '/payment/check', [
                'apikey' => $this->config['api_key'] ?? '',
                'site_id' => $this->config['site_id'] ?? '',
                'transaction_id' => $transactionId,
            ]);

            $data = $response->json();
            $code = $data['code'] ?? '';
            $status = match ($code) {
                '00' => 'completed', '600' => 'failed', '627' => 'cancelled', default => 'pending',
            };

            return [
                'success' => $status === 'completed',
                'amount' => isset($data['data']['amount']) ? (float) $data['data']['amount'] : null,
                'status' => $status,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'amount' => null, 'status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        return ['success' => false, 'refund_id' => null, 'error' => 'CinetPay does not support API refunds'];
    }

    public function handleWebhook(Request $request): array
    {
        $transactionId = $request->input('cpm_trans_id');

        if (!$transactionId) {
            return ['valid' => false, 'transaction_id' => null, 'status' => null, 'error' => 'Missing cpm_trans_id'];
        }

        // Re-verify the payment status
        $result = $this->verify($transactionId);

        return [
            'valid' => true,
            'transaction_id' => $transactionId,
            'status' => $result['status'],
        ];
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'required' => true],
            ['name' => 'site_id', 'label' => 'Site ID', 'type' => 'text', 'required' => true],
            ['name' => 'secret_key', 'label' => 'Cle secrete', 'type' => 'password', 'required' => false],
        ];
    }
}

<?php

namespace Modules\Eshop360\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Payment;

/**
 * CinetPay payment gateway integration.
 *
 * Uses the existing polymorphic Payment model and stores gateway-specific
 * details in dedicated gateway columns and metadata.
 */
class CinetPayService
{
    private string $baseUrl;

    private string $merchantId;

    private string $secretKey;

    private string $callbackUrl;

    public function __construct()
    {
        $config = config('eshop360.cinetpay', config('eshop360.inetpay', []));
        $this->baseUrl = $config['base_url'] ?? '';
        $this->merchantId = $config['merchant_id'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->callbackUrl = $config['callback_url'] ?? '';
    }

    public function initiate(Order $order, string $paymentMethod = 'mobile_money', array $extra = []): array
    {
        $instance = CurrentInstance::get();
        $reference = $this->generateReference($order);
        $currency = function_exists('currency') ? currency($instance?->id) : config('billing.currency', 'EUR');

        $payload = [
            'merchant_id' => $this->merchantId,
            'reference' => $reference,
            'amount' => (float) $order->total,
            'currency' => $currency,
            'description' => 'Commande #'.($order->order_number ?? $order->id),
            'payment_method' => $paymentMethod,
            'customer' => [
                'name' => $order->customer?->name ?? 'Client',
                'email' => $order->customer?->email ?? '',
                'phone' => $extra['phone'] ?? $order->customer?->phone ?? '',
            ],
            'callback_url' => $this->callbackUrl ?: route('api.eshop360.cinetpay.callback'),
            'return_url' => $extra['return_url'] ?? '',
            'metadata' => [
                'order_id' => $order->id,
                'instance_id' => $instance?->id,
                'gateway' => 'cinetpay',
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
                'Content-Type' => 'application/json',
            ])->post(rtrim($this->baseUrl, '/').'/payments/initiate', $payload);

            if (! $response->successful()) {
                Log::error('CinetPay initiation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'order_id' => $order->id,
                ]);

                return [
                    'success' => false,
                    'error' => $response->json('message', 'Payment initiation failed'),
                ];
            }

            $data = $response->json();

            $payment = $order->payments()->create([
                'instance_id' => $instance?->id,
                'amount' => $order->total,
                'method' => 'external',
                'gateway' => 'cinetpay',
                'reference' => $reference,
                'gateway_reference' => $data['transaction_id'] ?? null,
                'status' => 'pending',
                'notes' => 'CinetPay initiation',
                'metadata' => [
                    'initiation' => $data,
                    'requested_method' => $paymentMethod,
                ],
                'received_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_url' => $data['payment_url'] ?? null,
                'ussd_code' => $data['ussd_code'] ?? null,
                'reference' => $reference,
            ];
        } catch (\Throwable $e) {
            Log::error('CinetPay exception', [
                'message' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return [
                'success' => false,
                'error' => 'Payment service unavailable',
            ];
        }
    }

    public function verifyCallback(array $data): array
    {
        $signature = (string) ($data['signature'] ?? '');
        $transactionId = (string) ($data['transaction_id'] ?? '');
        $status = (string) ($data['status'] ?? '');

        $expectedSignature = hash_hmac('sha256', $transactionId.$status, $this->secretKey);

        if ($signature === '' || ! hash_equals($expectedSignature, $signature)) {
            Log::warning('CinetPay invalid signature', ['data' => $data]);

            return ['valid' => false, 'error' => 'Invalid signature'];
        }

        $payment = Payment::query()
            ->where('gateway', 'cinetpay')
            ->when($transactionId !== '', fn ($query) => $query->where('gateway_reference', $transactionId))
            ->when(! empty($data['reference']), fn ($query) => $query->where('reference', $data['reference']))
            ->orderByDesc('id')
            ->first();

        if (! $payment && ! empty($data['reference'])) {
            $payment = Payment::query()
                ->where('gateway', 'cinetpay')
                ->where('reference', $data['reference'])
                ->orderByDesc('id')
                ->first();
        }

        if (! $payment) {
            return ['valid' => false, 'error' => 'Payment not found'];
        }

        $newStatus = match ($status) {
            'success', 'completed' => 'completed',
            'failed', 'declined' => 'failed',
            'cancelled' => 'failed',
            default => 'pending',
        };

        $payment->update([
            'status' => $newStatus,
            'metadata' => array_merge($payment->metadata ?? [], ['callback' => $data]),
        ]);

        if ($newStatus === 'completed') {
            $this->syncPayableTotals($payment->payable);
        }

        return [
            'valid' => true,
            'payment_id' => $payment->id,
            'status' => $newStatus,
        ];
    }

    public function checkStatus(string $transactionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
            ])->get(rtrim($this->baseUrl, '/')."/payments/{$transactionId}/status");

            if ($response->successful()) {
                return $response->json();
            }

            return ['status' => 'unknown', 'error' => 'Could not check status'];
        } catch (\Throwable $e) {
            return ['status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->merchantId !== '' && $this->secretKey !== '';
    }

    private function generateReference(Order $order): string
    {
        return 'CNPY-'.$order->id.'-'.strtoupper(substr(md5(uniqid('', true)), 0, 8));
    }

    private function syncPayableTotals(?Model $payable): void
    {
        if (! $payable || ! method_exists($payable, 'payments')) {
            return;
        }

        $paidAmount = (float) $payable->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $dueAmount = max(0, (float) $payable->total - $paidAmount);
        $paymentStatus = 'unpaid';

        if ($paidAmount >= (float) $payable->total) {
            $paymentStatus = 'paid';
        } elseif ($paidAmount > 0) {
            $paymentStatus = 'partial';
        }

        if ($payable instanceof Order) {
            $payable->update([
                'payment_method' => 'cinetpay',
                'paid_amount' => round($paidAmount, 2),
                'due_amount' => round($dueAmount, 2),
                'payment_status' => $paymentStatus,
            ]);

            return;
        }

        if ($payable instanceof Invoice) {
            $invoiceStatus = $paymentStatus === 'paid' ? 'paid' : ($paymentStatus === 'partial' ? 'partial' : 'unpaid');

            $payable->update([
                'paid_amount' => round($paidAmount, 2),
                'due_amount' => round($dueAmount, 2),
                'status' => $invoiceStatus,
            ]);
        }
    }
}

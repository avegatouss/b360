<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Services\Payment\PaymentGatewayInterface;

final class WalletDriver implements PaymentGatewayInterface
{
    public function __construct(private array $config = []) {}

    public function initiate(float $amount, string $currency, array $meta = []): array
    {
        $customerId = $meta['customer_id'] ?? null;

        if (!$customerId) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => 'Customer ID required'];
        }

        $customer = Customer::find($customerId);

        if (!$customer) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => 'Customer not found'];
        }

        if ((float) $customer->wallet_balance < $amount) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null,
                'error' => "Solde insuffisant ({$customer->wallet_balance} < {$amount})"];
        }

        try {
            $txnId = 'WALLET-' . $customerId . '-' . now()->format('YmdHis') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));

            DB::transaction(function () use ($customer, $amount, $txnId, $meta) {
                $customer->decrement('wallet_balance', $amount);

                $customer->transactions()->create([
                    'instance_id' => $customer->instance_id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'reference' => $txnId,
                    'description' => $meta['description'] ?? 'Paiement par portefeuille',
                ]);
            });

            return ['success' => true, 'redirect_url' => null, 'transaction_id' => $txnId];
        } catch (\Throwable $e) {
            Log::error('Wallet payment failed', ['error' => $e->getMessage(), 'customer_id' => $customerId]);
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => 'Wallet deduction failed'];
        }
    }

    public function verify(string $transactionId): array
    {
        // Wallet payments are instant — if the transaction ID exists, it succeeded
        return ['success' => str_starts_with($transactionId, 'WALLET-'), 'amount' => null, 'status' => 'completed'];
    }

    public function refund(string $transactionId, float $amount): array
    {
        // Extract customer ID from transaction ID format: WALLET-{customer_id}-...
        $parts = explode('-', $transactionId);
        $customerId = $parts[1] ?? null;
        $customer = $customerId ? Customer::find($customerId) : null;

        if (!$customer) {
            return ['success' => false, 'refund_id' => null, 'error' => 'Customer not found for refund'];
        }

        try {
            $refundId = 'WREF-' . uniqid();

            DB::transaction(function () use ($customer, $amount, $refundId, $transactionId) {
                $customer->increment('wallet_balance', $amount);

                $customer->transactions()->create([
                    'instance_id' => $customer->instance_id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'reference' => $refundId,
                    'description' => "Remboursement {$transactionId}",
                ]);
            });

            return ['success' => true, 'refund_id' => $refundId];
        } catch (\Throwable $e) {
            return ['success' => false, 'refund_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function handleWebhook(Request $request): array
    {
        return ['valid' => false, 'transaction_id' => null, 'status' => null, 'error' => 'Wallet does not use webhooks'];
    }

    public static function getConfigFields(): array
    {
        return [];
    }
}

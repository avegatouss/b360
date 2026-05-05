<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Services\Payment\PaymentGatewayInterface;

final class WalletDriver implements PaymentGatewayInterface
{
    public function __construct(private array $config = []) {}

    public function initiate(float $amount, string $currency, array $meta = []): array
    {
        $customerId = $meta['customer_id'] ?? null;

        if (! $customerId) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => 'Customer ID required'];
        }

        // Existence check hors transaction (pas critique, juste pour message d'erreur propre).
        $customer = Customer::find($customerId);

        if (! $customer) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => 'Customer not found'];
        }

        $txnId = 'WALLET-'.$customerId.'-'.now()->format('YmdHis').'-'.strtoupper(substr(md5(uniqid('', true)), 0, 6));

        try {
            // R-003 : check + decrement sous lock pessimiste pour fermer la race TOCTOU.
            // La vérification du solde hors transaction (ancienne version) laissait une
            // fenêtre entre check ligne 29 et decrement ligne 38. Désormais le check
            // est refait sous le lock, et l'opération entière est atomique.
            DB::transaction(function () use ($customerId, $amount, $txnId, $meta) {
                $locked = Customer::withoutGlobalScopes()
                    ->where('id', $customerId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $balance = (float) $locked->getAttribute('wallet_balance');

                if ($balance < $amount) {
                    throw new InsufficientWalletBalanceException($balance, $amount);
                }

                $locked->decrement('wallet_balance', $amount);

                $locked->transactions()->create([
                    'instance_id' => $locked->getAttribute('instance_id'),
                    'type' => 'debit',
                    'amount' => $amount,
                    'reference' => $txnId,
                    'description' => $meta['description'] ?? 'Paiement par portefeuille',
                ]);
            });

            return ['success' => true, 'redirect_url' => null, 'transaction_id' => $txnId];
        } catch (InsufficientWalletBalanceException $e) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null,
                'error' => "Solde insuffisant ({$e->available} < {$e->requested})"];
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

        if (! $customerId) {
            return ['success' => false, 'refund_id' => null, 'error' => 'Customer not found for refund'];
        }

        $refundId = 'WREF-'.uniqid();

        try {
            // R-003 : increment sous lock pessimiste pour garantir l'atomicité
            // entre plusieurs refunds concurrents sur le même client.
            DB::transaction(function () use ($customerId, $amount, $refundId, $transactionId) {
                $locked = Customer::withoutGlobalScopes()
                    ->where('id', $customerId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $locked->increment('wallet_balance', $amount);

                $locked->transactions()->create([
                    'instance_id' => $locked->getAttribute('instance_id'),
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

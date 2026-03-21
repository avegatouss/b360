<?php

namespace Modules\Eshop360\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Account;
use Modules\Eshop360\Models\AccountTransaction;
use Modules\Eshop360\Models\AccountTransfer;

class DemoAccountTransactionsSeeder extends Seeder
{
    public function run(?int $instanceId = null): void
    {
        $instanceId = $instanceId ?? CurrentInstance::get()?->id ?? 1;

        if (AccountTransaction::whereHas('account', fn ($q) => $q->where('instance_id', $instanceId))->exists()) {
            return;
        }

        $accounts = Account::withoutGlobalScopes()->where('instance_id', $instanceId)->get();
        if ($accounts->isEmpty()) {
            return;
        }

        $descriptions = [
            'deposit' => [
                'Encaissement vente du jour',
                'Depot especes client',
                'Virement recu fournisseur',
                'Recette journaliere magasin',
                'Depot cheque client',
                'Encaissement facture',
                'Paiement commande en ligne',
                'Remboursement assurance',
            ],
            'withdrawal' => [
                'Achat fournitures bureau',
                'Paiement fournisseur',
                'Paiement loyer mensuel',
                'Frais de transport',
                'Paiement salaire employe',
                'Achat matiere premiere',
                'Reglement facture electricite',
                'Paiement prestataire',
                'Frais bancaires',
                'Achat carburant',
            ],
        ];

        foreach ($accounts as $account) {
            $balance = (float) $account->balance;
            $txCount = rand(15, 30);

            for ($i = 0; $i < $txCount; $i++) {
                $isDeposit = rand(0, 10) > 4; // 60% deposits
                $type = $isDeposit ? 'deposit' : 'withdrawal';
                $amount = $isDeposit ? rand(50000, 2000000) : rand(10000, 500000);

                // Don't go negative
                if (! $isDeposit && $amount > $balance) {
                    $amount = max(1000, (int) ($balance * 0.3));
                }
                if (! $isDeposit && $balance < 5000) {
                    $type = 'deposit';
                    $amount = rand(100000, 1000000);
                }

                $balance += ($type === 'deposit' ? $amount : -$amount);

                $desc = $descriptions[$type][array_rand($descriptions[$type])];

                AccountTransaction::create([
                    'account_id' => $account->id,
                    'type'       => $type,
                    'amount'     => $amount,
                    'notes'      => $desc,
                    'user_id'    => 1,
                    'created_at' => now()->subDays(rand(0, 60))->subHours(rand(0, 12)),
                ]);
            }

            $account->update(['balance' => max(0, $balance)]);
        }

        // Create some transfers between accounts
        if ($accounts->count() >= 2) {
            for ($i = 0; $i < 5; $i++) {
                $from = $accounts->random();
                $to = $accounts->where('id', '!=', $from->id)->random();
                $amount = rand(50000, 500000);
                $fee = rand(0, 1) ? rand(500, 5000) : 0;

                if ((float) $from->balance < $amount + $fee) {
                    continue;
                }

                AccountTransfer::create([
                    'instance_id'     => $instanceId,
                    'from_account_id' => $from->id,
                    'to_account_id'   => $to->id,
                    'amount'          => $amount,
                    'fee'             => $fee,
                    'notes'           => "Transfert {$from->name} → {$to->name}",
                    'user_id'         => 1,
                    'created_at'      => now()->subDays(rand(0, 30)),
                ]);

                AccountTransaction::create([
                    'account_id' => $from->id,
                    'type'       => 'transfer_out',
                    'amount'     => $amount + $fee,
                    'notes'      => "Transfert vers {$to->name}" . ($fee > 0 ? " (frais: {$fee})" : ''),
                    'user_id'    => 1,
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);

                AccountTransaction::create([
                    'account_id' => $to->id,
                    'type'       => 'transfer_in',
                    'amount'     => $amount,
                    'notes'      => "Transfert depuis {$from->name}",
                    'user_id'    => 1,
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);

                $from->decrement('balance', $amount + $fee);
                $to->increment('balance', $amount);
            }
        }
    }
}

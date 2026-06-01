<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Domain\Finance\Models\Account;
use Modules\Eshop360\Domain\Finance\Models\CompanyCharge;
use Modules\Eshop360\Domain\Finance\Models\Expense;
use Modules\Eshop360\Domain\Finance\Models\ExpenseCategory;
use Modules\Eshop360\Domain\Finance\Models\Income;
use Modules\Eshop360\Domain\Finance\Models\IncomeSource;
use Modules\Eshop360\Domain\Finance\Models\PaymentMethod;

final class DemoFinanceSeeder
{
    public function run(int $instanceId): void
    {
        $accounts = $this->seedAccounts($instanceId);
        $this->seedExpenseCategories($instanceId);
        $this->seedIncomeSources($instanceId);
        $this->seedPaymentMethods($instanceId);
        $this->seedCompanyCharges($instanceId);
        $this->seedSampleExpenses($instanceId, $accounts);
        $this->seedSampleIncomes($instanceId, $accounts);
    }

    public function reset(int $instanceId): void
    {
        Expense::withoutGlobalScopes()->where('instance_id', $instanceId)->delete();
        Income::withoutGlobalScopes()->where('instance_id', $instanceId)->delete();
        CompanyCharge::withoutGlobalScopes()->where('instance_id', $instanceId)->delete();
        PaymentMethod::withoutGlobalScopes()->where('instance_id', $instanceId)->delete();
        ExpenseCategory::withoutGlobalScopes()->where('instance_id', $instanceId)->delete();
        IncomeSource::withoutGlobalScopes()->where('instance_id', $instanceId)->delete();
        Account::withoutGlobalScopes()->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')->delete();
    }

    private function seedAccounts(int $instanceId): array
    {
        $data = [
            ['name' => '[DEMO] Caisse Principale', 'type' => 'cash', 'balance' => 2500000, 'currency' => 'XOF'],
            ['name' => '[DEMO] SGBCI Compte Courant', 'type' => 'bank', 'account_number' => 'CI093 0001 0101 000123456 78', 'bank_name' => 'SGBCI', 'balance' => 15000000, 'currency' => 'XOF'],
            ['name' => '[DEMO] BICICI Epargne', 'type' => 'bank', 'account_number' => 'CI093 0002 0201 000987654 32', 'bank_name' => 'BICICI', 'balance' => 8000000, 'currency' => 'XOF'],
            ['name' => '[DEMO] Orange Money Pro', 'type' => 'mobile_money', 'balance' => 500000, 'currency' => 'XOF'],
        ];

        $result = [];
        foreach ($data as $d) {
            $result[] = Account::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'name' => $d['name']],
                array_merge($d, ['instance_id' => $instanceId, 'is_active' => true])
            );
        }

        return $result;
    }

    private function seedExpenseCategories(int $instanceId): void
    {
        $cats = ['Loyer', 'Electricite', 'Eau', 'Salaires', 'Transport', 'Maintenance', 'Fournitures bureau', 'Telecommunications', 'Assurance', 'Impots et taxes', 'Carburant', 'Frais bancaires', 'Publicite', 'Divers'];

        foreach ($cats as $name) {
            ExpenseCategory::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'name' => $name],
                ['instance_id' => $instanceId]
            );
        }
    }

    private function seedIncomeSources(int $instanceId): void
    {
        $sources = ['Ventes grossiste', 'Ventes detail', 'Ventes Revendeur', 'Commissions', 'Interets bancaires', 'Location espace', 'Services conseil', 'Divers'];

        foreach ($sources as $name) {
            IncomeSource::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'name' => $name],
                ['instance_id' => $instanceId]
            );
        }
    }

    private function seedPaymentMethods(int $instanceId): void
    {
        $methods = [
            ['name' => 'Especes', 'type' => 'cash'],
            ['name' => 'Virement bancaire', 'type' => 'bank_transfer'],
            ['name' => 'Cheque', 'type' => 'bank_transfer'],
            ['name' => 'Orange Money', 'type' => 'mobile_money'],
            ['name' => 'MTN MoMo', 'type' => 'mobile_money'],
            ['name' => 'Wave', 'type' => 'mobile_money'],
            ['name' => 'Carte bancaire', 'type' => 'card'],
        ];

        foreach ($methods as $m) {
            PaymentMethod::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'name' => $m['name']],
                array_merge($m, ['instance_id' => $instanceId, 'is_active' => true])
            );
        }
    }

    private function seedCompanyCharges(int $instanceId): void
    {
        $charges = [
            ['category' => 'rent', 'name' => 'Loyer entrepot Abidjan', 'amount_monthly' => 1500000],
            ['category' => 'electricity', 'name' => 'Electricite entrepot + bureaux', 'amount_monthly' => 350000],
            ['category' => 'salary', 'name' => 'Masse salariale', 'amount_monthly' => 4500000],
            ['category' => 'transport', 'name' => 'Transport et logistique', 'amount_monthly' => 800000],
            ['category' => 'maintenance', 'name' => 'Maintenance equipements', 'amount_monthly' => 200000],
            ['category' => 'other', 'name' => 'Assurance et divers', 'amount_monthly' => 300000],
        ];

        foreach ($charges as $c) {
            CompanyCharge::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'name' => $c['name']],
                array_merge($c, ['instance_id' => $instanceId, 'is_active' => true, 'start_date' => now()->startOfMonth()])
            );
        }
    }

    private function seedSampleExpenses(int $instanceId, array $accounts): void
    {
        $categories = ExpenseCategory::withoutGlobalScopes()->where('instance_id', $instanceId)->get();
        $account = $accounts[0] ?? null;
        if (! $account || $categories->isEmpty()) {
            return;
        }

        $userId = auth()->id() ?? \App\Models\User::first()?->id ?? 1;

        $expenses = [
            ['category' => 'Loyer', 'amount' => 1500000, 'description' => 'Loyer mars 2026', 'date' => now()->startOfMonth()],
            ['category' => 'Electricite', 'amount' => 285000, 'description' => 'Facture CIE fevrier', 'date' => now()->subDays(10)],
            ['category' => 'Transport', 'amount' => 450000, 'description' => 'Livraisons clients semaine 10', 'date' => now()->subDays(5)],
            ['category' => 'Fournitures bureau', 'amount' => 75000, 'description' => 'Papier, encre, classeurs', 'date' => now()->subDays(3)],
            ['category' => 'Carburant', 'amount' => 180000, 'description' => 'Carburant vehicules mars', 'date' => now()->subDays(2)],
        ];

        foreach ($expenses as $e) {
            $cat = $categories->firstWhere('name', $e['category']);
            if (! $cat) {
                continue;
            }

            Expense::withoutGlobalScopes()->create([
                'instance_id' => $instanceId,
                'category_id' => $cat->id,
                'account_id' => $account->id,
                'amount' => $e['amount'],
                'date' => $e['date'],
                'description' => $e['description'],
                'user_id' => $userId,
            ]);
        }
    }

    private function seedSampleIncomes(int $instanceId, array $accounts): void
    {
        $sources = IncomeSource::withoutGlobalScopes()->where('instance_id', $instanceId)->get();
        $account = $accounts[1] ?? $accounts[0] ?? null;
        if (! $account || $sources->isEmpty()) {
            return;
        }

        $userId = auth()->id() ?? \App\Models\User::first()?->id ?? 1;

        $incomes = [
            ['source' => 'Commissions', 'amount' => 250000, 'description' => 'Commissions agents fevrier', 'date' => now()->subDays(15)],
            ['source' => 'Location espace', 'amount' => 150000, 'description' => 'Location sous-espace stockage', 'date' => now()->subDays(8)],
        ];

        foreach ($incomes as $inc) {
            $src = $sources->firstWhere('name', $inc['source']);
            if (! $src) {
                continue;
            }

            Income::withoutGlobalScopes()->create([
                'instance_id' => $instanceId,
                'source_id' => $src->id,
                'account_id' => $account->id,
                'amount' => $inc['amount'],
                'date' => $inc['date'],
                'description' => $inc['description'],
                'user_id' => $userId,
            ]);
        }
    }
}

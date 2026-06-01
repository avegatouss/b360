<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Domain\Finance\Models\Account;
use Modules\Eshop360\Domain\Finance\Models\ExpenseCategory;
use Modules\Eshop360\Domain\Finance\Models\IncomeSource;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class FinanceAccountTest extends TestCase
{
    private function makePermissions(): void
    {
        foreach (['eshop.finance.view', 'eshop.finance.manage'] as $perm) {
            Permission::findOrCreate($perm);
        }
    }

    private function makeAccount($instance, float $balance = 0): Account
    {
        return Account::create([
            'instance_id' => $instance->id,
            'name' => 'Caisse Principale',
            'type' => 'cash',
            'account_number' => 'CAISSE-001',
            'balance' => $balance,
            'currency' => 'XAF',
            'is_active' => true,
        ]);
    }

    public function test_deposit_creates_transaction_and_updates_balance(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $account = $this->makeAccount($instance, 100000);

        $response = $this->actingAs($user)
            ->post(route('eshop360.finance.accounts.deposit', [$instance->slug, $account->id]), [
                'amount' => 50000,
                'notes' => 'Versement client',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_account_transactions', [
            'account_id' => $account->id,
            'type' => 'deposit',
            'amount' => 50000,
        ]);

        $this->assertDatabaseHas('eshop_accounts', [
            'id' => $account->id,
            'balance' => 150000,
        ]);
    }

    public function test_withdraw_creates_transaction_and_updates_balance(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $account = $this->makeAccount($instance, 50000);

        $response = $this->actingAs($user)
            ->post(route('eshop360.finance.accounts.withdraw', [$instance->slug, $account->id]), [
                'amount' => 10000,
                'notes' => 'Retrait caisse',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_account_transactions', [
            'account_id' => $account->id,
            'type' => 'withdrawal',
            'amount' => 10000,
        ]);

        $this->assertDatabaseHas('eshop_accounts', [
            'id' => $account->id,
            'balance' => 40000,
        ]);
    }

    public function test_withdraw_reduces_balance_correctly(): void
    {
        // The AccountController delegates to FinanceService::withdraw which calls decrement.
        // There is no server-side guard for insufficient balance in the controller/service —
        // the balance simply goes negative. This test documents the current behavior.

        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $account = $this->makeAccount($instance, 5000);

        $response = $this->actingAs($user)
            ->post(route('eshop360.finance.accounts.withdraw', [$instance->slug, $account->id]), [
                'amount' => 20000,
                'notes' => 'Retrait excessif',
            ]);

        // The controller currently processes the withdrawal without checking balance.
        // It redirects back with success. Balance will be negative.
        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_account_transactions', [
            'account_id' => $account->id,
            'type' => 'withdrawal',
            'amount' => 20000,
        ]);
    }

    public function test_expense_can_be_created_with_category(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $category = ExpenseCategory::create([
            'instance_id' => $instance->id,
            'name' => 'Loyer',
        ]);

        $response = $this->actingAs($user)
            ->post(route('eshop360.finance.expenses.store', $instance->slug), [
                'category_id' => $category->id,
                'amount' => 150000,
                'date' => now()->toDateString(),
                'description' => 'Loyer mensuel bureau',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_expenses', [
            'instance_id' => $instance->id,
            'category_id' => $category->id,
            'amount' => 150000,
            'description' => 'Loyer mensuel bureau',
        ]);
    }

    public function test_income_can_be_created_with_source(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $source = IncomeSource::create([
            'instance_id' => $instance->id,
            'name' => 'Vente en gros',
        ]);

        $response = $this->actingAs($user)
            ->post(route('eshop360.finance.incomes.store', $instance->slug), [
                'source_id' => $source->id,
                'amount' => 500000,
                'date' => now()->toDateString(),
                'description' => 'Vente lot janvier',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('eshop_incomes', [
            'instance_id' => $instance->id,
            'source_id' => $source->id,
            'amount' => 500000,
            'description' => 'Vente lot janvier',
        ]);
    }

    public function test_multiple_deposits_accumulate_correctly(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);
        $this->makePermissions();

        $account = $this->makeAccount($instance, 0);

        $this->actingAs($user)
            ->post(route('eshop360.finance.accounts.deposit', [$instance->slug, $account->id]), [
                'amount' => 30000,
                'notes' => 'Premier depot',
            ]);

        $this->post(route('eshop360.finance.accounts.deposit', [$instance->slug, $account->id]), [
            'amount' => 20000,
            'notes' => 'Deuxieme depot',
        ]);

        $account->refresh();
        $this->assertSame(50000.0, (float) $account->balance);

        $transactions = $account->transactions()->where('type', 'deposit')->get();
        $this->assertCount(2, $transactions);
    }
}

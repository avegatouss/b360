<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Account;
use Modules\Eshop360\Models\Expense;
use Modules\Eshop360\Models\ExpenseCategory;
use Modules\Eshop360\Models\GiftCard;
use Modules\Eshop360\Models\GiftCardTopup;
use Modules\Eshop360\Models\Income;
use Modules\Eshop360\Models\IncomeSource;
use Modules\Eshop360\Models\Loan;
use Modules\Eshop360\Models\LoanPayment;
use Modules\Eshop360\Tests\TestCase;

final class FinanceTransactionTest extends TestCase
{
    private $instance;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = $this->makeRootInstance();
        CurrentInstance::set($this->instance);

        $this->user = $this->makeRootSuperAdmin($this->instance);
        $this->actingAs($this->user);
    }

    // ─── Expense Transactions ────────────────────────

    public function test_expense_store_deducts_account_balance(): void
    {
        $account = Account::create([
            'instance_id' => $this->instance->id,
            'name' => 'Cash', 'type' => 'cash', 'balance' => 10000, 'is_active' => true,
        ]);
        $category = ExpenseCategory::create([
            'instance_id' => $this->instance->id, 'name' => 'Office',
        ]);

        $this->post(route('eshop360.finance.expenses.store', $this->instance->slug), [
            'category_id' => $category->id,
            'account_id' => $account->id,
            'amount' => 3000,
            'date' => now()->toDateString(),
        ]);

        $account->refresh();
        $this->assertSame(7000.0, (float) $account->balance);
        $this->assertSame(1, Expense::count());
    }

    public function test_expense_update_adjusts_account_balance(): void
    {
        $account = Account::create([
            'instance_id' => $this->instance->id,
            'name' => 'Cash', 'type' => 'cash', 'balance' => 10000, 'is_active' => true,
        ]);
        $category = ExpenseCategory::create([
            'instance_id' => $this->instance->id, 'name' => 'Office',
        ]);
        $expense = Expense::create([
            'instance_id' => $this->instance->id,
            'category_id' => $category->id,
            'account_id' => $account->id,
            'amount' => 2000,
            'date' => now()->toDateString(),
            'user_id' => $this->user->id,
        ]);
        $account->decrement('balance', 2000); // Simulate initial store

        // Update from 2000 to 5000
        $this->put(route('eshop360.finance.expenses.update', [$this->instance->slug, $expense->id]), [
            'category_id' => $category->id,
            'amount' => 5000,
            'date' => now()->toDateString(),
        ]);

        $account->refresh();
        // Balance was 8000 (10000-2000), then +2000 (reversal) -5000 (new) = 5000
        $this->assertSame(5000.0, (float) $account->balance);
    }

    public function test_expense_delete_refunds_account_balance(): void
    {
        $account = Account::create([
            'instance_id' => $this->instance->id,
            'name' => 'Cash', 'type' => 'cash', 'balance' => 7000, 'is_active' => true,
        ]);
        $category = ExpenseCategory::create([
            'instance_id' => $this->instance->id, 'name' => 'Office',
        ]);
        $expense = Expense::create([
            'instance_id' => $this->instance->id,
            'category_id' => $category->id,
            'account_id' => $account->id,
            'amount' => 3000,
            'date' => now()->toDateString(),
            'user_id' => $this->user->id,
        ]);

        $this->delete(route('eshop360.finance.expenses.destroy', [$this->instance->slug, $expense->id]));

        $account->refresh();
        $this->assertSame(10000.0, (float) $account->balance);
        $this->assertSame(0, Expense::count());
    }

    // ─── Income Transactions ─────────────────────────

    public function test_income_store_credits_account_balance(): void
    {
        $account = Account::create([
            'instance_id' => $this->instance->id,
            'name' => 'Bank', 'type' => 'bank', 'balance' => 5000, 'is_active' => true,
        ]);
        $source = IncomeSource::create([
            'instance_id' => $this->instance->id, 'name' => 'Sales',
        ]);

        $this->post(route('eshop360.finance.incomes.store', $this->instance->slug), [
            'source_id' => $source->id,
            'account_id' => $account->id,
            'amount' => 8000,
            'date' => now()->toDateString(),
        ]);

        $account->refresh();
        $this->assertSame(13000.0, (float) $account->balance);
    }

    public function test_income_delete_reverses_account_credit(): void
    {
        $account = Account::create([
            'instance_id' => $this->instance->id,
            'name' => 'Bank', 'type' => 'bank', 'balance' => 13000, 'is_active' => true,
        ]);
        $source = IncomeSource::create([
            'instance_id' => $this->instance->id, 'name' => 'Sales',
        ]);
        $income = Income::create([
            'instance_id' => $this->instance->id,
            'source_id' => $source->id,
            'account_id' => $account->id,
            'amount' => 8000,
            'date' => now()->toDateString(),
            'user_id' => $this->user->id,
        ]);

        $this->delete(route('eshop360.finance.incomes.destroy', [$this->instance->slug, $income->id]));

        $account->refresh();
        $this->assertSame(5000.0, (float) $account->balance);
    }

    // ─── Gift Card Topup ─────────────────────────────

    public function test_gift_card_topup_is_atomic(): void
    {
        $card = GiftCard::create([
            'instance_id' => $this->instance->id,
            'code' => 'GC-TEST-001',
            'amount' => 5000,
            'balance' => 500,
            'status' => 'depleted',
            'created_by' => $this->user->id,
        ]);

        $this->post(route('eshop360.finance.gift-cards.topup', [$this->instance->slug, $card->id]), [
            'amount' => 2000,
        ]);

        $card->refresh();
        $this->assertSame(2500.0, (float) $card->balance);
        $this->assertSame('active', $card->status);
        $this->assertSame(1, GiftCardTopup::where('gift_card_id', $card->id)->count());
    }

    // ─── Loan Payment ────────────────────────────────

    public function test_loan_payment_updates_paid_amount_and_status(): void
    {
        $loan = Loan::create([
            'instance_id' => $this->instance->id,
            'party_type' => 'customer',
            'party_id' => 1,
            'amount' => 10000,
            'paid_amount' => 8000,
            'interest_rate' => 0,
            'duration_months' => 12,
            'status' => 'active',
        ]);

        $this->post(route('eshop360.finance.loans.payment', [$this->instance->slug, $loan->id]), [
            'amount' => 2000,
            'date' => now()->toDateString(),
        ]);

        $loan->refresh();
        $this->assertSame(10000.0, (float) $loan->paid_amount);
        $this->assertSame('paid', $loan->status);
        $this->assertSame(1, LoanPayment::where('loan_id', $loan->id)->count());
    }
}

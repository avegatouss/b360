<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Carbon;
use Modules\Eshop360\Models\Account;
use Modules\Eshop360\Models\AccountTransaction;
use Modules\Eshop360\Models\AccountTransfer;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\GiftCard;
use Modules\Eshop360\Models\GiftCardTopup;
use Modules\Eshop360\Models\InstallmentPlan;
use Modules\Eshop360\Models\InstallmentPayment;
use Modules\Eshop360\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceService
{
    /**
     * Record a deposit to an account
     */
    public function deposit(Account $account, float $amount, ?string $notes = null, ?int $userId = null, ?string $refType = null, ?int $refId = null): AccountTransaction
    {
        return DB::transaction(function () use ($account, $amount, $notes, $userId, $refType, $refId) {
            $account->increment('balance', $amount);
            return AccountTransaction::create([
                'account_id' => $account->id,
                'type' => 'deposit',
                'amount' => $amount,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'notes' => $notes,
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * Record a withdrawal from an account
     */
    public function withdraw(Account $account, float $amount, ?string $notes = null, ?int $userId = null, ?string $refType = null, ?int $refId = null): AccountTransaction
    {
        return DB::transaction(function () use ($account, $amount, $notes, $userId, $refType, $refId) {
            $account->decrement('balance', $amount);
            return AccountTransaction::create([
                'account_id' => $account->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'notes' => $notes,
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * Transfer between accounts
     */
    public function transfer(Account $from, Account $to, float $amount, float $fee = 0, ?string $notes = null, ?int $userId = null): AccountTransfer
    {
        return DB::transaction(function () use ($from, $to, $amount, $fee, $notes, $userId) {
            $from->decrement('balance', $amount + $fee);
            $to->increment('balance', $amount);

            AccountTransaction::create([
                'account_id' => $from->id, 'type' => 'transfer_out', 'amount' => $amount + $fee,
                'notes' => "Transfer to {$to->name}" . ($notes ? " - $notes" : ''), 'user_id' => $userId,
            ]);
            AccountTransaction::create([
                'account_id' => $to->id, 'type' => 'transfer_in', 'amount' => $amount,
                'notes' => "Transfer from {$from->name}" . ($notes ? " - $notes" : ''), 'user_id' => $userId,
            ]);

            return AccountTransfer::create([
                'instance_id' => $from->instance_id,
                'from_account_id' => $from->id,
                'to_account_id' => $to->id,
                'amount' => $amount,
                'fee' => $fee,
                'notes' => $notes,
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * Generate a gift card with unique code
     */
    public function createGiftCard(int $instanceId, float $amount, ?string $expiryDate = null, ?int $createdBy = null): GiftCard
    {
        return GiftCard::create([
            'instance_id' => $instanceId,
            'code' => strtoupper(Str::random(12)),
            'amount' => $amount,
            'balance' => $amount,
            'status' => 'active',
            'expiry_date' => $expiryDate,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Use gift card for payment - returns actual amount deducted
     */
    public function useGiftCard(GiftCard $card, float $amount, ?int $orderId = null): float
    {
        return DB::transaction(function () use ($card, $amount, $orderId) {
            $deducted = min((float) $card->balance, $amount);
            $card->decrement('balance', $deducted);
            $card->refresh();

            if ((float) $card->balance <= 0) {
                $card->update(['status' => 'depleted']);
            }

            GiftCardTopup::create([
                'gift_card_id' => $card->id,
                'amount'       => -$deducted,
                'notes'        => $orderId ? "Used for order #{$orderId}" : 'Used for payment',
                'user_id'      => auth()->id(),
            ]);

            return $deducted;
        });
    }

    /**
     * Debit customer wallet balance for a payment
     */
    public function debitWallet(Customer $customer, float $amount, ?int $orderId = null): float
    {
        return DB::transaction(function () use ($customer, $amount, $orderId) {
            $deducted = min((float) $customer->wallet_balance, $amount);
            $customer->decrement('wallet_balance', $deducted);
            return $deducted;
        });
    }

    /**
     * Record a sale payment into an account (deposit for a completed order)
     */
    public function depositForSale(Order $order, Account $account): AccountTransaction
    {
        return $this->deposit(
            $account,
            (float) $order->paid_amount,
            "Sale {$order->order_number}",
            auth()->id(),
            Order::class,
            $order->id,
        );
    }

    /**
     * Create installment plan for an order
     */
    public function createInstallmentPlan(Order $order, int $installmentsCount, string $frequency = 'monthly'): InstallmentPlan
    {
        return DB::transaction(function () use ($order, $installmentsCount, $frequency) {
            $plan = InstallmentPlan::create([
                'instance_id' => $order->instance_id,
                'order_id' => $order->id,
                'total' => $order->due_amount,
                'installments_count' => $installmentsCount,
                'frequency' => $frequency,
                'status' => 'active',
            ]);

            $amountPerInstallment = round($order->due_amount / $installmentsCount, 2);
            $dueDate = now();

            for ($i = 0; $i < $installmentsCount; $i++) {
                $dueDate = match($frequency) {
                    'weekly' => $dueDate->copy()->addWeek(),
                    'biweekly' => $dueDate->copy()->addWeeks(2),
                    default => $dueDate->copy()->addMonth(),
                };

                // Last installment gets remainder to avoid rounding issues
                $amount = ($i === $installmentsCount - 1)
                    ? $order->due_amount - ($amountPerInstallment * ($installmentsCount - 1))
                    : $amountPerInstallment;

                InstallmentPayment::create([
                    'plan_id' => $plan->id,
                    'due_date' => $dueDate,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
            }

            return $plan->load('payments');
        });
    }

    /**
     * Get profit & loss for a period, optionally with previous period for comparison
     */
    public function profitAndLoss(int $instanceId, string $from, string $to, bool $withPrev = true): array
    {
        $data = $this->computePL($instanceId, $from, $to);

        if ($withPrev) {
            // Previous period has same duration shifted back
            $duration = Carbon::parse($from)->diffInDays(Carbon::parse($to));
            $prevTo = Carbon::parse($from)->subDay()->toDateString();
            $prevFrom = Carbon::parse($prevTo)->subDays($duration)->toDateString();
            $data['prev'] = $this->computePL($instanceId, $prevFrom, $prevTo);
        }

        return $data;
    }

    private function computePL(int $instanceId, string $from, string $to): array
    {
        [$fromDateTime, $toDateTime] = $this->normalizeDateTimeRange($from, $to);
        [$fromDate, $toDate] = $this->normalizeDateRange($from, $to);

        $sales = \Modules\Eshop360\Models\Order::where('instance_id', $instanceId)
            ->whereBetween('created_at', [$fromDateTime, $toDateTime])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // COGS: sum of (cost_price * quantity) for completed order items
        $cogs = \Modules\Eshop360\Models\OrderItem::whereHas('order', function ($q) use ($instanceId, $fromDateTime, $toDateTime) {
                $q->where('instance_id', $instanceId)
                  ->where('status', 'completed')
                  ->whereBetween('created_at', [$fromDateTime, $toDateTime]);
            })
            ->join('eshop_products as p', 'p.id', '=', 'eshop_order_items.product_id')
            ->selectRaw('SUM(eshop_order_items.quantity * COALESCE(p.cost_price, 0)) as total_cogs')
            ->value('total_cogs') ?? 0;

        $purchases = \Modules\Eshop360\Models\PurchaseOrder::where('instance_id', $instanceId)
            ->whereBetween('created_at', [$fromDateTime, $toDateTime])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $expenses = \Modules\Eshop360\Models\Expense::where('instance_id', $instanceId)
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate)
            ->sum('amount');

        $incomes = \Modules\Eshop360\Models\Income::where('instance_id', $instanceId)
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate)
            ->sum('amount');

        $grossProfit = $sales - $cogs;
        $totalRevenue = $sales + $incomes;
        $totalExpenses = $purchases + $expenses;
        // Net profit = total revenue minus all expenses (purchases + operating)
        // COGS is shown as an informational metric (gross margin) but not double-counted
        $netProfit = $totalRevenue - $totalExpenses;

        return [
            'from' => $from,
            'to'   => $to,
            'revenue' => ['sales' => (float) $sales, 'other_income' => (float) $incomes, 'total' => (float) $totalRevenue],
            'expenses' => ['purchases' => (float) $purchases, 'operating_expenses' => (float) $expenses, 'total' => (float) $totalExpenses],
            'cogs'          => (float) $cogs,
            'gross_profit'  => (float) $grossProfit,
            'gross_margin'  => $sales > 0 ? round($grossProfit / $sales * 100, 1) : 0,
            'total_revenue' => (float) $totalRevenue,
            'total_expenses' => (float) $totalExpenses,
            'revenue_lines' => [
                ['label' => 'Ventes', 'amount' => (float) $sales],
                ['label' => 'Autres revenus', 'amount' => (float) $incomes],
            ],
            'expense_lines' => [
                ['label' => 'Coût des marchandises (COGS)', 'amount' => (float) $cogs],
                ['label' => 'Achats', 'amount' => (float) $purchases],
                ['label' => 'Charges operationnelles', 'amount' => (float) $expenses],
            ],
            'net_profit' => (float) $netProfit,
        ];
    }

    private function normalizeDateTimeRange(string $from, string $to): array
    {
        return [
            Carbon::parse($from)->startOfDay()->toDateTimeString(),
            Carbon::parse($to)->endOfDay()->toDateTimeString(),
        ];
    }

    private function normalizeDateRange(string $from, string $to): array
    {
        return [
            Carbon::parse($from)->toDateString(),
            Carbon::parse($to)->toDateString(),
        ];
    }
}

<?php

namespace Modules\Eshop360\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\Finance\Models\Account;
use Modules\Eshop360\Domain\Finance\Models\Expense;
use Modules\Eshop360\Domain\Finance\Models\Income;
use Modules\Eshop360\Domain\Finance\Models\Loan;
use Modules\Eshop360\Domain\Finance\Models\LoanPayment;
use Modules\Eshop360\Domain\Finance\Models\LoanSchedule;
use Modules\Eshop360\Domain\HR\Models\Employee;
use Modules\Eshop360\Domain\Purchasing\Models\Supplier;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $query = Loan::where('instance_id', $instance->id)
            ->with('payments', 'account')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->party_type, fn ($q, $p) => $q->where('party_type', $p))
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('reference', 'like', "%{$s}%")
                    ->orWhere('party_name', 'like', "%{$s}%");
            }));

        $fq = clone $query;
        $kpi = (object) [
            'total_given' => (int) (clone $fq)->where('type', 'given')->sum('amount'),
            'total_received' => (int) (clone $fq)->where('type', 'received')->sum('amount'),
            'total_paid' => (int) (clone $fq)->sum('paid_amount'),
            'total_remaining' => (int) ((clone $fq)->sum('amount') - (clone $fq)->sum('paid_amount')),
            'active' => (clone $fq)->where('status', 'active')->count(),
            'paid' => (clone $fq)->where('status', 'paid')->count(),
            'defaulted' => (clone $fq)->where('status', 'defaulted')->count(),
            'count' => (clone $fq)->count(),
        ];

        $loans = $query->latest()->paginate(25)->withQueryString();

        // Lookups for the creation modal
        $customers = Customer::where('instance_id', $instance->id)->where('is_active', true)
            ->orderBy('name')->get(['id', 'name', 'code']);
        $suppliers = Supplier::where('instance_id', $instance->id)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'company']);
        $employees = Employee::where('instance_id', $instance->id)->where('status', 'active')->orderBy('name')->get(['id', 'name', 'position']);
        $accounts = Account::where('instance_id', $instance->id)->where('is_active', true)->orderBy('name')->get();

        return view('eshop360::finance.loans.index', compact('loans', 'kpi', 'customers', 'suppliers', 'employees', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:given,received',
            'party_type' => 'required|in:customer,supplier,employee,other',
            'party_id' => 'nullable|integer',
            'party_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'duration_months' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'account_id' => 'nullable|exists:eshop_accounts,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $instance = CurrentInstance::get();
        $validated['instance_id'] = $instance->id;
        $validated['status'] = 'active';
        $validated['created_by'] = auth()->id();
        $validated['reference'] = 'PRET-'.now()->format('Y').'-'.str_pad(Loan::where('instance_id', $instance->id)->count() + 1, 4, '0', STR_PAD_LEFT);

        // Calculate due date
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $validated['due_date'] = $startDate->copy()->addMonths((int) $validated['duration_months']);

        // Resolve party_id from model
        if ($validated['party_type'] !== 'other' && $validated['party_id']) {
            $modelMap = [
                'customer' => Customer::class,
                'supplier' => Supplier::class,
                'employee' => Employee::class,
            ];
            $validated['party_type'] = $modelMap[$validated['party_type']] ?? $validated['party_type'];
        }

        DB::transaction(function () use ($validated, $startDate) {
            $loan = Loan::create($validated);

            // Generate schedule (echeancier)
            $this->generateSchedule($loan, $startDate);

            // Record in accounts if linked
            if ($loan->account_id) {
                $account = Account::find($loan->account_id);
                if ($account) {
                    if ($loan->type === 'given') {
                        $account->decrement('balance', $loan->amount);
                    } else {
                        $account->increment('balance', $loan->amount);
                    }
                }
            }

            // Record as expense (given) or income (received)
            $instance = CurrentInstance::get();
            if ($loan->type === 'given') {
                Expense::create([
                    'instance_id' => $instance->id,
                    'category_id' => $this->getOrCreateCategory($instance->id, 'Prets accordes'),
                    'account_id' => $loan->account_id,
                    'amount' => $loan->amount,
                    'date' => $loan->start_date,
                    'description' => "Pret accorde #{$loan->reference} — {$loan->party_name}",
                    'user_id' => auth()->id(),
                ]);
            } else {
                Income::create([
                    'instance_id' => $instance->id,
                    'source_id' => $this->getOrCreateSource($instance->id, 'Prets recus'),
                    'account_id' => $loan->account_id,
                    'amount' => $loan->amount,
                    'date' => $loan->start_date,
                    'description' => "Pret recu #{$loan->reference} — {$loan->party_name}",
                    'user_id' => auth()->id(),
                ]);
            }
        });

        return redirect()->back()->with('success', __('Pret cree avec succes.'));
    }

    public function show(string $slug, Loan $loan)
    {
        $loan->load('payments', 'schedules', 'account', 'creator');

        return view('eshop360::finance.loans.show', compact('loan'));
    }

    public function recordPayment(Request $request, string $slug, Loan $loan)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'schedule_id' => 'nullable|exists:eshop_loan_schedules,id',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $loan) {
            LoanPayment::create([
                'loan_id' => $loan->id,
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $loan->increment('paid_amount', $validated['amount']);
            $loan->refresh();

            if ($loan->paid_amount >= $loan->amount) {
                $loan->update(['status' => 'paid']);
            }

            // Update schedule if linked
            if (! empty($validated['schedule_id'])) {
                $schedule = LoanSchedule::find($validated['schedule_id']);
                if ($schedule) {
                    $schedule->increment('paid_amount', $validated['amount']);
                    $schedule->update([
                        'status' => $schedule->paid_amount >= $schedule->total_due ? 'paid' : 'partial',
                    ]);
                }
            }

            // Record as income (loan given = receiving repayment) or expense (loan received = repaying)
            $instance = CurrentInstance::get();
            if ($loan->type === 'given') {
                Income::create([
                    'instance_id' => $instance->id,
                    'source_id' => $this->getOrCreateSource($instance->id, 'Remboursements prets'),
                    'account_id' => $loan->account_id,
                    'amount' => $validated['amount'],
                    'date' => $validated['date'],
                    'description' => "Remboursement pret #{$loan->reference} — {$loan->party_name}",
                    'user_id' => auth()->id(),
                ]);
                if ($loan->account_id) {
                    Account::find($loan->account_id)?->increment('balance', $validated['amount']);
                }
            } else {
                Expense::create([
                    'instance_id' => $instance->id,
                    'category_id' => $this->getOrCreateCategory($instance->id, 'Remboursements prets'),
                    'account_id' => $loan->account_id,
                    'amount' => $validated['amount'],
                    'date' => $validated['date'],
                    'description' => "Remboursement pret #{$loan->reference} — {$loan->party_name}",
                    'user_id' => auth()->id(),
                ]);
                if ($loan->account_id) {
                    Account::find($loan->account_id)?->decrement('balance', $validated['amount']);
                }
            }
        });

        return redirect()->back()->with('success', __('Paiement enregistre.'));
    }

    public function destroy(string $slug, Loan $loan)
    {
        $loan->delete();

        return redirect()->route('eshop360.finance.loans.index', $slug)->with('success', __('Pret supprime.'));
    }

    private function generateSchedule(Loan $loan, \Carbon\Carbon $startDate): void
    {
        $months = (int) $loan->duration_months;
        $principal = (float) $loan->amount;
        $rate = (float) $loan->interest_rate;
        $monthlyPrincipal = round($principal / $months, 2);
        $totalInterest = round($principal * ($rate / 100) * ($months / 12), 2);
        $monthlyInterest = $months > 0 ? round($totalInterest / $months, 2) : 0;

        for ($i = 1; $i <= $months; $i++) {
            LoanSchedule::create([
                'loan_id' => $loan->id,
                'installment_number' => $i,
                'due_date' => $startDate->copy()->addMonths($i),
                'principal' => $monthlyPrincipal,
                'interest' => $monthlyInterest,
                'total_due' => $monthlyPrincipal + $monthlyInterest,
                'status' => 'pending',
            ]);
        }
    }

    private function getOrCreateCategory(int $instanceId, string $name): int
    {
        return \Modules\Eshop360\Domain\Finance\Models\ExpenseCategory::firstOrCreate(
            ['instance_id' => $instanceId, 'name' => $name],
        )->id;
    }

    private function getOrCreateSource(int $instanceId, string $name): int
    {
        return \Modules\Eshop360\Domain\Finance\Models\IncomeSource::firstOrCreate(
            ['instance_id' => $instanceId, 'name' => $name],
        )->id;
    }
}

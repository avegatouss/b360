<?php

namespace Modules\Eshop360\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\Account;
use Modules\Eshop360\Models\AccountTransaction;
use Modules\Eshop360\Services\FinanceService;
use Modules\Core\Support\CurrentInstance;

class AccountController extends Controller
{
    public function __construct(private FinanceService $financeService) {}

    public function index(Request $request)
    {
        $instance = CurrentInstance::get();
        $accounts = Account::where('instance_id', $instance->id)
            ->withCount('transactions')
            ->get();

        $totalBalance = $accounts->sum('balance');

        // KPIs
        $kpi = (object) [
            'total_accounts' => $accounts->count(),
            'active'         => $accounts->where('is_active', true)->count(),
            'total_balance'  => $totalBalance,
            'total_deposits' => (int) AccountTransaction::whereIn('account_id', $accounts->pluck('id'))
                ->where('type', 'deposit')->sum('amount'),
            'total_withdrawals' => (int) AccountTransaction::whereIn('account_id', $accounts->pluck('id'))
                ->where('type', 'withdrawal')->sum('amount'),
            'total_transactions' => AccountTransaction::whereIn('account_id', $accounts->pluck('id'))->count(),
            'by_type' => [
                'cash'         => $accounts->where('type', 'cash')->sum('balance'),
                'bank'         => $accounts->where('type', 'bank')->sum('balance'),
                'mobile_money' => $accounts->where('type', 'mobile_money')->sum('balance'),
                'other'        => $accounts->whereNotIn('type', ['cash', 'bank', 'mobile_money'])->sum('balance'),
            ],
        ];

        return view('eshop360::finance.accounts.index', compact('accounts', 'totalBalance', 'kpi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:bank,cash,mobile_money,other',
            'account_number' => 'nullable|string|max:100',
            'bank_name'      => 'nullable|string|max:255',
            'balance'        => 'nullable|numeric|min:0',
            'currency'       => 'nullable|string|max:10',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        $validated['balance'] = $validated['balance'] ?? 0;
        Account::create($validated);
        return redirect()->back()->with('success', __('Compte cree avec succes.'));
    }

    public function show(Request $request, string $slug, Account $account)
    {
        $query = AccountTransaction::where('account_id', $account->id)
            ->with('user')
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->search, fn ($q, $s) => $q->where('notes', 'like', "%{$s}%"));

        // Account stats
        $allTxQuery = AccountTransaction::where('account_id', $account->id);
        $stats = (object) [
            'total_deposits'    => (int) (clone $allTxQuery)->where('type', 'deposit')->sum('amount'),
            'total_withdrawals' => (int) (clone $allTxQuery)->where('type', 'withdrawal')->sum('amount'),
            'total_transfers_in'  => (int) (clone $allTxQuery)->where('type', 'transfer_in')->sum('amount'),
            'total_transfers_out' => (int) (clone $allTxQuery)->where('type', 'transfer_out')->sum('amount'),
            'transaction_count' => (clone $allTxQuery)->count(),
            'last_transaction'  => (clone $allTxQuery)->latest()->first()?->created_at,
        ];

        $transactions = $query->latest()->paginate(30)->withQueryString();

        return view('eshop360::finance.accounts.show', compact('account', 'transactions', 'stats'));
    }

    public function deposit(Request $request, string $slug, Account $account)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes'  => 'nullable|string',
        ]);
        $this->financeService->deposit($account, $validated['amount'], $validated['notes'], auth()->id());
        return redirect()->back()->with('success', __('Depot enregistre.'));
    }

    public function withdraw(Request $request, string $slug, Account $account)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes'  => 'nullable|string',
        ]);
        $this->financeService->withdraw($account, $validated['amount'], $validated['notes'], auth()->id());
        return redirect()->back()->with('success', __('Retrait enregistre.'));
    }

    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:eshop_accounts,id',
            'to_account_id'   => 'required|exists:eshop_accounts,id|different:from_account_id',
            'amount'          => 'required|numeric|min:0.01',
            'fee'             => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);
        $from = Account::findOrFail($validated['from_account_id']);
        $to = Account::findOrFail($validated['to_account_id']);
        $this->financeService->transfer($from, $to, $validated['amount'], $validated['fee'] ?? 0, $validated['notes'], auth()->id());
        return redirect()->back()->with('success', __('Transfert effectue.'));
    }

    public function update(Request $request, string $slug, Account $account)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:bank,cash,mobile_money,other',
            'account_number' => 'nullable|string|max:100',
            'bank_name'      => 'nullable|string|max:255',
            'is_active'      => 'boolean',
        ]);
        $account->update($validated);
        return redirect()->back()->with('success', __('Compte mis a jour.'));
    }

    public function destroy(string $slug, Account $account)
    {
        $account->delete();
        return redirect()->back()->with('success', __('Compte supprime.'));
    }
}

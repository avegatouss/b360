<?php

namespace Modules\Eshop360\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Events\ReportDataChanged;
use Modules\Eshop360\Models\Expense;
use Modules\Eshop360\Models\ExpenseCategory;
use Modules\Eshop360\Models\Account;
use Modules\Core\Support\CurrentInstance;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $query = Expense::where('instance_id', $instance->id)
            ->with('category', 'account', 'user')
            ->when($request->category_id, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->account_id, fn ($q, $a) => $q->where('account_id', $a))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('date', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('date', '<=', $d))
            ->when($request->search, fn ($q, $s) => $q->where('description', 'like', "%{$s}%"));

        $fq = clone $query;
        $kpi = (object) [
            'total'       => (int) (clone $fq)->sum('amount'),
            'count'       => (clone $fq)->count(),
            'month_total' => (int) Expense::where('instance_id', $instance->id)->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount'),
            'avg'         => (int) (clone $fq)->avg('amount'),
            'by_category' => ExpenseCategory::where('instance_id', $instance->id)
                ->withCount('expenses')
                ->withSum('expenses', 'amount')
                ->having('expenses_count', '>', 0)
                ->orderByDesc('expenses_sum_amount')
                ->limit(5)
                ->get(),
        ];

        $expenses = $query->latest('date')->paginate(25)->withQueryString();
        $categories = ExpenseCategory::where('instance_id', $instance->id)->orderBy('name')->get();
        $accounts = Account::where('instance_id', $instance->id)->where('is_active', true)->orderBy('name')->get();

        return view('eshop360::finance.expenses.index', compact('expenses', 'categories', 'accounts', 'kpi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:eshop_expense_categories,id,instance_id,' . CurrentInstance::idOrFail(),
            'account_id' => 'nullable|exists:eshop_accounts,id,instance_id,' . CurrentInstance::idOrFail(),
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        $validated['user_id'] = auth()->id();

        if ($request->hasFile('receipt')) {
            $validated['receipt'] = $request->file('receipt')->store('receipts', 'public');
        }

        DB::transaction(function () use ($validated) {
            Expense::create($validated);

            if (!empty($validated['account_id'])) {
                Account::find($validated['account_id'])?->decrement('balance', $validated['amount']);
            }
        });

        ReportDataChanged::dispatch($validated['instance_id'], 'finance');

        return redirect()->back()->with('success', 'Expense recorded.');
    }

    public function update(Request $request, string $slug, Expense $expense)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:eshop_expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $expense) {
            if ($expense->account_id) {
                $account = Account::find($expense->account_id);
                if ($account) {
                    $account->increment('balance', $expense->amount);
                    $account->decrement('balance', $validated['amount']);
                }
            }

            $expense->update($validated);
        });

        return redirect()->back()->with('success', 'Expense updated.');
    }

    public function destroy(string $slug, Expense $expense)
    {
        DB::transaction(function () use ($expense) {
            if ($expense->account_id) {
                Account::find($expense->account_id)?->increment('balance', $expense->amount);
            }

            $expense->delete();
        });

        return redirect()->back()->with('success', 'Expense deleted.');
    }

    // Expense Categories CRUD
    public function categories()
    {
        $instance = CurrentInstance::get();
        $categories = ExpenseCategory::where('instance_id', $instance->id)
            ->withCount('expenses')
            ->withSum('expenses', 'amount')
            ->orderBy('name')
            ->get();

        $totalExpenses = Expense::where('instance_id', $instance->id)->sum('amount');
        $totalCategories = $categories->count();

        return view('eshop360::finance.expenses.categories', compact('categories', 'totalExpenses', 'totalCategories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $validated['instance_id'] = CurrentInstance::get()->id;
        ExpenseCategory::create($validated);
        return redirect()->back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, string $slug, ExpenseCategory $category)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $category->update($validated);
        return redirect()->back()->with('success', __('Categorie mise a jour.'));
    }

    public function destroyCategory(string $slug, ExpenseCategory $category)
    {
        $category->delete();
        return redirect()->back()->with('success', __('Categorie supprimee.'));
    }
}

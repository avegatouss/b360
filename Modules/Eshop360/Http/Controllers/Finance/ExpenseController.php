<?php

namespace Modules\Eshop360\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\Expense;
use Modules\Eshop360\Models\ExpenseCategory;
use Modules\Eshop360\Models\Account;
use Modules\Core\Support\CurrentInstance;

class ExpenseController extends Controller
{
    public function index()
    {
        $instance = CurrentInstance::get();
        $expenses = Expense::where('instance_id', $instance->id)
            ->with('category', 'account', 'user')
            ->latest('date')
            ->paginate(20);
        $categories = ExpenseCategory::where('instance_id', $instance->id)->get();
        $accounts = Account::where('instance_id', $instance->id)->where('is_active', true)->get();
        $totalExpenses = Expense::where('instance_id', $instance->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        return view('eshop360::finance.expenses.index', compact('expenses', 'categories', 'accounts', 'totalExpenses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:eshop_expense_categories,id',
            'account_id' => 'nullable|exists:eshop_accounts,id',
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

        Expense::create($validated);

        // Deduct from account if specified
        if (!empty($validated['account_id'])) {
            $account = Account::find($validated['account_id']);
            $account?->decrement('balance', $validated['amount']);
        }

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
        $expense->update($validated);
        return redirect()->back()->with('success', 'Expense updated.');
    }

    public function destroy(string $slug, Expense $expense)
    {
        $expense->delete();
        return redirect()->back()->with('success', 'Expense deleted.');
    }

    // Expense Categories CRUD
    public function categories()
    {
        $instance = CurrentInstance::get();
        $categories = ExpenseCategory::where('instance_id', $instance->id)->withCount('expenses')->get();
        return view('eshop360::finance.expenses.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $validated['instance_id'] = CurrentInstance::get()->id;
        ExpenseCategory::create($validated);
        return redirect()->back()->with('success', 'Category created.');
    }

    public function destroyCategory(string $slug, ExpenseCategory $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted.');
    }
}

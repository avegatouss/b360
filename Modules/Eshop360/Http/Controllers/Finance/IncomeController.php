<?php

namespace Modules\Eshop360\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\Income;
use Modules\Eshop360\Models\IncomeSource;
use Modules\Eshop360\Models\Account;
use Modules\Core\Support\CurrentInstance;

class IncomeController extends Controller
{
    public function index()
    {
        $instance = CurrentInstance::get();
        $incomes = Income::where('instance_id', $instance->id)
            ->with('source', 'account', 'user')
            ->latest('date')
            ->paginate(20);
        $sources = IncomeSource::where('instance_id', $instance->id)->get();
        $accounts = Account::where('instance_id', $instance->id)->where('is_active', true)->get();
        $totalIncomes = Income::where('instance_id', $instance->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        return view('eshop360::finance.incomes.index', compact('incomes', 'sources', 'accounts', 'totalIncomes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_id' => 'required|exists:eshop_income_sources,id',
            'account_id' => 'nullable|exists:eshop_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        $validated['user_id'] = auth()->id();

        DB::transaction(function () use ($validated) {
            Income::create($validated);

            if (!empty($validated['account_id'])) {
                Account::find($validated['account_id'])?->increment('balance', $validated['amount']);
            }
        });

        return redirect()->back()->with('success', 'Income recorded.');
    }

    public function update(Request $request, string $slug, Income $income)
    {
        $validated = $request->validate([
            'source_id' => 'required|exists:eshop_income_sources,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $income) {
            if ($income->account_id) {
                $account = Account::find($income->account_id);
                if ($account) {
                    $account->decrement('balance', $income->amount);
                    $account->increment('balance', $validated['amount']);
                }
            }

            $income->update($validated);
        });

        return redirect()->back()->with('success', 'Income updated.');
    }

    public function destroy(string $slug, Income $income)
    {
        DB::transaction(function () use ($income) {
            if ($income->account_id) {
                Account::find($income->account_id)?->decrement('balance', $income->amount);
            }

            $income->delete();
        });

        return redirect()->back()->with('success', 'Income deleted.');
    }

    public function sources()
    {
        $instance = CurrentInstance::get();
        $sources = IncomeSource::where('instance_id', $instance->id)->withCount('incomes')->get();
        return view('eshop360::finance.incomes.sources', compact('sources'));
    }

    public function storeSource(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $validated['instance_id'] = CurrentInstance::get()->id;
        IncomeSource::create($validated);
        return redirect()->back()->with('success', 'Source created.');
    }

    public function updateSource(Request $request, string $slug, IncomeSource $source)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $source->update($validated);

        return redirect()->back()->with('success', 'Source updated.');
    }

    public function destroySource(string $slug, IncomeSource $source)
    {
        $source->delete();
        return redirect()->back()->with('success', 'Source deleted.');
    }
}

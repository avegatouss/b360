<?php

namespace Modules\Eshop360\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\Loan;
use Modules\Eshop360\Models\LoanPayment;
use Modules\Core\Support\CurrentInstance;

class LoanController extends Controller
{
    public function index()
    {
        $instance = CurrentInstance::get();
        $loans = Loan::where('instance_id', $instance->id)->with('payments')->latest()->paginate(20);
        return view('eshop360::finance.loans.index', compact('loans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'party_type' => 'required|in:customer,supplier,employee,other',
            'party_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'duration_months' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        $validated['status'] = 'active';
        Loan::create($validated);
        return redirect()->back()->with('success', 'Loan created.');
    }

    public function show(string $slug, Loan $loan)
    {
        $loan->load('payments');
        return view('eshop360::finance.loans.show', compact('loan'));
    }

    public function recordPayment(Request $request, string $slug, Loan $loan)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        LoanPayment::create([
            'loan_id' => $loan->id,
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $loan->increment('paid_amount', $validated['amount']);
        if ($loan->paid_amount >= $loan->amount) {
            $loan->update(['status' => 'paid']);
        }

        return redirect()->back()->with('success', 'Payment recorded.');
    }

    public function destroy(string $slug, Loan $loan)
    {
        $loan->delete();
        return redirect()->back()->with('success', 'Loan deleted.');
    }
}

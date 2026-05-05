{{-- Widget: Finance Overview --}}
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $instanceId = $instance?->id ?? 0;
    $slug = $instance->slug ?? '';
    $month = now()->month;
    $year = now()->year;

    $monthlyIncome = round((float) \Modules\Eshop360\Domain\Finance\Models\Income::where('instance_id', $instanceId)
        ->whereMonth('date', $month)->whereYear('date', $year)->sum('amount'), 2);
    $monthlyExpense = round((float) \Modules\Eshop360\Domain\Finance\Models\Expense::where('instance_id', $instanceId)
        ->whereMonth('date', $month)->whereYear('date', $year)->sum('amount'), 2);
    $accountsBalance = round((float) \Modules\Eshop360\Domain\Finance\Models\Account::where('instance_id', $instanceId)
        ->where('is_active', true)->sum('balance'), 2);
    $netMonth = round($monthlyIncome - $monthlyExpense, 2);
@endphp
<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-wallet me-2 text-success"></i>Finance du mois</h6>
        <a href="{{ route('eshop360.finance.accounts.index', $slug) }}" class="btn btn-sm btn-outline-primary">Comptes</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6">
                <div class="text-muted">Revenus</div>
                <div class="fw-bold fs-4 text-success">{{ number_format($monthlyIncome, 0, ',', ' ') }}</div>
            </div>
            <div class="col-6">
                <div class="text-muted">Depenses</div>
                <div class="fw-bold fs-4 text-danger">{{ number_format($monthlyExpense, 0, ',', ' ') }}</div>
            </div>
            <div class="col-6">
                <div class="text-muted">Resultat net</div>
                <div class="fw-bold fs-4 {{ $netMonth >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netMonth, 0, ',', ' ') }}</div>
            </div>
            <div class="col-6">
                <div class="text-muted">Solde comptes</div>
                <div class="fw-bold fs-4 text-primary">{{ number_format($accountsBalance, 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
</div>

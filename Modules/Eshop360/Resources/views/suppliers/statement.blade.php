<x-dashboard::layouts.master
    :title="'Statement — ' . ($supplier->name ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Supplier Statement">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Supplier Statement</h4>
            <h6>{{ $supplier->name }} — {{ $supplier->company ?? '' }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="#" onclick="window.print()" class="btn btn-info me-2"><i class="ti ti-printer me-1"></i>Print</a>
        <a href="{{ route('eshop360.suppliers.show', [$instance->slug ?? '', $supplier]) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('eshop360.suppliers.statement', [$instance->slug ?? '', $supplier]) }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo ?? '') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i>Filter</button>
                <a href="{{ route('eshop360.suppliers.statement', [$instance->slug ?? '', $supplier]) }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted mb-1">Total Purchases</h6>
                    <h4 class="fw-bold">{{ number_format($totals['purchases'] ?? 0, 2) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted mb-1">Total Payments</h6>
                    <h4 class="fw-bold text-success">{{ number_format($totals['payments'] ?? 0, 2) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted mb-1">Balance Due</h6>
                    <h4 class="fw-bold text-danger">{{ number_format($totals['balance'] ?? 0, 2) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted mb-1">Opening Balance</h6>
                    <h4 class="fw-bold">{{ number_format($totals['opening_balance'] ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-light">
                        <td colspan="6" class="fw-bold">Opening Balance</td>
                        <td class="text-end fw-bold">{{ number_format($totals['opening_balance'] ?? 0, 2) }}</td>
                    </tr>
                    @forelse($transactions ?? [] as $txn)
                    <tr>
                        <td>{{ $txn->date->format('d/m/Y') }}</td>
                        <td>{{ $txn->reference ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $txn->type === 'purchase' ? 'primary' : 'success' }}">{{ ucfirst($txn->type) }}</span>
                        </td>
                        <td>{{ $txn->description ?? '—' }}</td>
                        <td class="text-end">{{ $txn->type === 'purchase' ? number_format($txn->amount, 2) : '—' }}</td>
                        <td class="text-end">{{ $txn->type === 'payment' ? number_format($txn->amount, 2) : '—' }}</td>
                        <td class="text-end fw-bold">{{ number_format($txn->running_balance ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">No transactions in this period.</td></tr>
                    @endforelse
                    <tr class="table-dark">
                        <td colspan="4" class="fw-bold">Closing Balance</td>
                        <td class="text-end fw-bold">{{ number_format($totals['total_debit'] ?? 0, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($totals['total_credit'] ?? 0, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($totals['balance'] ?? 0, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>

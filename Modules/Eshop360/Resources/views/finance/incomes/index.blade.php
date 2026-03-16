<x-dashboard::layouts.master
    :title="'Incomes — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Incomes">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Incomes</h4>
            <h6>Manage your incomes</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{ URL::asset('build/img/icons/pdf.svg') }}" alt="img"></a></li>
        <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{ URL::asset('build/img/icons/excel.svg') }}" alt="img"></a></li>
    </ul>
    <div class="page-btn">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIncomeModal"><i data-feather="plus-circle" class="me-1"></i>New Income</a>
    </div>
</div>

{{-- Total Incomes Card --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Incomes</h6>
                <h3 class="fw-bold mb-0">{{ number_format($totalIncomes ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card table-list-card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <div class="search-set">
            <div class="search-input">
                <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
            </div>
        </div>
        <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
            <div class="dropdown me-2">
                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">Source</a>
                <ul class="dropdown-menu dropdown-menu-end p-3">
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">All</a></li>
                    @foreach($sources as $source)
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ $source->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Source</th>
                        <th>Account</th>
                        <th class="text-end">Amount</th>
                        <th>Reference</th>
                        <th class="no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incomes as $income)
                    <tr>
                        <td>{{ $income->date->format('d/m/Y') }}</td>
                        <td>{{ $income->description }}</td>
                        <td><span class="badge bg-info">{{ $income->source->name ?? '—' }}</span></td>
                        <td>{{ $income->account->name ?? '—' }}</td>
                        <td class="text-end fw-bold text-success">{{ number_format($income->amount, 2) }}</td>
                        <td>{{ $income->reference ?? '—' }}</td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editIncomeModal{{ $income->id }}"><i data-feather="edit" class="feather-edit"></i></a>
                                <form action="{{ route('eshop360.finance.incomes.destroy', [$instance->slug ?? '', $income]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this income?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 border-0 bg-transparent"><i data-feather="trash-2" class="feather-trash-2"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">No incomes found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($incomes->hasPages())
        <div class="p-3">{{ $incomes->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Income Modal --}}
<div class="modal fade" id="addIncomeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('eshop360.finance.incomes.store', $instance->slug ?? '') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New Income</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Source <span class="text-danger">*</span></label>
                        <select name="source_id" class="form-select" required>
                            <option value="">Select source</option>
                            @foreach($sources as $source)
                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account <span class="text-danger">*</span></label>
                        <select name="account_id" class="form-select" required>
                            <option value="">Select account</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} ({{ number_format($account->balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Income</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>

<x-dashboard::layouts.master
    :title="'Loans — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Loans">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Loans</h4>
            <h6>Manage loans given and received</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLoanModal"><i data-feather="plus-circle" class="me-1"></i>New Loan</a>
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
                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">Status</a>
                <ul class="dropdown-menu dropdown-menu-end p-3">
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">All</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Paid</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Overdue</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Cancelled</a></li>
                </ul>
            </div>
            <div class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">Type</a>
                <ul class="dropdown-menu dropdown-menu-end p-3">
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">All</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Given</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Received</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>Reference</th>
                        <th>Borrower / Lender</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Remaining</th>
                        <th>Status</th>
                        <th class="no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                    <tr>
                        <td><a href="{{ route('eshop360.finance.loans.show', [$instance->slug ?? '', $loan]) }}">{{ $loan->reference }}</a></td>
                        <td>{{ $loan->contact_name }}</td>
                        <td>
                            <span class="badge bg-{{ $loan->type === 'given' ? 'info' : 'warning' }}">{{ ucfirst($loan->type) }}</span>
                        </td>
                        <td>{{ $loan->date->format('d/m/Y') }}</td>
                        <td>{{ $loan->due_date ? $loan->due_date->format('d/m/Y') : '—' }}</td>
                        <td class="text-end fw-bold">{{ number_format($loan->amount, 2) }}</td>
                        <td class="text-end text-success">{{ number_format($loan->paid_amount ?? 0, 2) }}</td>
                        <td class="text-end text-danger fw-bold">{{ number_format($loan->remaining_amount ?? ($loan->amount - ($loan->paid_amount ?? 0)), 2) }}</td>
                        <td>
                            @php
                                $loanStatusColors = ['active' => 'primary', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $loanStatusColors[$loan->status] ?? 'secondary' }}">{{ ucfirst($loan->status) }}</span>
                        </td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="{{ route('eshop360.finance.loans.show', [$instance->slug ?? '', $loan]) }}"><i data-feather="eye" class="action-eye"></i></a>
                                @if($loan->status === 'active')
                                <form action="{{ route('eshop360.finance.loans.destroy', [$instance->slug ?? '', $loan]) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this loan?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 border-0 bg-transparent"><i data-feather="trash-2" class="feather-trash-2"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted">No loans found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($loans->hasPages())
        <div class="p-3">{{ $loans->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Loan Modal --}}
<div class="modal fade" id="addLoanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('eshop360.finance.loans.store', $instance->slug ?? '') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="given">Given (Lent)</option>
                            <option value="received">Received (Borrowed)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Name <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Loan</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>

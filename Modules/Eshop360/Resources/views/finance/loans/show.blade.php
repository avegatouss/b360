<x-dashboard::layouts.master
    :title="__('Loan') . ($loan->reference ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Loan Detail')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $loan->reference }}</h4>
            <h6>{{ __('Loan detail and payments') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.finance.loans.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Loan Information') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th>Reference</th><td>{{ $loan->reference }}</td></tr>
                    <tr>
                        <th>{{ __('Type') }}</th>
                        <td><span class="badge bg-{{ $loan->type === 'given' ? 'info' : 'warning' }}">{{ ucfirst($loan->type) }}</span></td>
                    </tr>
                    <tr><th>Contact</th><td>{{ $loan->contact_name }}</td></tr>
                    <tr><th>Date</th><td>{{ $loan->date->format('d/m/Y') }}</td></tr>
                    <tr><th>Due Date</th><td>{{ $loan->due_date ? $loan->due_date->format('d/m/Y') : '—' }}</td></tr>
                    <tr>
                        <th>{{ __('Status') }}</th>
                        <td>
                            @php
                                $loanStatusColors = ['active' => 'primary', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $loanStatusColors[$loan->status] ?? 'secondary' }}">{{ ucfirst($loan->status) }}</span>
                        </td>
                    </tr>
                    @if($loan->notes)
                    <tr><th>Notes</th><td>{{ $loan->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Summary') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr class="fw-bold"><th>Total Amount</th><td class="text-end">{{ number_format($loan->amount, 2) }}</td></tr>
                    <tr><th>Paid</th><td class="text-end text-success">{{ number_format($loan->paid_amount ?? 0, 2) }}</td></tr>
                    <tr class="border-top"><th>Remaining</th><td class="text-end text-danger fw-bold">{{ number_format($loan->remaining_amount ?? ($loan->amount - ($loan->paid_amount ?? 0)), 2) }}</td></tr>
                </table>
            </div>
        </div>

        {{-- Payment Form --}}
        @if($loan->status === 'active' || $loan->status === 'overdue')
        <div class="card">
            <div class="card-header"><h5>{{ __('Record Payment') }}</h5></div>
            <div class="card-body">
                <form action="{{ route('eshop360.finance.loans.payment', [$instance->slug ?? '', $loan]) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ __('Amount') }}<span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="{{ $loan->remaining_amount ?? ($loan->amount - ($loan->paid_amount ?? 0)) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Date') }}<span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i data-feather="dollar-sign" class="me-1"></i>{{ __('Record Payment') }}</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5>{{ __('Payment History') }}</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Date') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                                <th>{{ __('Notes') }}</th>
                                <th>{{ __('Recorded By') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loan->payments ?? [] as $index => $payment)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $payment->date->format('d/m/Y') }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->notes ?? '—' }}</td>
                                <td>{{ $payment->user->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">{{ __('No payments recorded yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>

<x-dashboard::layouts.master
    :title="__('Installment') . ($plan->reference ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Installment Plan Detail')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $plan->reference }}</h4>
            <h6>{{ __('Installment plan detail and schedule') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.finance.installments.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Plan Information') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th>Reference</th><td>{{ $plan->reference }}</td></tr>
                    <tr><th>Order</th><td>{{ $plan->order->reference ?? '—' }}</td></tr>
                    <tr><th>Customer</th><td>{{ $plan->customer->name ?? '—' }}</td></tr>
                    <tr><th>Created</th><td>{{ $plan->created_at->format('d/m/Y') }}</td></tr>
                    <tr>
                        <th>{{ __('Status') }}</th>
                        <td>
                            @php
                                $planStatusColors = ['active' => 'primary', 'completed' => 'success', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $planStatusColors[$plan->status] ?? 'secondary' }}">{{ ucfirst($plan->status) }}</span>
                        </td>
                    </tr>
                    @if($plan->notes)
                    <tr><th>Notes</th><td>{{ $plan->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Payment Summary') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr class="fw-bold"><th>Total Amount</th><td class="text-end">{{ number_format($plan->total_amount, 2) }}</td></tr>
                    <tr><th>Down Payment</th><td class="text-end">{{ number_format($plan->down_payment ?? 0, 2) }}</td></tr>
                    <tr><th>Paid</th><td class="text-end text-success">{{ number_format($plan->paid_amount ?? 0, 2) }}</td></tr>
                    <tr class="border-top"><th>Remaining</th><td class="text-end text-danger fw-bold">{{ number_format($plan->remaining_amount ?? ($plan->total_amount - ($plan->paid_amount ?? 0)), 2) }}</td></tr>
                </table>

                {{-- Progress bar --}}
                @php
                    $paidPercent = $plan->total_amount > 0 ? min(100, (($plan->paid_amount ?? 0) / $plan->total_amount) * 100) : 0;
                @endphp
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $paidPercent }}%"></div>
                </div>
                <small class="text-muted">{{ number_format($paidPercent, 1) }}% paid</small>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('Payment Schedule') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Due Date') }}</th>
                                <th class="text-end">{{ __('Amount Due') }}</th>
                                <th class="text-end">{{ __('Amount Paid') }}</th>
                                <th>{{ __('Paid Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="no-sort">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plan->installments ?? [] as $index => $installment)
                            <tr class="{{ $installment->status === 'overdue' ? 'table-danger' : ($installment->status === 'paid' ? 'table-success' : '') }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $installment->due_date->format('d/m/Y') }}</td>
                                <td class="text-end fw-bold">{{ number_format($installment->amount, 2) }}</td>
                                <td class="text-end {{ $installment->paid_amount > 0 ? 'text-success' : '' }}">{{ number_format($installment->paid_amount ?? 0, 2) }}</td>
                                <td>{{ $installment->paid_at ? $installment->paid_at->format('d/m/Y') : '—' }}</td>
                                <td>
                                    @php
                                        $instStatusColors = ['pending' => 'warning', 'paid' => 'success', 'overdue' => 'danger', 'partial' => 'info'];
                                    @endphp
                                    <span class="badge bg-{{ $instStatusColors[$installment->status] ?? 'secondary' }}">{{ ucfirst($installment->status) }}</span>
                                </td>
                                <td>
                                    @if($installment->status !== 'paid')
                                    <form action="{{ route('eshop360.finance.installments.payment', [$instance->slug ?? '', $installment]) }}" method="POST" class="d-inline" onsubmit="return confirm('Record this payment?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"><i data-feather="check" class="me-1"></i>{{ __('Pay') }}</button>
                                    </form>
                                    @else
                                    <span class="text-success"><i data-feather="check-circle"></i></span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted">{{ __('No installments scheduled.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>

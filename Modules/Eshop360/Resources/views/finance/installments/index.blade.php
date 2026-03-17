<x-dashboard::layouts.master
    :title="__('Installment Plans') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Installment Plans')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Installment Plans') }}</h4>
            <h6>{{ __('Manage payment installment plans') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal"><i data-feather="plus-circle" class="me-1"></i>{{ __('New Plan') }}</a>
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
            <div class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">{{ __('Status') }}</a>
                <ul class="dropdown-menu dropdown-menu-end p-3">
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('All') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Active') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Completed') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Overdue') }}</a></li>
                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Cancelled') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Order') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-end">{{ __('Total Amount') }}</th>
                        <th class="text-end">{{ __('Paid') }}</th>
                        <th class="text-end">{{ __('Remaining') }}</th>
                        <th class="text-center">{{ __('Installments') }}</th>
                        <th>{{ __('Next Due') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="no-sort">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                    <tr>
                        <td><a href="{{ route('eshop360.finance.installments.show', [$instance->slug ?? '', $plan]) }}">{{ $plan->reference }}</a></td>
                        <td>{{ $plan->order->reference ?? '—' }}</td>
                        <td>{{ $plan->customer->name ?? '—' }}</td>
                        <td class="text-end fw-bold">{{ number_format($plan->total_amount, 2) }}</td>
                        <td class="text-end text-success">{{ number_format($plan->paid_amount ?? 0, 2) }}</td>
                        <td class="text-end text-danger">{{ number_format($plan->remaining_amount ?? ($plan->total_amount - ($plan->paid_amount ?? 0)), 2) }}</td>
                        <td class="text-center">
                            <span class="text-success">{{ $plan->paid_installments_count ?? 0 }}</span>
                            /
                            <span>{{ $plan->total_installments_count ?? $plan->installments_count ?? 0 }}</span>
                        </td>
                        <td>{{ $plan->next_due_date ? $plan->next_due_date->format('d/m/Y') : '—' }}</td>
                        <td>
                            @php
                                $planStatusColors = ['active' => 'primary', 'completed' => 'success', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $planStatusColors[$plan->status] ?? 'secondary' }}">{{ ucfirst($plan->status) }}</span>
                        </td>
                        <td class="action-table-data">
                            <div class="edit-delete-action">
                                <a class="me-2 p-2" href="{{ route('eshop360.finance.installments.show', [$instance->slug ?? '', $plan]) }}"><i data-feather="eye" class="action-eye"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted">{{ __('No installment plans found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->hasPages())
        <div class="p-3">{{ $plans->links() }}</div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>

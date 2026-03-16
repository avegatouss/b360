<x-dashboard::layouts.master
    :title="'Purchases — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Purchases">

<div class="page-header transfer">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">Purchase</h4>
                    <h6>Manage your purchases</h6>
                </div>
            </div>
            <ul class="table-top-head">
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img
                            src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img
                            src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i
                            data-feather="rotate-ccw" class="feather-rotate-ccw"></i></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
            <div class="d-flex purchase-pg-btn">
                <div class="page-btn">
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-purchase"><i
                            data-feather="plus-circle" class="me-1"></i>Add Purchase</a>
                </div>
                <div class="page-btn import">
                    <a href="#" class="btn btn-secondary color" data-bs-toggle="modal" data-bs-target="#view-notes"><i
                            data-feather="download" class="me-2"></i>Import Purchase</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="search-set">
                    <div class="search-input">
                        <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                    </div>
                </div>
                <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                        Payment Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Paid</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Unpaid</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Overdue</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light">
                            <tr>
                                <th class="no-sort">
                                    <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>
                                </th>
                                <th>Supplier Name</th>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Payment Status</th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchases as $purchase)
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td>{{ $purchase->supplier->name ?? $purchase->supplier_name ?? '—' }}</td>
                                <td>{{ $purchase->reference ?? $purchase->order_number ?? '—' }}</td>
                                <td>{{ $purchase->created_at->format('d M Y') }}</td>
                                <td>
                                    @php
                                        $pStatus = $purchase->status ?? 'pending';
                                        $pBadge = match($pStatus) {
                                            'received' => 'badges status-badge fs-10 p-1 px-2 rounded-1',
                                            'pending' => 'badges status-badge badge-pending fs-10 p-1 px-2 rounded-1',
                                            'ordered' => 'badges status-badge bg-warning fs-10 p-1 px-2 rounded-1',
                                            default => 'badges status-badge fs-10 p-1 px-2 rounded-1',
                                        };
                                    @endphp
                                    <span class="{{ $pBadge }}">{{ ucfirst($pStatus) }}</span>
                                </td>
                                <td>${{ number_format($purchase->total ?? 0, 2) }}</td>
                                <td>${{ number_format($purchase->paid_amount ?? 0, 2) }}</td>
                                <td>${{ number_format($purchase->due_amount ?? (($purchase->total ?? 0) - ($purchase->paid_amount ?? 0)), 2) }}</td>
                                <td>
                                    @php
                                        $payStatus = $purchase->payment_status ?? 'unpaid';
                                        $payClass = match($payStatus) {
                                            'paid' => 'p-1 pe-2 rounded-1 text-success bg-success-transparent fs-10',
                                            'unpaid' => 'p-1 pe-2 rounded-1 text-danger bg-danger-transparent fs-10',
                                            'overdue' => 'p-1 pe-2 rounded-1 text-warning bg-warning-transparent fs-10',
                                            default => 'p-1 pe-2 rounded-1 text-secondary bg-secondary-transparent fs-10',
                                        };
                                    @endphp
                                    <span class="{{ $payClass }}"><i class="ti ti-point-filled me-1 fs-11"></i>{{ ucfirst($payStatus) }}</span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="{{ route('eshop360.purchases.show', [$instance->slug ?? '', $purchase]) }}">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <form action="{{ route('eshop360.purchases.destroy', [$instance->slug ?? '', $purchase]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 border-0 bg-transparent">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No purchases found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($purchases->hasPages())
                <div class="p-3">
                    {{ $purchases->links() }}
                </div>
                @endif
            </div>
        </div>
        <!-- /product list -->

</x-dashboard::layouts.master>

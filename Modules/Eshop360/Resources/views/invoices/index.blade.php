<x-dashboard::layouts.master
    :title="'Invoices — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Invoices">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Invoices</h4>
                    <h6>Manage your stock invoices</h6>
                </div>
            </div>
            <ul class="table-top-head">
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="search-set">
                    <div class="search-input">
                        <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                    </div>
                </div>
                <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                    <div class="dropdown me-2">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Status
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
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Sort By : Last 7 Days
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Desending</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Last Month</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
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
                                <th>Invoice No</th>
                                <th>Customer</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Amount Due</th>
                                <th>Status</th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td><a href="{{ route('eshop360.invoices.show', [$instance->slug ?? '', $invoice]) }}">{{ $invoice->invoice_number }}</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);">{{ $invoice->customer->name ?? '—' }}</a>
                                    </div>
                                </td>
                                <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '—' }}</td>
                                <td>${{ number_format($invoice->total ?? 0, 2) }}</td>
                                <td>${{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
                                <td>${{ number_format($invoice->due_amount ?? (($invoice->total ?? 0) - ($invoice->paid_amount ?? 0)), 2) }}</td>
                                <td>
                                    @php
                                        $invStatus = $invoice->status ?? 'unpaid';
                                        $badgeClass = match($invStatus) {
                                            'paid' => 'badge-soft-success',
                                            'unpaid' => 'badge-soft-danger',
                                            'overdue' => 'badge-soft-warning',
                                            default => 'badge-soft-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>{{ ucfirst($invStatus) }}</span>
                                </td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{ route('eshop360.invoices.show', [$instance->slug ?? '', $invoice]) }}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <form action="{{ route('eshop360.invoices.destroy', [$instance->slug ?? '', $invoice]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 d-flex align-items-center justify-content-between border rounded bg-transparent">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No invoices found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($invoices->hasPages())
                <div class="p-3">
                    {{ $invoices->links() }}
                </div>
                @endif
            </div>
        </div>
        <!-- /product list -->

</x-dashboard::layouts.master>

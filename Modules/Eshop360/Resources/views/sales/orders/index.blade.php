<x-dashboard::layouts.master
    :title="'Orders — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Orders">

<div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4 class="fw-bold">Order List</h4>
                        <h6>Manage your orders</h6>
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

            <!-- /product list -->
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
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Payment Type</th>
                                    <th>Amount</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th class="no-sort"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ $order->order_number ?? $order->id }}</td>
                                    <td>
                                        <div class="userimgname">
                                            <a href="javascript:void(0);">{{ $order->customer->name ?? '—' }}</a>
                                        </div>
                                    </td>
                                    <td>{{ ucfirst($order->payment_type ?? $order->payment_method ?? '—') }}</td>
                                    <td>${{ number_format($order->total ?? $order->amount ?? 0, 2) }}</td>
                                    <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @php
                                            $statusClass = match($order->status ?? 'pending') {
                                                'completed', 'complete' => 'bg-success',
                                                'pending' => 'bg-cyan',
                                                'processing' => 'bg-purple',
                                                'cancelled' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="{{ $statusClass }} fs-10 text-white p-1 rounded"><i class="ti ti-point-filled me-1"></i>{{ ucfirst($order->status ?? 'Pending') }}</span>
                                    </td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon d-flex align-items-center border rounded p-2" href="{{ route('eshop360.orders.show', [$instance->slug ?? '', $order]) }}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <form action="{{ route('eshop360.orders.destroy', [$instance->slug ?? '', $order]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 d-flex align-items-center border rounded bg-transparent">
                                                    <i data-feather="trash-2" class="feather-trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No orders found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($orders->hasPages())
                    <div class="p-3">
                        {{ $orders->links() }}
                    </div>
                    @endif
                </div>
            </div>
            <!-- /product list -->

</x-dashboard::layouts.master>

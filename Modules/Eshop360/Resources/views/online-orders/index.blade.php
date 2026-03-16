<x-dashboard::layouts.master
    :title="__('eshop360::eshop.online_orders') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="{{ __('eshop360::eshop.online_orders') }}">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('eshop360::eshop.online_orders') }}</h4>
                        <h6>{{ __('eshop360::eshop.online_orders_management') }}</h6>
                    </div>
                </div>
                <ul class="table-top-head">
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('eshop360::eshop.export_pdf') }}"><img src="{{ URL::asset('build/img/icons/pdf.svg') }}" alt="img"></a>
                    </li>
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('eshop360::eshop.export_excel') }}"><img src="{{ URL::asset('build/img/icons/excel.svg') }}" alt="img"></a>
                    </li>
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('eshop360::eshop.refresh') }}"><i class="ti ti-refresh"></i></a>
                    </li>
                </ul>
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label>
                                    </th>
                                    <th>{{ __('eshop360::eshop.reference') }}</th>
                                    <th>{{ __('eshop360::eshop.customer') }}</th>
                                    <th>{{ __('eshop360::eshop.date') }}</th>
                                    <th>{{ __('eshop360::eshop.total') }}</th>
                                    <th>{{ __('eshop360::eshop.status') }}</th>
                                    <th class="no-sort">{{ __('eshop360::eshop.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label>
                                    </td>
                                    <td><a href="{{ route('eshop360.online-orders.show', [$instance->slug ?? '', $order]) }}">{{ $order->reference ?? $order->order_number ?? $order->id }}</a></td>
                                    <td>{{ $order->customer->name ?? $order->customer_name ?? '---' }}</td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="fw-bold">{{ number_format($order->total ?? 0, 2) }}</td>
                                    <td>
                                        @php
                                            $statusClass = match($order->status ?? 'pending') {
                                                'completed', 'delivered' => 'bg-success',
                                                'pending' => 'bg-warning',
                                                'processing', 'confirmed' => 'bg-info',
                                                'shipped' => 'bg-purple',
                                                'cancelled', 'refunded' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($order->status ?? 'pending') }}</span>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{ route('eshop360.online-orders.show', [$instance->slug ?? '', $order]) }}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">{{ __('eshop360::eshop.portal_no_online_orders') }}</td>
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
        </div>
    </div>

</x-dashboard::layouts.master>

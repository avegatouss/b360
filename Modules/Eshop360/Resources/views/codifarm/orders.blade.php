<x-dashboard::layouts.master
    :title="'Codifarm Orders — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Codifarm Orders">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Codifarm Orders</h4>
            <h6>All orders processed through Codifarm</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.codifarm.dashboard', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back to Dashboard</a>
    </div>
</div>

<div class="card table-list-card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <form method="GET" action="{{ route('eshop360.codifarm.orders', $instance->slug ?? '') }}" class="row g-3 w-100">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Reference ou client">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach(['pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'refunded' => 'Refunded'] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </div>
        </form>
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
                        <th>Order Ref</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Margin</th>
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
                        <td><strong>{{ $order->reference ?? $order->id }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->customer->name ?? $order->customer_name ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($order->total ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($order->codifarmMarginLog?->total_margin ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>
                            @if(($order->status ?? '') === 'completed')
                                <span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-success fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Completed</span>
                            @elseif(($order->status ?? '') === 'pending')
                                <span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-warning fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Pending</span>
                            @elseif(($order->status ?? '') === 'cancelled')
                                <span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-danger fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Cancelled</span>
                            @else
                                <span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-secondary fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>{{ $order->status ?? '—' }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="edit-delete-action d-flex align-items-center">
                                <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{ route('eshop360.orders.show', [$instance->slug ?? '', $order]) }}">
                                    <i data-feather="eye" class="feather-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No Codifarm orders found.</td>
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

</x-dashboard::layouts.master>

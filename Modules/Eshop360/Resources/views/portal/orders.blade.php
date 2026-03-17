<x-dashboard::layouts.master
    :title="__('eshop360::eshop.portal_my_online_orders') . ' - ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="{{ __('eshop360::eshop.portal_my_online_orders') }}">

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold">{{ __('eshop360::eshop.portal_my_online_orders') }}</h4>
        <h6>{{ $customer->name }}</h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.portal.catalog', $instance->slug ?? '') }}" class="btn btn-secondary">{{ __('eshop360::eshop.portal_catalog') }}</a>
        <a href="{{ route('eshop360.portal.cart', $instance->slug ?? '') }}" class="btn btn-primary">{{ __('eshop360::eshop.portal_cart') }}</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('eshop360.portal.orders.index', $instance->slug ?? '') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="portal-status">{{ __('eshop360::eshop.status') }}</label>
                <select id="portal-status" name="status" class="form-select">
                    <option value="">{{ __('eshop360::eshop.portal_all_statuses') }}</option>
                    @foreach(['pending_validation', 'validated', 'preparing', 'prepared', 'shipping', 'delivered', 'received', 'invoiced', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ \Modules\Eshop360\Support\UiLabel::enum($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">{{ __('eshop360::eshop.filter') }}</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('eshop360::eshop.reference') }}</th>
                        <th>{{ __('eshop360::eshop.date') }}</th>
                        <th>{{ __('eshop360::eshop.portal_context') }}</th>
                        <th>{{ __('eshop360::eshop.total') }}</th>
                        <th>{{ __('eshop360::eshop.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="fw-semibold">{{ $order->reference }}</td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($order->is_codifarm)
                                    <span class="badge bg-info">{{ __('eshop360::eshop.portal_codifarm') }}</span>
                                @elseif($order->channel)
                                    <span class="badge bg-secondary">{{ $order->channel->name }}</span>
                                @else
                                    <span class="text-muted">{{ __('eshop360::eshop.portal_standard') }}</span>
                                @endif
                            </td>
                            <td>{{ number_format((float) $order->total, 2) }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ \Modules\Eshop360\Support\UiLabel::enum($order->status) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('eshop360.portal.orders.show', [$instance->slug ?? '', $order]) }}" class="btn btn-sm btn-outline-primary">{{ __('eshop360::eshop.portal_open') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">{{ __('eshop360::eshop.portal_no_online_orders') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
    @endif
</div>

</x-dashboard::layouts.master>

@extends('eshop360::channel-shop.layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">{{ __('My Orders') }}</h2>
        <p class="text-muted mb-0">{{ $channel->name }}</p>
    </div>
    <a href="{{ route('eshop360.channel-shop.catalog', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}" class="btn btn-shop">
        {{ __('Browse Catalog') }}
    </a>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('eshop360.channel-shop.orders', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="order-status">{{ __('Status') }}</label>
                <select id="order-status" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach(['pending_validation', 'validated', 'preparing', 'prepared', 'shipping', 'delivered', 'received', 'invoiced', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ \Modules\Eshop360\Support\UiLabel::enum($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">{{ __('Filter') }}</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="fw-semibold">{{ $order->reference }}</td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ number_format((float) $order->total, 2) }}</td>
                            <td>
                                @php
                                    $badgeColors = [
                                        'pending_validation' => 'warning',
                                        'validated' => 'info',
                                        'preparing' => 'primary',
                                        'prepared' => 'primary',
                                        'shipping' => 'info',
                                        'delivered' => 'success',
                                        'received' => 'success',
                                        'invoiced' => 'secondary',
                                        'cancelled' => 'danger',
                                    ];
                                    $color = $badgeColors[$order->status] ?? 'light';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ \Modules\Eshop360\Support\UiLabel::enum($order->status) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('eshop360.channel-shop.orders.show', [$instance->slug ?? '', $channel->slug ?? $channel->id, $order]) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('View') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                {{ __('No orders found.') }}
                                <div class="mt-2">
                                    <a href="{{ route('eshop360.channel-shop.catalog', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">{{ __('Start shopping') }}</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection

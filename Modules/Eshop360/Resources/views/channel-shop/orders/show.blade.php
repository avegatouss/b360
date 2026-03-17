@extends('eshop360::channel-shop.layout')

@section('content')
@php
    $currentIndex = collect($statusTimeline)->search(fn (array $step) => $step['key'] === $onlineOrder->status);
    $isCancelled = $onlineOrder->status === 'cancelled';
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">{{ __('Order') }} {{ $onlineOrder->reference }}</h2>
        <p class="text-muted mb-0">{{ $onlineOrder->created_at->format('Y-m-d H:i') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.channel-shop.orders', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}" class="btn btn-outline-secondary">
            &larr; {{ __('Back to Orders') }}
        </a>
        <a href="{{ route('eshop360.channel-shop.catalog', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}" class="btn btn-shop">
            {{ __('Catalog') }}
        </a>
    </div>
</div>

{{-- Status Timeline --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-3 justify-content-between">
            @foreach($statusTimeline as $index => $step)
                <div class="text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ !$isCancelled && $currentIndex !== false && $index <= $currentIndex ? 'bg-success text-white' : 'bg-light text-dark' }}" style="width:42px;height:42px;">
                        {{ $index + 1 }}
                    </div>
                    <div class="small mt-2 {{ !$isCancelled && $currentIndex !== false && $index <= $currentIndex ? 'fw-semibold' : 'text-muted' }}">
                        {{ $step['label'] }}
                    </div>
                </div>
            @endforeach
            @if($isCancelled)
                <div class="text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-danger text-white" style="width:42px;height:42px;">
                        X
                    </div>
                    <div class="small mt-2 fw-semibold text-danger">{{ __('Cancelled') }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        {{-- Order Summary --}}
        <div class="card mb-3">
            <div class="card-header fw-semibold">{{ __('Order Summary') }}</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted ps-0">{{ __('Customer') }}</th>
                        <td class="pe-0">{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-0">{{ __('Channel') }}</th>
                        <td class="pe-0">{{ $channel->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-0">{{ __('Status') }}</th>
                        <td class="pe-0">
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
                                $color = $badgeColors[$onlineOrder->status] ?? 'light';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ \Modules\Eshop360\Support\UiLabel::enum($onlineOrder->status) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-0">{{ __('Delivery Address') }}</th>
                        <td class="pe-0">{{ $onlineOrder->delivery_address ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-0">{{ __('Notes') }}</th>
                        <td class="pe-0">{{ $onlineOrder->notes ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-0">{{ __('Total') }}</th>
                        <td class="pe-0 fw-bold fs-5">{{ number_format((float) $onlineOrder->total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Confirm Reception --}}
        @if($onlineOrder->status === 'delivered')
            <div class="card mb-3">
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('Your order has been delivered. Please confirm reception below.') }}</p>
                    <form method="POST" action="{{ route('eshop360.channel-shop.orders.confirm', [$instance->slug ?? '', $channel->slug ?? $channel->id, $onlineOrder]) }}">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            {{ __('Confirm Reception') }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-8">
        {{-- Order Items --}}
        <div class="card">
            <div class="card-header fw-semibold">{{ __('Items') }}</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('SKU') }}</th>
                                <th>{{ __('Unit Price') }}</th>
                                <th>{{ __('Quantity') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($onlineOrder->items as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item->product->name ?? __('Product') }}</td>
                                    <td><code>{{ $item->product->sku ?? '-' }}</code></td>
                                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((float) $item->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">{{ __('No items found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-semibold">{{ __('Subtotal') }}</td>
                                <td class="text-end">{{ number_format((float) $onlineOrder->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-semibold">{{ __('Tax') }}</td>
                                <td class="text-end">{{ number_format((float) $onlineOrder->tax_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold fs-5">{{ __('Total') }}</td>
                                <td class="text-end fw-bold fs-5">{{ number_format((float) $onlineOrder->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

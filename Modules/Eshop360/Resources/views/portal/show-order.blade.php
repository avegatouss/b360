<x-dashboard::layouts.master
    :title="__('eshop360::eshop.order') . ' ' . ($onlineOrder->reference ?? $onlineOrder->id) . ' - ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="{{ __('eshop360::eshop.portal_order_detail') }}">

@php
    $currentIndex = collect($statusTimeline)->search(fn (array $step) => $step['key'] === $onlineOrder->status);
    $isCancelled = $onlineOrder->status === 'cancelled';
@endphp

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold">{{ __('eshop360::eshop.order') }} {{ $onlineOrder->reference }}</h4>
        <h6>{{ $onlineOrder->created_at->format('Y-m-d H:i') }}</h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.portal.orders.index', $instance->slug ?? '') }}" class="btn btn-secondary">{{ __('eshop360::eshop.portal_back_to_orders') }}</a>
        <a href="{{ route('eshop360.portal.catalog', $instance->slug ?? '') }}" class="btn btn-outline-primary">{{ __('eshop360::eshop.portal_catalog') }}</a>
    </div>
</div>

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
                    <div class="small mt-2 fw-semibold text-danger">{{ __('eshop360::eshop.portal_cancelled') }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ __('eshop360::eshop.portal_summary') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th>{{ __('eshop360::eshop.customer') }}</th><td>{{ $customer->name }}</td></tr>
                    <tr><th>{{ __('eshop360::eshop.status') }}</th><td>{{ \Modules\Eshop360\Support\UiLabel::enum($onlineOrder->status) }}</td></tr>
                    <tr>
                        <th>{{ __('eshop360::eshop.portal_context') }}</th>
                        <td>
                            @if($onlineOrder->is_codifarm)
                                {{ __('eshop360::eshop.portal_codifarm') }}
                            @elseif($onlineOrder->channel)
                                {{ $onlineOrder->channel->name }}
                            @else
                                {{ __('eshop360::eshop.portal_standard') }}
                            @endif
                        </td>
                    </tr>
                    <tr><th>{{ __('eshop360::eshop.portal_delivery_address') }}</th><td>{{ $onlineOrder->delivery_address ?: '-' }}</td></tr>
                    <tr><th>{{ __('eshop360::eshop.notes') }}</th><td>{{ $onlineOrder->notes ?: '-' }}</td></tr>
                    <tr><th>{{ __('eshop360::eshop.total') }}</th><td class="fw-semibold">{{ number_format((float) $onlineOrder->total, 2) }}</td></tr>
                </table>
            </div>
        </div>

        @if($onlineOrder->status === 'delivered')
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('eshop360.portal.orders.received', [$instance->slug ?? '', $onlineOrder]) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success w-100">{{ __('eshop360::eshop.portal_confirm_reception') }}</button>
                    </form>
                </div>
            </div>
        @endif

        @if(in_array($onlineOrder->status, ['pending_validation', 'validated'], true))
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('eshop360.portal.orders.cancel', [$instance->slug ?? '', $onlineOrder]) }}" onsubmit="return confirm('{{ __('eshop360::eshop.portal_cancel_order_confirmation') }}')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-danger w-100">{{ __('eshop360::eshop.portal_cancel_order') }}</button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ __('Items') }}</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('SKU') }}</th>
                                <th>{{ __('Unit price') }}</th>
                                <th>{{ __('Quantity') }}</th>
                                <th>{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($onlineOrder->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'Product' }}</td>
                                    <td><code>{{ $item->product->sku ?? '-' }}</code></td>
                                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="fw-semibold">{{ number_format((float) $item->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">{{ __('No items found for this order.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>

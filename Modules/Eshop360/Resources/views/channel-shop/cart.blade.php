@extends('eshop360::channel-shop.layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">{{ __('Shopping Cart') }}</h2>
        <p class="text-muted mb-0">{{ $channel->name }}</p>
    </div>
    <a href="{{ route('eshop360.channel-shop.catalog', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}" class="btn btn-outline-secondary">
        &larr; {{ __('Continue Shopping') }}
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Unit Price') }}</th>
                                <th style="width: 180px;">{{ __('Quantity') }}</th>
                                <th>{{ __('Total') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cart as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item['name'] }}</div>
                                        <div class="small text-muted"><code>{{ $item['sku'] }}</code></div>
                                    </td>
                                    <td>
                                        {{ number_format((float) $item['unit_price'], 2) }}
                                        @if((float) ($item['original_price'] ?? 0) > (float) $item['unit_price'])
                                            <div class="small text-muted text-decoration-line-through">{{ number_format((float) $item['original_price'], 2) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('eshop360.channel-shop.cart.update', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}" class="d-flex gap-2">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                            <input type="number" name="quantity" min="1" value="{{ $item['quantity'] }}" class="form-control" style="width: 80px;">
                                            <button type="submit" class="btn btn-sm btn-light">{{ __('Update') }}</button>
                                        </form>
                                    </td>
                                    <td class="fw-semibold">{{ number_format((float) $item['total'], 2) }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('eshop360.channel-shop.cart.remove', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Remove') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        {{ __('Your cart is empty.') }}
                                        <div class="mt-2">
                                            <a href="{{ route('eshop360.channel-shop.catalog', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">{{ __('Browse catalog') }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header fw-semibold">{{ __('Order Summary') }}</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ __('Subtotal') }}</span>
                    <strong>{{ number_format((float) $totals['subtotal'], 2) }}</strong>
                </div>
                @if((float) $totals['discount'] > 0)
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>{{ __('Discount') }}</span>
                        <strong>-{{ number_format((float) $totals['discount'], 2) }}</strong>
                    </div>
                @endif
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ __('Tax') }}</span>
                    <strong>{{ number_format((float) $totals['tax'], 2) }}</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fs-5 fw-bold">{{ __('Total') }}</span>
                    <span class="fs-5 fw-bold">{{ number_format((float) $totals['total'], 2) }}</span>
                </div>

                @if(! empty($cart))
                    <a href="{{ route('eshop360.channel-shop.checkout', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}" class="btn btn-shop w-100 btn-lg">
                        {{ __('Proceed to Checkout') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

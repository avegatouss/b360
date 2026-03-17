@extends('eshop360::channel-shop.layout')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold mb-1">{{ __('Checkout') }}</h2>
    <p class="text-muted mb-0">{{ $channel->name }}</p>
</div>

<form method="POST" action="{{ route('eshop360.channel-shop.checkout.store', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">
    @csrf
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header fw-semibold">{{ __('Delivery Information') }}</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="checkout-address">{{ __('Delivery Address') }} <span class="text-danger">*</span></label>
                        <textarea id="checkout-address" name="delivery_address" rows="4" class="form-control @error('delivery_address') is-invalid @enderror" required>{{ old('delivery_address', $customer->address) }}</textarea>
                        @error('delivery_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="checkout-notes">{{ __('Order Notes') }}</label>
                        <textarea id="checkout-notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" placeholder="{{ __('Any special instructions...') }}">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold">{{ __('Order Items') }}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Unit Price') }}</th>
                                    <th>{{ __('Qty') }}</th>
                                    <th class="text-end">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item['name'] }}</div>
                                            <div class="small text-muted"><code>{{ $item['sku'] }}</code></div>
                                        </td>
                                        <td>{{ number_format((float) $item['unit_price'], 2) }}</td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td class="text-end fw-semibold">{{ number_format((float) $item['total'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card sticky-top" style="top: 1rem;">
                <div class="card-header fw-semibold">{{ __('Order Summary') }}</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('Items') }}</span>
                        <span>{{ count($cart) }}</span>
                    </div>
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
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fs-5 fw-bold">{{ __('Total') }}</span>
                        <span class="fs-5 fw-bold">{{ number_format((float) $totals['total'], 2) }}</span>
                    </div>

                    <button type="submit" class="btn btn-shop w-100 btn-lg">
                        {{ __('Place Order') }}
                    </button>

                    <a href="{{ route('eshop360.channel-shop.cart', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}" class="btn btn-outline-secondary w-100 mt-2">
                        &larr; {{ __('Back to Cart') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

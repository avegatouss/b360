<x-dashboard::layouts.master
    :title="__('eshop360::eshop.portal_cart') . ' - ' . $channel->name . ' - ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('eshop360::eshop.portal_cart') . ' - ' . $channel->name">

@php
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold">{{ __('eshop360::eshop.portal_cart') }} &mdash; {{ $channel->name }}</h4>
        <h6>{{ $customer->name }}</h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.canal-portal.catalog', [$slug, $channelKey]) }}" class="btn btn-secondary">{{ __('eshop360::eshop.portal_continue_shopping') }}</a>
        <a href="{{ route('eshop360.canal-portal.orders.index', [$slug, $channelKey]) }}" class="btn btn-outline-primary">{{ __('eshop360::eshop.portal_my_orders') }}</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('eshop360::eshop.product') }}</th>
                                <th>{{ __('eshop360::eshop.portal_unit_price') }}</th>
                                <th>{{ __('eshop360::eshop.quantity') }}</th>
                                <th>{{ __('eshop360::eshop.total') }}</th>
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
                                    <td style="width: 160px;">
                                        <form method="POST" action="{{ route('eshop360.canal-portal.cart.update', [$slug, $channelKey, $item['product_id']]) }}" class="d-flex gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                            <input type="number" name="quantity" min="1" value="{{ $item['quantity'] }}" class="form-control">
                                            <button type="submit" class="btn btn-light">{{ __('eshop360::eshop.portal_update') }}</button>
                                        </form>
                                    </td>
                                    <td class="fw-semibold">{{ number_format((float) $item['total'], 2) }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('eshop360.canal-portal.cart.remove', [$slug, $channelKey, $item['product_id']]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('eshop360::eshop.portal_remove') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">{{ __('Your online cart is empty.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(! empty($cart))
                <div class="card-footer d-flex justify-content-end">
                    <form method="POST" action="{{ route('eshop360.canal-portal.cart.clear', [$slug, $channelKey]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">{{ __('eshop360::eshop.portal_clear_cart') }}</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('eshop360::eshop.portal_checkout') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span>{{ __('eshop360::eshop.subtotal') }}</span><strong>{{ number_format((float) $totals['subtotal'], 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>{{ __('eshop360::eshop.tax') }}</span><strong>{{ number_format((float) $totals['tax'], 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-3"><span>{{ __('eshop360::eshop.total') }}</span><strong>{{ number_format((float) $totals['total'], 2) }}</strong></div>

                <form method="POST" action="{{ route('eshop360.canal-portal.checkout', [$slug, $channelKey]) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="portal-delivery-address">{{ __('eshop360::eshop.portal_delivery_address') }}</label>
                        <textarea id="portal-delivery-address" name="delivery_address" rows="4" class="form-control" required>{{ old('delivery_address', $customer->address) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="portal-notes">{{ __('eshop360::eshop.notes') }}</label>
                        <textarea id="portal-notes" name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" {{ empty($cart) ? 'disabled' : '' }}>{{ __('eshop360::eshop.portal_submit_online_order') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>

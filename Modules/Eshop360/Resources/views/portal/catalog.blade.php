<x-dashboard::layouts.master
    :title="__('eshop360::eshop.portal_client_portal') . ' - ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="{{ __('eshop360::eshop.portal_client_portal') }}">

@php
    $activeChannelId = (string) (($context['channel_id'] ?? '') ?: request('channel_id', ''));
    $activeIsRevendeur = (bool) (($context['is_revendeur'] ?? false) || request()->boolean('is_revendeur'));
    $activeContextLabel = $activeIsRevendeur
        ? 'Mode Revendeur'
        : ($channels->firstWhere('id', $activeChannelId !== '' ? (int) $activeChannelId : null)?->name ?? null);
@endphp

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold">{{ __('eshop360::eshop.portal_client_portal') }}</h4>
        <h6>{{ __('eshop360::eshop.portal_welcome_back', ['name' => $customer->name]) }}</h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.portal.orders.index', $instance->slug ?? '') }}" class="btn btn-secondary">{{ __('eshop360::eshop.portal_my_orders') }}</a>
        <a href="{{ route('eshop360.portal.cart', $instance->slug ?? '') }}" class="btn btn-primary">
            {{ __('eshop360::eshop.portal_cart') }} ({{ count($cart) }})
        </a>
    </div>
</div>

@if($activeContextLabel)
    <div class="alert alert-info">
        {{ __('eshop360::eshop.portal_active_pricing_context') }}: <strong>{{ $activeContextLabel }}</strong>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ route('eshop360.portal.catalog', $instance->slug ?? '') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="portal-search">{{ __('eshop360::eshop.search') }}</label>
                        <input id="portal-search" type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('eshop360::eshop.portal_search_placeholder') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="portal-category">{{ __('eshop360::eshop.category') }}</label>
                        <select id="portal-category" name="category_id" class="form-select">
                            <option value="">{{ __('eshop360::eshop.portal_all_categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="portal-brand">{{ __('eshop360::eshop.brand') }}</label>
                        <select id="portal-brand" name="brand_id" class="form-select">
                            <option value="">{{ __('eshop360::eshop.portal_all_brands') }}</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" @selected((string) request('brand_id') === (string) $brand->id)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary w-100">{{ __('eshop360::eshop.filter') }}</button>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="portal-channel">{{ __('eshop360::eshop.portal_distribution_channel') }}</label>
                        <select id="portal-channel" name="channel_id" class="form-select" {{ $activeIsRevendeur ? 'disabled' : '' }}>
                            <option value="">{{ __('eshop360::eshop.portal_standard_pricing') }}</option>
                            @foreach($channels as $channel)
                                <option value="{{ $channel->id }}" @selected($activeChannelId === (string) $channel->id)>{{ $channel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input id="portal-revendeur" type="checkbox" name="is_revendeur" value="1" class="form-check-input" {{ $activeIsRevendeur ? 'checked' : '' }}>
                            <label class="form-check-label" for="portal-revendeur">{{ __('eshop360::eshop.portal_revendeur_pricing') }}</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="{{ route('eshop360.portal.catalog', $instance->slug ?? '') }}" class="btn btn-light w-100">{{ __('eshop360::eshop.portal_reset_context') }}</a>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($products as $product)
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1">{{ $product->name }}</h5>
                                        <div class="text-muted small"><code>{{ $product->sku }}</code></div>
                                    </div>
                                    <span class="badge bg-light text-dark">{{ $product->display_stock }} {{ __('eshop360::eshop.in_stock') }}</span>
                                </div>
                                <div class="text-muted small mb-3">
                                    {{ $product->category->name ?? __('eshop360::eshop.portal_no_category') }}
                                    @if($product->brand)
                                        · {{ $product->brand->name }}
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <div class="fw-bold fs-5">{{ number_format((float) $product->display_price, 2) }}</div>
                                    @if((float) $product->display_original_price > (float) $product->display_price)
                                        <div class="text-muted text-decoration-line-through">{{ number_format((float) $product->display_original_price, 2) }}</div>
                                    @endif
                                    <div class="small text-muted text-uppercase">{{ $product->display_price_source }}</div>
                                </div>
                                <div class="mt-auto">
                                    <form method="POST" action="{{ route('eshop360.portal.cart.add', $instance->slug ?? '') }}" class="row g-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        @if($activeChannelId !== '' && ! $activeIsRevendeur)
                                            <input type="hidden" name="channel_id" value="{{ $activeChannelId }}">
                                        @endif
                                        @if($activeIsRevendeur)
                                            <input type="hidden" name="is_revendeur" value="1">
                                        @endif
                                        <div class="col-4">
                                            <input type="number" min="1" name="quantity" value="1" class="form-control">
                                        </div>
                                        <div class="col-8">
                                            <button type="submit" class="btn btn-primary w-100" {{ $product->display_stock <= 0 ? 'disabled' : '' }}>
                                                {{ __('eshop360::eshop.portal_add_to_online_cart') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center text-muted py-5">{{ __('eshop360::eshop.portal_no_products_found') }}</div>
                        </div>
                    @endforelse
                </div>
            </div>
            @if($products->hasPages())
                <div class="card-footer">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('eshop360::eshop.portal_cart_snapshot') }}</h5>
                <a href="{{ route('eshop360.portal.cart', $instance->slug ?? '') }}" class="btn btn-sm btn-outline-primary">{{ __('eshop360::eshop.portal_open_cart') }}</a>
            </div>
            <div class="card-body">
                @forelse($cart as $item)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-semibold">{{ $item['name'] }}</div>
                            <div class="small text-muted">{{ $item['quantity'] }} x {{ number_format((float) $item['unit_price'], 2) }}</div>
                        </div>
                        <div class="fw-semibold">{{ number_format((float) $item['total'], 2) }}</div>
                    </div>
                @empty
                    <div class="text-muted">{{ __('eshop360::eshop.portal_cart_empty') }}</div>
                @endforelse
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between"><span>{{ __('eshop360::eshop.subtotal') }}</span><strong>{{ number_format((float) $totals['subtotal'], 2) }}</strong></div>
                <div class="d-flex justify-content-between"><span>{{ __('eshop360::eshop.tax') }}</span><strong>{{ number_format((float) $totals['tax'], 2) }}</strong></div>
                <div class="d-flex justify-content-between"><span>{{ __('eshop360::eshop.total') }}</span><strong>{{ number_format((float) $totals['total'], 2) }}</strong></div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>

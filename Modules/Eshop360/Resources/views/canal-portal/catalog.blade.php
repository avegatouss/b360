<x-dashboard::layouts.master
    :title="__('Portail') . ' ' . $channel->name . ' - ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Portail') . ' ' . $channel->name">

@php
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold">{{ __('Portail') }} {{ $channel->name }}</h4>
        <h6>{{ __('eshop360::eshop.portal_welcome_back', ['name' => $customer->name]) }}</h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.canal-portal.orders.index', [$slug, $channelKey]) }}" class="btn btn-secondary">{{ __('eshop360::eshop.portal_my_orders') }}</a>
        <a href="{{ route('eshop360.canal-portal.cart', [$slug, $channelKey]) }}" class="btn btn-primary">
            {{ __('eshop360::eshop.portal_cart') }} ({{ count($cart) }})
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ route('eshop360.canal-portal.catalog', [$slug, $channelKey]) }}" id="catalog-filter-form" class="row g-2 align-items-center">
                    <div class="col">
                        <input type="text" name="search" id="catalog-search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('eshop360::eshop.portal_search_placeholder') }}" autocomplete="off">
                    </div>
                    <div class="col-auto" style="min-width: 160px;">
                        <select name="category_id" class="form-select form-select-sm catalog-select2" data-placeholder="{{ __('Categorie') }}">
                            <option value=""></option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto" style="min-width: 150px;">
                        <select name="brand_id" class="form-select form-select-sm catalog-select2" data-placeholder="{{ __('Marque') }}">
                            <option value=""></option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" @selected((string) request('brand_id') === (string) $brand->id)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(request()->hasAny(['search', 'category_id', 'brand_id']))
                        <div class="col-auto">
                            <a href="{{ route('eshop360.canal-portal.catalog', [$slug, $channelKey]) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                        </div>
                    @endif
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
                                    <form method="POST" action="{{ route('eshop360.canal-portal.cart.add', [$slug, $channelKey]) }}" class="row g-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
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
                <a href="{{ route('eshop360.canal-portal.cart', [$slug, $channelKey]) }}" class="btn btn-sm btn-outline-primary">{{ __('eshop360::eshop.portal_open_cart') }}</a>
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

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.catalog-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' })
            .on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
    });
    var searchTimer = null;
    $('#catalog-search').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { $('#catalog-filter-form')[0].submit(); }, 500);
    }).on('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); $('#catalog-filter-form')[0].submit(); }
    });
});
</script>
@endpush

</x-dashboard::layouts.master>

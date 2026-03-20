<x-dashboard::layouts.master
    :title="__('eshop360::eshop.portal_client_portal') . ' - ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="{{ __('eshop360::eshop.portal_client_portal') }}">

@php
    $slug = $instance->slug ?? '';
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

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ route('eshop360.portal.catalog', $instance->slug ?? '') }}" id="catalog-filter-form" class="row g-2 align-items-center">
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
                            <a href="{{ route('eshop360.portal.catalog', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                        </div>
                    @endif
                </form>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($products as $product)
                        @php $outOfStock = $product->display_stock <= 0; @endphp
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 d-flex flex-column {{ $outOfStock ? 'opacity-75' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $product->name }}</h6>
                                        <span class="text-muted">{{ $product->category->name ?? '—' }}@if($product->brand) · {{ $product->brand->name }}@endif</span>
                                    </div>
                                    @if($outOfStock)
                                        <span class="badge bg-danger">{{ __('Rupture') }}</span>
                                    @else
                                        <span class="badge bg-success">{{ $product->display_stock }} {{ __('en stock') }}</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <div class="fw-bold fs-4 text-primary">{{ number_format((float) $product->display_price, 0, ',', ' ') }}</div>
                                    @if((float) $product->display_original_price > (float) $product->display_price)
                                        <div class="text-muted text-decoration-line-through">{{ number_format((float) $product->display_original_price, 0, ',', ' ') }}</div>
                                    @endif
                                </div>
                                <div class="mt-auto">
                                    @if($outOfStock)
                                        <div class="alert alert-warning py-2 mb-0 text-center">
                                            <i class="ti ti-package-off me-1"></i>{{ __('Produit indisponible actuellement') }}
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('eshop360.portal.cart.add', $slug) }}" class="d-flex gap-2">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="number" min="1" name="quantity" value="1" class="form-control" style="width:70px;">
                                            <button type="submit" class="btn btn-primary flex-grow-1">
                                                <i class="ti ti-shopping-cart-plus me-1"></i>{{ __('Ajouter') }}
                                            </button>
                                        </form>
                                    @endif
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
                <h5 class="mb-0"><i class="ti ti-shopping-cart me-1"></i>{{ __('Panier') }} <span class="badge bg-primary ms-1">{{ count($cart) }}</span></h5>
                <a href="{{ route('eshop360.portal.cart', $slug) }}" class="btn btn-sm btn-outline-primary">{{ __('Voir') }}</a>
            </div>
            <div class="card-body p-0">
                @forelse($cart as $key => $item)
                    <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                        <div class="flex-grow-1">
                            <div class="fw-semibold" style="font-size:.9rem;">{{ $item['name'] }}</div>
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <form method="POST" action="{{ route('eshop360.portal.cart.update', [$slug, $key]) }}" class="d-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}">
                                    <button type="submit" class="btn btn-outline-secondary px-1 py-0" style="font-size:.8rem;" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}><i class="ti ti-minus" style="font-size:.8rem;"></i></button>
                                </form>
                                <span class="fw-bold" style="min-width:24px; text-align:center; font-size:.85rem;">{{ $item['quantity'] }}</span>
                                <form method="POST" action="{{ route('eshop360.portal.cart.update', [$slug, $key]) }}" class="d-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                    <button type="submit" class="btn btn-outline-secondary px-1 py-0" style="font-size:.8rem;"><i class="ti ti-plus" style="font-size:.8rem;"></i></button>
                                </form>
                                <span class="text-muted ms-1" style="font-size:.9rem;">x {{ number_format((float) $item['unit_price'], 0, ',', ' ') }}</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold" style="font-size:.9rem;">{{ number_format((float) $item['total'], 0, ',', ' ') }}</div>
                            <form method="POST" action="{{ route('eshop360.portal.cart.remove', [$slug, $key]) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link text-danger p-0" style="font-size:.8rem;" title="{{ __('Retirer') }}"><i class="ti ti-trash" style="font-size:.8rem;"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="ti ti-shopping-cart-off fs-3 d-block mb-1"></i>
                        <span style="font-size:.9rem;">{{ __('Panier vide') }}</span>
                    </div>
                @endforelse
            </div>
            @if(count($cart) > 0)
            <div class="card-footer">
                <div class="d-flex justify-content-between mb-1"><span class="text-muted">{{ __('Sous-total') }}</span><span>{{ number_format((float) $totals['subtotal'], 0, ',', ' ') }}</span></div>
                @if($totals['tax'] > 0)
                <div class="d-flex justify-content-between mb-1"><span class="text-muted">{{ __('Taxes') }}</span><span>{{ number_format((float) $totals['tax'], 0, ',', ' ') }}</span></div>
                @endif
                <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2"><span>{{ __('Total') }}</span><span class="text-primary">{{ number_format((float) $totals['total'], 0, ',', ' ') }}</span></div>
                <div class="d-grid gap-1 mt-2">
                    <a href="{{ route('eshop360.portal.cart', $slug) }}" class="btn btn-primary btn-sm"><i class="ti ti-shopping-cart me-1"></i>{{ __('Commander') }}</a>
                    <form method="POST" action="{{ route('eshop360.portal.cart.clear', $slug) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('{{ __('Vider le panier ?') }}')"><i class="ti ti-trash me-1"></i>{{ __('Vider') }}</button>
                    </form>
                </div>
            </div>
            @endif
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

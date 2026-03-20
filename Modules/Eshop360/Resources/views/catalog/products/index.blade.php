<x-dashboard::layouts.master
    :title="__('Produits') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Produits')">

@php
    $slug = $instance->slug ?? '';
    $totalProducts = $products->total();
    $allStocks = $products->getCollection();
    $outOfStock = $allStocks->filter(fn($p) => $p->stocks->sum('quantity') <= 0)->count();
    $lowStock = $allStocks->filter(fn($p) => ($qty = $p->stocks->sum('quantity')) > 0 && $qty <= ($p->alert_quantity ?? 5))->count();
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Produits') }}</h4>
            <h6>{{ __('Gerer votre catalogue de produits') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.products', $slug) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <a href="{{ route('eshop360.products.create', $slug) }}" class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter un produit') }}
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-package fs-4 text-primary"></i></div>
                <div><div class="text-muted">{{ __('Total produits') }}</div><div class="fs-4 fw-bold">{{ $totalProducts }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-danger bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-package-off fs-4 text-danger"></i></div>
                <div><div class="text-muted">{{ __('Rupture de stock') }}</div><div class="fs-4 fw-bold text-danger">{{ $outOfStock }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-alert-triangle fs-4 text-warning"></i></div>
                <div><div class="text-muted">{{ __('Stock faible') }}</div><div class="fs-4 fw-bold text-warning">{{ $lowStock }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-currency-dollar fs-4 text-success"></i></div>
                <div>
                    <div class="text-muted">{{ __('Valeur stock') }}</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($allStocks->sum(fn($p) => $p->stocks->sum('quantity') * (float)$p->price), 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.products.index', $slug) }}" id="product-filter-form" class="row g-2 align-items-center">
            <div class="col">
                <input type="text" name="search" id="product-search-input" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Recherche...') }}" autocomplete="off">
            </div>
            <div class="col-auto" style="min-width: 150px;">
                <select name="category_id" class="form-select form-select-sm idx-select2" data-placeholder="{{ __('Categorie') }}">
                    <option value=""></option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 140px;">
                <select name="brand_id" class="form-select form-select-sm idx-select2" data-placeholder="{{ __('Marque') }}">
                    <option value=""></option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ (string) request('brand_id') === (string) $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 140px;">
                <select name="store_id" class="form-select form-select-sm idx-select2" data-placeholder="{{ __('Magasin') }}">
                    <option value=""></option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 140px;">
                <select name="warehouse_id" class="form-select form-select-sm idx-select2" data-placeholder="{{ __('Entrepot') }}">
                    <option value=""></option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string) request('warehouse_id') === (string) $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 120px;">
                <select name="is_active" class="form-select form-select-sm idx-select2" data-placeholder="{{ __('Statut') }}">
                    <option value=""></option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                </select>
            </div>
            @if(request()->hasAny(['search', 'category_id', 'brand_id', 'store_id', 'warehouse_id', 'is_active', 'price_min', 'price_max']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.products.index', $slug) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Effacer filtres') }}"><i class="ti ti-x"></i></a>
                </div>
            @endif
            <div class="col-auto ms-auto">
                <span class="text-muted">{{ $totalProducts }} {{ __('produit(s)') }}</span>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Bulk Action Bar (hidden by default) --}}
<div class="card mb-2 border-0 shadow-sm d-none" id="bulk-bar">
    <div class="card-body py-2">
        <form method="POST" action="{{ route('eshop360.products.bulk-action', $slug) }}" id="bulk-form" class="d-flex align-items-center gap-3">
            @csrf
            <div id="bulk-ids-container"></div>
            <span class="fw-bold text-primary" id="bulk-count">0</span> <span class="text-muted">{{ __('selectionne(s)') }}</span>
            <select name="action" class="form-select form-select-sm idx-select2-bulk" id="bulk-action-select" style="width: 200px;" data-placeholder="{{ __('Action') }}">
                <option value=""></option>
                <option value="activate">{{ __('Activer') }}</option>
                <option value="deactivate">{{ __('Desactiver') }}</option>
                <option value="change_category">{{ __('Changer de categorie') }}</option>
                <option value="delete">{{ __('Supprimer') }}</option>
            </select>
            <div id="bulk-category-wrap" class="d-none" style="width: 200px;">
                <select name="category_id" class="form-select form-select-sm idx-select2-bulk" data-placeholder="{{ __('Categorie cible') }}">
                    <option value=""></option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-primary" id="bulk-submit-btn" disabled>
                <i class="ti ti-check me-1"></i>{{ __('Appliquer') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" id="bulk-cancel-btn">
                <i class="ti ti-x me-1"></i>{{ __('Annuler') }}
            </button>
        </form>
    </div>
</div>

{{-- Products Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th style="width:50px;"></th>
                        <th>{{ __('Produit') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Categorie') }}</th>
                        <th class="text-end">{{ __('Prix') }}</th>
                        <th class="text-center">{{ __('Stock') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end" style="width:160px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $stock = $product->stocks->sum('quantity');
                            $alertQty = $product->alert_quantity ?? 5;
                            $stockClass = $stock <= 0 ? 'bg-danger' : ($stock <= $alertQty ? 'bg-warning' : 'bg-success');
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->id }}"></td>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="rounded" style="width:42px;height:42px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:42px;height:42px;"><i class="ti ti-package text-muted"></i></div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('eshop360.products.show', [$slug, $product]) }}" class="fw-semibold text-decoration-none d-block">{{ $product->name }}</a>
                                <div class="d-flex gap-1 mt-1">
                                    @if($product->brand)
                                        <span class="badge bg-light text-dark border" style="font-size:.75rem;">{{ $product->brand->name }}</span>
                                    @endif
                                    @if($product->variations && $product->variations->count())
                                        <span class="badge bg-info-subtle text-info" style="font-size:.75rem;">{{ $product->variations->count() }} var.</span>
                                    @endif
                                    @if($product->selling_type && $product->selling_type !== 'both')
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size:.75rem;">{{ $product->selling_type === 'pos' ? 'POS' : 'Online' }}</span>
                                    @endif
                                </div>
                            </td>
                            <td><code class="text-muted" style="font-size:.8rem;">{{ $product->sku }}</code></td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td class="text-end">
                                <div class="fw-bold">{{ number_format($product->price, 0, ',', ' ') }}</div>
                                @if(($canSeePricing ?? false) && $product->cost_price > 0)
                                    <small class="text-muted">{{ __('Cout') }}: {{ number_format($product->cost_price, 0, ',', ' ') }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $stockClass }} fs-6">{{ $stock }}</span>
                            </td>
                            <td class="text-center">
                                @if($product->is_active)
                                    <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactif') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="btn btn-sm btn-outline-info product-detail-btn" data-id="{{ $product->id }}" title="{{ __('Apercu') }}"><i class="ti ti-eye"></i></button>
                                    <a href="{{ route('eshop360.products.edit', [$slug, $product]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Modifier') }}"><i class="ti ti-edit"></i></a>
                                    <a href="{{ route('eshop360.products.variations', [$slug, $product]) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Variantes') }}"><i class="ti ti-layers-subtract"></i></a>
                                    <form action="{{ route('eshop360.products.destroy', [$slug, $product]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce produit ?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="ti ti-package-off fs-1 d-block mb-2"></i>
                                {{ __('Aucun produit trouve.') }}
                                <div class="mt-2"><a href="{{ route('eshop360.products.create', $slug) }}" class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>{{ __('Ajouter un produit') }}</a></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-3 border-top">{{ $products->links() }}</div>
        @endif
    </div>
</div>

{{-- Product Quick View Modal --}}
<div class="modal fade" id="productDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="pdm-title">{{ __('Apercu produit') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pdm-body">
                <div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>
            </div>
            <div class="modal-footer border-0 pt-0" id="pdm-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Fermer') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
jQuery(function ($) {
    // ── Select2 filters with auto-submit ──
    $('.idx-select2').each(function () {
        $(this).select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            placeholder: $(this).data('placeholder') || ''
        }).on('select2:select select2:clear', function () {
            $(this).closest('form')[0].submit();
        });
    });

    // ── Bulk action Select2 ──
    $('.idx-select2-bulk').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '', minimumResultsForSearch: Infinity });
    });

    // ── Bulk actions ──
    var $bulkBar = $('#bulk-bar'), $bulkCount = $('#bulk-count'), $bulkForm = $('#bulk-form'),
        $bulkIds = $('#bulk-ids-container'), $selectAll = $('#select-all'),
        $bulkActionSelect = $('#bulk-action-select'), $bulkCatWrap = $('#bulk-category-wrap'),
        $bulkSubmit = $('#bulk-submit-btn');

    function updateBulkBar() {
        var checked = $('.product-checkbox:checked');
        var count = checked.length;
        $bulkBar.toggleClass('d-none', count === 0);
        $bulkCount.text(count);
        $selectAll.prop('indeterminate', count > 0 && count < $('.product-checkbox').length);
        $selectAll.prop('checked', count > 0 && count === $('.product-checkbox').length);
        // Update hidden inputs
        $bulkIds.empty();
        checked.each(function () {
            $bulkIds.append('<input type="hidden" name="product_ids[]" value="' + $(this).val() + '">');
        });
        $bulkSubmit.prop('disabled', count === 0 || !$bulkActionSelect.val());
    }

    $selectAll.on('change', function () {
        $('.product-checkbox').prop('checked', this.checked);
        updateBulkBar();
    });
    $(document).on('change', '.product-checkbox', updateBulkBar);

    $bulkActionSelect.on('change', function () {
        $bulkCatWrap.toggleClass('d-none', $(this).val() !== 'change_category');
        $bulkSubmit.prop('disabled', !$(this).val() || $('.product-checkbox:checked').length === 0);
    });

    $('#bulk-cancel-btn').on('click', function () {
        $('.product-checkbox, #select-all').prop('checked', false);
        $bulkActionSelect.val('').trigger('change');
        updateBulkBar();
    });

    $bulkForm.on('submit', function (e) {
        var action = $bulkActionSelect.val();
        if (action === 'delete' && !confirm(@json(__('Supprimer les produits selectionnes ?')))) {
            e.preventDefault();
        }
    });

    // ── Auto-search with debounce ──
    var searchTimer = null;
    $('#product-search-input').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            $('#product-filter-form')[0].submit();
        }, 500);
    }).on('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); $('#product-filter-form')[0].submit(); }
    });

    // ── Product quick view modal ──
    var modal = document.getElementById('productDetailModal');
    var bsModal = modal ? new bootstrap.Modal(modal) : null;
    var titleEl = document.getElementById('pdm-title');
    var bodyEl = document.getElementById('pdm-body');
    var footerEl = document.getElementById('pdm-footer');
    var slug = @json($slug);
    var fmt = function(n) { return Math.round(n).toLocaleString('fr-FR'); };

    function loadProductDetail(productId) {
        bodyEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        titleEl.textContent = @json(__('Chargement...'));
        bsModal.show();

        fetch('/i/' + slug + '/products/' + productId, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var p = data.product;
            var statusBadge = p.is_active
                ? '<span class="badge bg-success ms-2">' + @json(__('Actif')) + '</span>'
                : '<span class="badge bg-secondary ms-2">' + @json(__('Inactif')) + '</span>';
            titleEl.innerHTML = p.name + statusBadge;

            var imgHtml = p.image
                ? '<img src="/storage/' + p.image + '" class="img-fluid rounded shadow-sm" style="max-height:180px;">'
                : '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:180px;"><i class="ti ti-package fs-1 text-muted"></i></div>';

            var stockBadge = data.total_stock <= 0 ? 'bg-danger' : (data.total_stock <= (p.alert_quantity || 5) ? 'bg-warning' : 'bg-success');

            var html = '<div class="row g-3">'
                + '<div class="col-md-4 text-center">' + imgHtml + '</div>'
                + '<div class="col-md-8">'
                + '<div class="row g-2 mb-3">'
                + '<div class="col-4"><div class="text-center p-3 bg-primary bg-opacity-10 rounded"><div class="text-muted">' + @json(__('Prix')) + '</div><div class="fw-bold fs-4 text-primary">' + fmt(p.price) + '</div></div></div>'
                + '<div class="col-4"><div class="text-center p-3 bg-success bg-opacity-10 rounded"><div class="text-muted">' + @json(__('Stock')) + '</div><div class="fw-bold fs-4"><span class="badge ' + stockBadge + ' fs-5">' + data.total_stock + '</span></div></div></div>'
                + '<div class="col-4"><div class="text-center p-3 bg-info bg-opacity-10 rounded"><div class="text-muted">' + @json(__('Vendu')) + '</div><div class="fw-bold fs-4 text-info">' + (data.sales_stats ? data.sales_stats.total_qty_sold : 0) + '</div></div></div>'
                + '</div>'
                + '<table class="table table-sm table-borderless mb-0"><tbody>'
                + '<tr><td class="text-muted" style="width:35%">' + @json(__('SKU')) + '</td><td class="fw-medium">' + (p.sku || '—') + '</td></tr>'
                + '<tr><td class="text-muted">' + @json(__('Categorie')) + '</td><td>' + (p.category ? p.category.name : '—') + '</td></tr>'
                + '<tr><td class="text-muted">' + @json(__('Marque')) + '</td><td>' + (p.brand ? p.brand.name : '—') + '</td></tr>'
                + '<tr><td class="text-muted">' + @json(__('Code-barres')) + '</td><td><code>' + (p.barcode || '—') + '</code></td></tr>'
                + '<tr><td class="text-muted">' + @json(__('Taxe')) + '</td><td>' + (p.tax_rate || 0) + ' %' + (p.tax_inclusive ? ' <span class="badge bg-secondary">TTC</span>' : '') + '</td></tr>'
                + '</tbody></table></div></div>';

            if (p.description) {
                html += '<div class="mt-3 p-3 bg-light rounded"><strong class="d-block mb-1">' + @json(__('Description')) + '</strong><div class="small">' + p.description + '</div></div>';
            }

            bodyEl.innerHTML = html;

            footerEl.innerHTML = '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">' + @json(__('Fermer')) + '</button>'
                + '<a href="/i/' + slug + '/products/' + p.id + '" class="btn btn-info"><i class="ti ti-eye me-1"></i>' + @json(__('Details complets')) + '</a>'
                + '<a href="/i/' + slug + '/products/' + p.id + '/edit" class="btn btn-primary"><i class="ti ti-edit me-1"></i>' + @json(__('Modifier')) + '</a>';
        })
        .catch(function () {
            bodyEl.innerHTML = '<div class="text-center text-danger py-4"><i class="ti ti-alert-triangle fs-1 d-block mb-2"></i>' + @json(__('Erreur de chargement.')) + '</div>';
        });
    }

    $(document).on('click', '.product-detail-btn', function (e) {
        e.preventDefault();
        loadProductDetail($(this).data('id'));
    });
});
</script>
@endpush

</x-dashboard::layouts.master>

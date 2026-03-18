<x-dashboard::layouts.master
    :title="__('Produits') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Produits')">

@php $slug = $instance->slug ?? ''; @endphp

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

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.products.index', $slug) }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Recherche nom, SKU, code-barres...') }}">
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Categorie') }}">
                    <option value="">{{ __('Toutes les categories') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="brand_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Marque') }}">
                    <option value="">{{ __('Toutes les marques') }}</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ (string) request('brand_id') === (string) $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">{{ __('Tous les statuts') }}</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'category_id', 'brand_id', 'is_active']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.products.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Products Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;"></th>
                        <th>{{ __('Produit') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Categorie') }}</th>
                        <th>{{ __('Marque') }}</th>
                        <th class="text-end">{{ __('Prix') }}</th>
                        <th class="text-center">{{ __('Stock') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end" style="width:140px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php $stock = $product->stocks->sum('quantity'); @endphp
                        <tr>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="rounded" style="width:36px;height:36px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="ti ti-package text-muted"></i></div>
                                @endif
                            </td>
                            <td>
                                <a href="javascript:void(0);" class="fw-medium text-decoration-none product-detail-btn" data-id="{{ $product->id }}">{{ $product->name }}</a>
                                @if($product->variations_count ?? $product->variations?->count())
                                    <span class="badge bg-info-subtle text-info ms-1" style="font-size:.55rem;">{{ $product->variations->count() }} var.</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $product->sku }}</td>
                            <td class="small">{{ $product->category->name ?? '—' }}</td>
                            <td class="small">{{ $product->brand->name ?? '—' }}</td>
                            <td class="text-end fw-bold">{{ number_format($product->price, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                @php $alertQty = $product->alert_quantity ?? 5; @endphp
                                <span class="badge {{ $stock <= 0 ? 'bg-danger' : ($stock <= $alertQty ? 'bg-warning' : 'bg-success') }}">{{ $stock }}</span>
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
                                    <button class="btn btn-sm btn-outline-info product-detail-btn" data-id="{{ $product->id }}" title="{{ __('Detail') }}"><i class="ti ti-eye"></i></button>
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
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="ti ti-package-off fs-1 d-block mb-2"></i>
                                {{ __('Aucun produit trouve.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-3">{{ $products->links() }}</div>
        @endif
    </div>
</div>

{{-- Product Detail Modal --}}
<div class="modal fade" id="productDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdm-title">{{ __('Detail produit') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pdm-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer" id="pdm-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Fermer') }}</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Init Select2
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('.select2-filter').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
        }).on('change', function () {
            this.closest('form').submit();
        });
    }

    // Product detail modal
    var modal = document.getElementById('productDetailModal');
    var bsModal = modal ? new bootstrap.Modal(modal) : null;
    var titleEl = document.getElementById('pdm-title');
    var bodyEl = document.getElementById('pdm-body');
    var footerEl = document.getElementById('pdm-footer');
    var slug = @json($slug);

    function loadProductDetail(productId) {
        bodyEl.textContent = '';
        var spinner = document.createElement('div');
        spinner.className = 'text-center py-5';
        var s = document.createElement('div');
        s.className = 'spinner-border text-primary';
        spinner.appendChild(s);
        bodyEl.appendChild(spinner);
        titleEl.textContent = @json(__('Chargement...'));
        bsModal.show();

        fetch('/i/' + slug + '/products/' + productId, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var p = data.product;
            titleEl.textContent = p.name;

            bodyEl.textContent = '';

            var row = document.createElement('div');
            row.className = 'row g-3';

            // Left: image
            var colImg = document.createElement('div');
            colImg.className = 'col-md-4 text-center';
            if (p.image) {
                var img = document.createElement('img');
                img.src = '/storage/' + p.image;
                img.className = 'img-fluid rounded';
                img.style.maxHeight = '200px';
                colImg.appendChild(img);
            } else {
                var noImg = document.createElement('div');
                noImg.className = 'bg-light rounded d-flex align-items-center justify-content-center';
                noImg.style.height = '200px';
                var icon = document.createElement('i');
                icon.className = 'ti ti-package fs-1 text-muted';
                noImg.appendChild(icon);
                colImg.appendChild(noImg);
            }
            row.appendChild(colImg);

            // Right: details table
            var colInfo = document.createElement('div');
            colInfo.className = 'col-md-8';

            var fields = [
                [@json(__('SKU')), p.sku || '—'],
                [@json(__('Categorie')), p.category ? p.category.name : '—'],
                [@json(__('Marque')), p.brand ? p.brand.name : '—'],
                [@json(__('Prix de vente')), Number(p.price || 0).toLocaleString('fr-FR')],
                [@json(__('Prix de revient')), Number(p.cost_price || 0).toLocaleString('fr-FR')],
                [@json(__('Taxe')), (p.tax_rate || 0) + ' %'],
                [@json(__('Stock total')), String(data.total_stock)],
                [@json(__('Stock reserve')), String(data.reserved_stock)],
                [@json(__('Unite')), p.unit || 'Piece'],
                [@json(__('Seuil alerte')), String(p.alert_quantity || 0)],
                [@json(__('Code-barres')), p.barcode || '—'],
                [@json(__('Statut')), p.is_active ? @json(__('Actif')) : @json(__('Inactif'))],
            ];

            var table = document.createElement('table');
            table.className = 'table table-sm mb-0';
            var tbody = document.createElement('tbody');
            fields.forEach(function (f) {
                var tr = document.createElement('tr');
                var th = document.createElement('td');
                th.className = 'text-muted fw-medium';
                th.style.width = '40%';
                th.textContent = f[0];
                var td = document.createElement('td');
                td.className = 'fw-bold';
                td.textContent = f[1];
                tr.appendChild(th);
                tr.appendChild(td);
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            colInfo.appendChild(table);
            row.appendChild(colInfo);
            bodyEl.appendChild(row);

            // Description
            if (p.description) {
                var descDiv = document.createElement('div');
                descDiv.className = 'mt-3 p-3 bg-light rounded';
                var descTitle = document.createElement('strong');
                descTitle.textContent = @json(__('Description'));
                descDiv.appendChild(descTitle);
                var descP = document.createElement('p');
                descP.className = 'mb-0 mt-1 small';
                descP.textContent = p.description;
                descDiv.appendChild(descP);
                bodyEl.appendChild(descDiv);
            }

            // Variations
            if (p.variations && p.variations.length > 0) {
                var varDiv = document.createElement('div');
                varDiv.className = 'mt-3';
                var varTitle = document.createElement('h6');
                varTitle.className = 'fw-bold';
                varTitle.textContent = @json(__('Variantes')) + ' (' + p.variations.length + ')';
                varDiv.appendChild(varTitle);

                var varTable = document.createElement('table');
                varTable.className = 'table table-sm table-bordered mb-0';
                var vthead = document.createElement('thead');
                vthead.className = 'table-light';
                var htr = document.createElement('tr');
                [@json(__('Nom')), @json(__('SKU')), @json(__('Prix')), @json(__('Qte'))].forEach(function (h) {
                    var hth = document.createElement('th');
                    hth.className = 'small';
                    hth.textContent = h;
                    htr.appendChild(hth);
                });
                vthead.appendChild(htr);
                varTable.appendChild(vthead);

                var vtbody = document.createElement('tbody');
                p.variations.forEach(function (v) {
                    var vtr = document.createElement('tr');
                    [v.name, v.sku || '—', v.price ? Number(v.price).toLocaleString('fr-FR') : '(produit)', String(v.quantity || 0)].forEach(function (val) {
                        var vtd = document.createElement('td');
                        vtd.className = 'small';
                        vtd.textContent = val;
                        vtr.appendChild(vtd);
                    });
                    vtbody.appendChild(vtr);
                });
                varTable.appendChild(vtbody);
                varDiv.appendChild(varTable);
                bodyEl.appendChild(varDiv);
            }

            // Footer actions
            footerEl.textContent = '';
            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'btn btn-outline-secondary';
            closeBtn.setAttribute('data-bs-dismiss', 'modal');
            closeBtn.textContent = @json(__('Fermer'));
            footerEl.appendChild(closeBtn);

            var editLink = document.createElement('a');
            editLink.href = '/i/' + slug + '/products/' + p.id + '/edit';
            editLink.className = 'btn btn-primary';
            editLink.textContent = @json(__('Modifier'));
            var editIcon = document.createElement('i');
            editIcon.className = 'ti ti-edit me-1';
            editLink.prepend(editIcon);
            footerEl.appendChild(editLink);
        })
        .catch(function () {
            bodyEl.textContent = '';
            var err = document.createElement('div');
            err.className = 'text-center text-danger py-4';
            err.textContent = @json(__('Erreur de chargement.'));
            bodyEl.appendChild(err);
        });
    }

    document.querySelectorAll('.product-detail-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            loadProductDetail(this.getAttribute('data-id'));
        });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>

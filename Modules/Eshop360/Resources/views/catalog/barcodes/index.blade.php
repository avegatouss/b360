<x-dashboard::layouts.master
    :title="__('Codes-barres') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Codes-barres')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Codes-barres') }}</h4>
            <h6>{{ __('Generer et imprimer des codes-barres produits') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.barcodes.qrcode', $slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-qrcode me-1"></i>{{ __('QR Codes') }}
        </a>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.barcodes.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom, SKU ou code-barres...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Entrepot') }}</label>
                <select name="warehouse_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous les entrepots') }}</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string) request('warehouse_id') === (string) $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Magasin') }}</label>
                <select name="store_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous les magasins') }}</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Categorie') }}</label>
                <select name="category_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Toutes') }}">
                    <option value="">{{ __('Toutes') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'warehouse_id', 'store_id', 'category_id']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.barcodes.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- Products Table --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-list me-2"></i>{{ __('Produits') }} <span class="badge bg-primary ms-1">{{ $products->total() }}</span></h6>
        @if($products->isNotEmpty())
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#print-modal">
                <i class="ti ti-printer me-1"></i>{{ __('Imprimer les codes-barres') }}
            </button>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" class="form-check-input" id="select-all-products">
                        </th>
                        <th style="width:50px;"></th>
                        <th>{{ __('Produit') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Code-barres') }}</th>
                        <th class="text-end">{{ __('Prix') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input product-cb" value="{{ $product->id }}" data-name="{{ $product->name }}" data-sku="{{ $product->sku }}">
                            </td>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="rounded" style="width:32px;height:32px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:32px;height:32px;"><i class="ti ti-package text-muted"></i></div>
                                @endif
                            </td>
                            <td class="fw-medium">{{ $product->name }}</td>
                            <td class="small"><code>{{ $product->sku }}</code></td>
                            <td>
                                @if($product->barcode)
                                    <span class="badge bg-light text-dark font-monospace">{{ $product->barcode }}</span>
                                @else
                                    <span class="text-muted small">{{ __('Non defini') }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format($product->price, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="ti ti-barcode-off fs-1 d-block mb-2"></i>
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

{{-- Print Barcode Modal --}}
<div class="modal fade" id="print-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-printer me-2"></i>{{ __('Imprimer les codes-barres') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('eshop360.barcodes.print-batch', $slug) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Produits selectionnes') }}</label>
                        <div id="selected-products-list" class="border rounded p-3" style="max-height:200px;overflow-y:auto;">
                            <p class="text-muted small mb-0">{{ __('Cochez les produits dans le tableau.') }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Par ligne') }}</label>
                            <select class="form-select form-select-sm" name="per_row">
                                <option value="2">2</option>
                                <option value="3" selected>3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Taille') }}</label>
                            <select class="form-select form-select-sm" name="size">
                                <option value="small">{{ __('Petit') }}</option>
                                <option value="medium" selected>{{ __('Moyen') }}</option>
                                <option value="large">{{ __('Grand') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Format') }}</label>
                            <select class="form-select form-select-sm" name="format">
                                <option value="Code128" selected>Code128</option>
                                <option value="EAN13">EAN-13</option>
                                <option value="Code39">Code39</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Quantite') }}</label>
                            <input type="number" class="form-control form-control-sm" name="quantity" value="1" min="1" max="100">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_name" value="1" id="opt-name" checked>
                                <label class="form-check-label small" for="opt-name">{{ __('Afficher le nom') }}</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_price" value="1" id="opt-price" checked>
                                <label class="form-check-label small" for="opt-price">{{ __('Afficher le prix') }}</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_sku" value="1" id="opt-sku">
                                <label class="form-check-label small" for="opt-sku">{{ __('Afficher le SKU') }}</label>
                            </div>
                        </div>
                    </div>

                    <div id="hidden-product-ids"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary" id="print-submit-btn" disabled>
                        <i class="ti ti-printer me-1"></i>{{ __('Generer et imprimer') }}
                    </button>
                </div>
            </form>
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
    // Select2 filters
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('.select2-filter').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
        }).on('change', function () {
            this.closest('form').submit();
        });
    }

    // Product selection for printing
    var selectAll = document.getElementById('select-all-products');
    var checkboxes = document.querySelectorAll('.product-cb');
    var listEl = document.getElementById('selected-products-list');
    var hiddenContainer = document.getElementById('hidden-product-ids');
    var submitBtn = document.getElementById('print-submit-btn');

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (cb) { cb.checked = selectAll.checked; });
            syncSelection();
        });
    }

    checkboxes.forEach(function (cb) { cb.addEventListener('change', syncSelection); });

    function syncSelection() {
        while (hiddenContainer.firstChild) hiddenContainer.removeChild(hiddenContainer.firstChild);
        while (listEl.firstChild) listEl.removeChild(listEl.firstChild);

        var selected = [];
        checkboxes.forEach(function (cb) {
            if (cb.checked) {
                selected.push({ id: cb.value, name: cb.dataset.name, sku: cb.dataset.sku });
                var input = document.createElement('input');
                input.type = 'hidden'; input.name = 'product_ids[]'; input.value = cb.value;
                hiddenContainer.appendChild(input);
            }
        });

        if (selected.length === 0) {
            var msg = document.createElement('p');
            msg.className = 'text-muted small mb-0';
            msg.textContent = @json(__('Cochez les produits dans le tableau.'));
            listEl.appendChild(msg);
            submitBtn.disabled = true;
        } else {
            selected.forEach(function (p) {
                var div = document.createElement('div');
                div.className = 'small mb-1';
                var icon = document.createElement('i');
                icon.className = 'ti ti-check text-success me-1';
                div.appendChild(icon);
                div.appendChild(document.createTextNode(p.name + ' '));
                var code = document.createElement('code');
                code.textContent = p.sku;
                div.appendChild(code);
                listEl.appendChild(div);
            });
            submitBtn.disabled = false;
        }
    }
});
</script>
@endpush

</x-dashboard::layouts.master>

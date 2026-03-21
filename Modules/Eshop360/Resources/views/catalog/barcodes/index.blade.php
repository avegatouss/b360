<x-dashboard::layouts.master
    :title="__('Codes-barres') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Codes-barres')">

@php
    $slug = $instance->slug ?? '';
    $totalProducts = $products->total();
    $withBarcode = $products->getCollection()->filter(fn($p) => !empty($p->barcode))->count();
    $withoutBarcode = $products->getCollection()->filter(fn($p) => empty($p->barcode))->count();
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<style>
    .label-preview { border: 1px dashed var(--bs-border-color); border-radius: .5rem; padding: 1rem; text-align: center; background: #fff; transition: all .2s; }
    .label-preview .barcode-svg { margin: .5rem 0; }
    @media print { .no-print { display: none !important; } }
</style>
@endpush

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Codes-barres') }}</h4>
            <h6>{{ __('Generer et imprimer des codes-barres produits') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.barcodes.qrcode', $slug) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-qrcode me-1"></i>{{ __('QR Codes') }}
        </a>
        @if($products->isNotEmpty())
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#print-modal" id="btn-open-print">
                <i class="ti ti-printer me-1"></i>{{ __('Imprimer') }}
            </button>
        @endif
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-3">
    <div class="col-xl-4 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-package fs-4 text-primary"></i></div>
                <div><div class="text-muted">{{ __('Produits') }}</div><div class="fs-4 fw-bold">{{ $totalProducts }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-barcode fs-4 text-success"></i></div>
                <div><div class="text-muted">{{ __('Avec code-barres') }}</div><div class="fs-4 fw-bold text-success">{{ $withBarcode }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-danger bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-barcode-off fs-4 text-danger"></i></div>
                <div><div class="text-muted">{{ __('Sans code-barres') }}</div><div class="fs-4 fw-bold text-danger">{{ $withoutBarcode }}</div></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.barcodes.index', $slug) }}" id="bc-filter-form" class="row g-2 align-items-center">
            <div class="col">
                <input type="text" name="search" id="bc-search-input" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Recherche...') }}" autocomplete="off">
            </div>
            <div class="col-auto" style="min-width: 140px;">
                <select name="category_id" class="form-select form-select-sm bc-select2" data-placeholder="{{ __('Categorie') }}">
                    <option value=""></option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 130px;">
                <select name="brand_id" class="form-select form-select-sm bc-select2" data-placeholder="{{ __('Marque') }}">
                    <option value=""></option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ (string) request('brand_id') === (string) $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 130px;">
                <select name="store_id" class="form-select form-select-sm bc-select2" data-placeholder="{{ __('Magasin') }}">
                    <option value=""></option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 130px;">
                <select name="warehouse_id" class="form-select form-select-sm bc-select2" data-placeholder="{{ __('Entrepot') }}">
                    <option value=""></option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string) request('warehouse_id') === (string) $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['search', 'category_id', 'brand_id', 'store_id', 'warehouse_id']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.barcodes.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
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

{{-- Products Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;"><input type="checkbox" class="form-check-input" id="select-all-bc"></th>
                        <th style="width:50px;"></th>
                        <th>{{ __('Produit') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Code-barres') }}</th>
                        <th>{{ __('Categorie') }}</th>
                        <th class="text-end">{{ __('Prix') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><input type="checkbox" class="form-check-input product-cb" value="{{ $product->id }}" data-name="{{ $product->name }}" data-sku="{{ $product->sku }}" data-barcode="{{ $product->barcode }}" data-price="{{ $product->price }}"></td>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="rounded" style="width:42px;height:42px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:42px;height:42px;"><i class="ti ti-package text-muted"></i></div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                @if($product->brand)<span class="badge bg-light text-dark border" style="font-size:.75rem;">{{ $product->brand->name }}</span>@endif
                            </td>
                            <td><code class="text-muted">{{ $product->sku }}</code></td>
                            <td>
                                @if($product->barcode)
                                    <span class="badge bg-light text-dark font-monospace border">{{ $product->barcode }}</span>
                                    <small class="text-muted d-block">{{ $product->barcode_type ?? 'code128' }}</small>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">{{ __('Non defini') }}</span>
                                @endif
                            </td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td class="text-end fw-bold">{{ number_format($product->price, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ti ti-barcode-off fs-1 d-block mb-2"></i>{{ __('Aucun produit trouve.') }}
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

{{-- Print Barcode Modal --}}
<div class="modal fade" id="print-modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="ti ti-printer me-2"></i>{{ __('Generer les etiquettes') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    {{-- Left: Options --}}
                    <div class="col-md-5">
                        <h6 class="fw-bold mb-3">{{ __('Configuration') }}</h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Produits selectionnes') }} (<span id="print-count">0</span>)</label>
                            <div id="selected-products-list" class="border rounded p-2" style="max-height:120px;overflow-y:auto;">
                                <p class="text-muted small mb-0">{{ __('Cochez les produits dans le tableau.') }}</p>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">{{ __('Format papier') }}</label>
                                <select class="form-select form-select-sm bc-modal-select2" id="opt-paper">
                                    <option value="a4" selected>A4 (210x297mm)</option>
                                    <option value="a5">A5 (148x210mm)</option>
                                    <option value="letter">Letter (216x279mm)</option>
                                    <option value="label_65">65 etiquettes/page</option>
                                    <option value="label_30">30 etiquettes/page</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label">{{ __('Par ligne') }}</label>
                                <select class="form-select form-select-sm bc-modal-select2" id="opt-per-row" name="per_row">
                                    <option value="2">2</option>
                                    <option value="3" selected>3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label">{{ __('Quantite') }}</label>
                                <input type="number" class="form-control form-control-sm" id="opt-quantity" value="1" min="1" max="100">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">{{ __('Format code') }}</label>
                                <select class="form-select form-select-sm bc-modal-select2" id="opt-format">
                                    <option value="CODE128" selected>Code 128</option>
                                    <option value="EAN13">EAN-13</option>
                                    <option value="CODE39">Code 39</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Taille') }}</label>
                                <select class="form-select form-select-sm bc-modal-select2" id="opt-size">
                                    <option value="small">{{ __('Petit') }}</option>
                                    <option value="medium" selected>{{ __('Moyen') }}</option>
                                    <option value="large">{{ __('Grand') }}</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-2">{{ __('Elements a afficher') }}</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><div class="form-check"><input class="form-check-input lbl-opt" type="checkbox" id="lbl-name" checked><label class="form-check-label" for="lbl-name">{{ __('Nom produit') }}</label></div></div>
                            <div class="col-6"><div class="form-check"><input class="form-check-input lbl-opt" type="checkbox" id="lbl-price" checked><label class="form-check-label" for="lbl-price">{{ __('Prix') }}</label></div></div>
                            <div class="col-6"><div class="form-check"><input class="form-check-input lbl-opt" type="checkbox" id="lbl-sku"><label class="form-check-label" for="lbl-sku">{{ __('SKU') }}</label></div></div>
                            <div class="col-6"><div class="form-check"><input class="form-check-input lbl-opt" type="checkbox" id="lbl-company"><label class="form-check-label" for="lbl-company">{{ __('Entreprise') }}</label></div></div>
                            <div class="col-6"><div class="form-check"><input class="form-check-input lbl-opt" type="checkbox" id="lbl-store"><label class="form-check-label" for="lbl-store">{{ __('Magasin') }}</label></div></div>
                            <div class="col-6"><div class="form-check"><input class="form-check-input lbl-opt" type="checkbox" id="lbl-currency" checked><label class="form-check-label" for="lbl-currency">{{ __('Devise') }}</label></div></div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">{{ __('Nom entreprise') }}</label>
                                <input type="text" class="form-control form-control-sm" id="opt-company-name" value="{{ $instance->name ?? 'B360' }}">
                            </div>
                            <div class="col-3">
                                <label class="form-label">{{ __('Devise') }}</label>
                                <input type="text" class="form-control form-control-sm" id="opt-currency" value="{{ $eshopCurrency ?? 'FCFA' }}">
                            </div>
                            <div class="col-3">
                                <label class="form-label">{{ __('Magasin') }}</label>
                                <input type="text" class="form-control form-control-sm" id="opt-store-name" value="">
                            </div>
                        </div>

                        <h6 class="fw-bold mb-2">{{ __('Alignement') }}</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label">{{ __('Horizontal') }}</label>
                                <select class="form-select form-select-sm bc-modal-select2" id="opt-align-h">
                                    <option value="center" selected>{{ __('Centre') }}</option>
                                    <option value="left">{{ __('Gauche') }}</option>
                                    <option value="right">{{ __('Droite') }}</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label">{{ __('Vertical') }}</label>
                                <select class="form-select form-select-sm bc-modal-select2" id="opt-align-v">
                                    <option value="center" selected>{{ __('Centre') }}</option>
                                    <option value="top">{{ __('Haut') }}</option>
                                    <option value="bottom">{{ __('Bas') }}</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label">{{ __('Marge (mm)') }}</label>
                                <input type="number" class="form-control form-control-sm" id="opt-margin" value="2" min="0" max="10">
                            </div>
                        </div>
                    </div>

                    {{-- Right: Live Preview --}}
                    <div class="col-md-7">
                        <h6 class="fw-bold mb-3">{{ __('Apercu') }}</h6>
                        <div class="border rounded p-3 bg-light" style="min-height: 300px;" id="label-preview-zone">
                            <div class="text-center text-muted py-5">{{ __('Selectionnez des produits pour voir l\'apercu') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <button type="button" class="btn btn-outline-info" id="btn-print-preview"><i class="ti ti-eye me-1"></i>{{ __('Apercu impression') }}</button>
                <button type="button" class="btn btn-primary" id="btn-print-labels" disabled><i class="ti ti-printer me-1"></i>{{ __('Imprimer') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Print Window (hidden, used for actual printing) --}}
<div id="print-output" style="display:none;"></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
jQuery(function ($) {
    // ── Select2 filters ──
    $('.bc-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' })
            .on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
    });

    // ── Select2 in modal ──
    $('.bc-modal-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', minimumResultsForSearch: Infinity, width: '100%', dropdownParent: $('#print-modal') });
    });

    // ── Search debounce ──
    var searchTimer = null;
    $('#bc-search-input').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { $('#bc-filter-form')[0].submit(); }, 500);
    }).on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); $('#bc-filter-form')[0].submit(); } });

    // ── Product selection ──
    var $selectAll = $('#select-all-bc');
    function getSelected() {
        var items = [];
        $('.product-cb:checked').each(function () {
            items.push({ id: $(this).val(), name: $(this).data('name'), sku: $(this).data('sku'), barcode: $(this).data('barcode'), price: $(this).data('price') });
        });
        return items;
    }

    function syncSelection() {
        var items = getSelected();
        var $list = $('#selected-products-list').empty();
        $('#print-count').text(items.length);
        if (items.length === 0) {
            $list.html('<p class="text-muted small mb-0">' + @json(__('Cochez les produits dans le tableau.')) + '</p>');
            $('#btn-print-labels').prop('disabled', true);
        } else {
            items.forEach(function (p) {
                $list.append('<div class="small mb-1"><i class="ti ti-check text-success me-1"></i>' + p.name + ' <code>' + p.sku + '</code></div>');
            });
            $('#btn-print-labels').prop('disabled', false);
        }
        updatePreview();
    }

    $selectAll.on('change', function () { $('.product-cb').prop('checked', this.checked); syncSelection(); });
    $(document).on('change', '.product-cb', syncSelection);
    $('.lbl-opt, #opt-per-row, #opt-format, #opt-size, #opt-align-h, #opt-align-v, #opt-margin, #opt-company-name, #opt-currency, #opt-store-name').on('change input', updatePreview);

    // ── Generate barcode SVG ──
    function renderBarcodeSVG(code, format) {
        if (!code) return '<div class="text-muted small">' + @json(__('Pas de code')) + '</div>';
        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        try {
            JsBarcode(svg, code, { format: format || 'CODE128', width: 1.5, height: 40, displayValue: true, fontSize: 11, margin: 2 });
            return svg.outerHTML;
        } catch (e) {
            return '<div class="text-danger small">Erreur: ' + e.message + '</div>';
        }
    }

    // ── Live preview ──
    function updatePreview() {
        var items = getSelected();
        var $zone = $('#label-preview-zone').empty();
        if (items.length === 0) { $zone.html('<div class="text-center text-muted py-5">' + @json(__('Selectionnez des produits')) + '</div>'); return; }

        var perRow = parseInt($('#opt-per-row').val()) || 3;
        var format = $('#opt-format').val() || 'CODE128';
        var alignH = $('#opt-align-h').val();
        var alignV = $('#opt-align-v').val();
        var margin = $('#opt-margin').val() + 'mm';
        var showName = $('#lbl-name').is(':checked'), showPrice = $('#lbl-price').is(':checked'), showSku = $('#lbl-sku').is(':checked');
        var showCompany = $('#lbl-company').is(':checked'), showStore = $('#lbl-store').is(':checked'), showCurrency = $('#lbl-currency').is(':checked');
        var companyName = $('#opt-company-name').val(), currency = $('#opt-currency').val(), storeName = $('#opt-store-name').val();
        var colClass = 'col-' + (12 / perRow);

        var justifyMap = { center: 'center', left: 'flex-start', right: 'flex-end' };
        var alignMap = { center: 'center', top: 'flex-start', bottom: 'flex-end' };

        var html = '<div class="row g-2">';
        var shown = items.slice(0, 6); // preview max 6
        shown.forEach(function (p) {
            html += '<div class="' + colClass + '">'
                + '<div class="label-preview" style="text-align:' + alignH + '; display:flex; flex-direction:column; justify-content:' + alignMap[alignV] + '; align-items:' + justifyMap[alignH] + '; padding:' + margin + '; min-height:120px;">';
            if (showCompany) html += '<div style="font-size:.65rem; font-weight:700; text-transform:uppercase;">' + companyName + '</div>';
            if (showStore && storeName) html += '<div style="font-size:.6rem; color:#666;">' + storeName + '</div>';
            if (showName) html += '<div style="font-size:.75rem; font-weight:600; margin-bottom:2px;">' + p.name + '</div>';
            html += '<div class="barcode-svg">' + renderBarcodeSVG(p.barcode || p.sku, format) + '</div>';
            if (showSku) html += '<div style="font-size:.6rem; color:#888;">SKU: ' + p.sku + '</div>';
            if (showPrice) html += '<div style="font-size:.85rem; font-weight:700;">' + Math.round(p.price).toLocaleString('fr-FR') + (showCurrency ? ' ' + currency : '') + '</div>';
            html += '</div></div>';
        });
        if (items.length > 6) html += '<div class="col-12 text-center text-muted small mt-2">+ ' + (items.length - 6) + ' ' + @json(__('autres produits')) + '</div>';
        html += '</div>';
        $zone.html(html);
    }

    // ── Print ──
    function buildPrintHTML() {
        var items = getSelected();
        var qty = parseInt($('#opt-quantity').val()) || 1;
        var perRow = parseInt($('#opt-per-row').val()) || 3;
        var format = $('#opt-format').val() || 'CODE128';
        var alignH = $('#opt-align-h').val(), alignV = $('#opt-align-v').val();
        var margin = $('#opt-margin').val() + 'mm';
        var showName = $('#lbl-name').is(':checked'), showPrice = $('#lbl-price').is(':checked'), showSku = $('#lbl-sku').is(':checked');
        var showCompany = $('#lbl-company').is(':checked'), showStore = $('#lbl-store').is(':checked'), showCurrency = $('#lbl-currency').is(':checked');
        var companyName = $('#opt-company-name').val(), currency = $('#opt-currency').val(), storeName = $('#opt-store-name').val();
        var paper = $('#opt-paper').val();
        var justifyMap = { center: 'center', left: 'flex-start', right: 'flex-end' };
        var alignMap = { center: 'center', top: 'flex-start', bottom: 'flex-end' };
        var colW = (100 / perRow) + '%';

        var html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' + @json(__('Etiquettes codes-barres')) + '</title>';
        html += '<style>@page{margin:5mm;size:' + paper + ';}*{margin:0;padding:0;box-sizing:border-box;}body{font-family:Arial,sans-serif;}';
        html += '.grid{display:flex;flex-wrap:wrap;}.cell{width:' + colW + ';padding:' + margin + ';display:flex;flex-direction:column;justify-content:' + alignMap[alignV] + ';align-items:' + justifyMap[alignH] + ';text-align:' + alignH + ';page-break-inside:avoid;}';
        html += '.cell svg{max-width:100%;height:auto;}</style></head><body><div class="grid">';

        items.forEach(function (p) {
            for (var i = 0; i < qty; i++) {
                html += '<div class="cell">';
                if (showCompany) html += '<div style="font-size:7pt;font-weight:700;text-transform:uppercase;">' + companyName + '</div>';
                if (showStore && storeName) html += '<div style="font-size:6pt;color:#666;">' + storeName + '</div>';
                if (showName) html += '<div style="font-size:8pt;font-weight:600;margin-bottom:1mm;">' + p.name + '</div>';
                html += '<div>' + renderBarcodeSVG(p.barcode || p.sku, format) + '</div>';
                if (showSku) html += '<div style="font-size:6pt;color:#888;">SKU: ' + p.sku + '</div>';
                if (showPrice) html += '<div style="font-size:9pt;font-weight:700;">' + Math.round(p.price).toLocaleString('fr-FR') + (showCurrency ? ' ' + currency : '') + '</div>';
                html += '</div>';
            }
        });
        html += '</div></body></html>';
        return html;
    }

    $('#btn-print-labels, #btn-print-preview').on('click', function () {
        var items = getSelected();
        if (items.length === 0) { alert(@json(__('Selectionnez au moins un produit.'))); return; }
        var printWindow = window.open('', '_blank', 'width=800,height=600');
        printWindow.document.write(buildPrintHTML());
        printWindow.document.close();
        if ($(this).attr('id') === 'btn-print-labels') {
            printWindow.onload = function () { printWindow.print(); };
        }
    });
});
</script>
@endpush

</x-dashboard::layouts.master>

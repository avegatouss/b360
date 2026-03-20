<x-dashboard::layouts.master
    :title="__('QR Codes') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('QR Codes')">

@php
    $slug = $instance->slug ?? '';
    $totalProducts = $products->total();
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<style>
    .qr-preview { border: 1px dashed var(--bs-border-color); border-radius: .5rem; padding: 1rem; text-align: center; background: #fff; }
</style>
@endpush

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('QR Codes') }}</h4>
            <h6>{{ __('Generer et imprimer des QR codes produits') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.barcodes.index', $slug) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-barcode me-1"></i>{{ __('Codes-barres') }}
        </a>
        @if($products->isNotEmpty())
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#qr-print-modal">
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
                <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-qrcode fs-4 text-success"></i></div>
                <div><div class="text-muted">{{ __('Avec QR') }}</div><div class="fs-4 fw-bold text-success">{{ $products->getCollection()->filter(fn($p) => !empty($p->qrcode) || !empty($p->barcode))->count() }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-scan fs-4 text-info"></i></div>
                <div><div class="text-muted">{{ __('Scannables') }}</div><div class="fs-4 fw-bold text-info">{{ $products->getCollection()->filter(fn($p) => !empty($p->sku))->count() }}</div></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.barcodes.qrcode', $slug) }}" id="qr-filter-form" class="row g-2 align-items-center">
            <div class="col">
                <input type="text" name="search" id="qr-search-input" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Recherche...') }}" autocomplete="off">
            </div>
            <div class="col-auto" style="min-width: 140px;">
                <select name="category_id" class="form-select form-select-sm qr-select2" data-placeholder="{{ __('Categorie') }}">
                    <option value=""></option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 130px;">
                <select name="brand_id" class="form-select form-select-sm qr-select2" data-placeholder="{{ __('Marque') }}">
                    <option value=""></option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ (string) request('brand_id') === (string) $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 130px;">
                <select name="store_id" class="form-select form-select-sm qr-select2" data-placeholder="{{ __('Magasin') }}">
                    <option value=""></option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 130px;">
                <select name="warehouse_id" class="form-select form-select-sm qr-select2" data-placeholder="{{ __('Entrepot') }}">
                    <option value=""></option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string) request('warehouse_id') === (string) $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['search', 'category_id', 'brand_id', 'store_id', 'warehouse_id']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.barcodes.qrcode', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
            <div class="col-auto ms-auto">
                <span class="text-muted">{{ $totalProducts }} {{ __('produit(s)') }}</span>
            </div>
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
                        <th style="width:35px;"><input type="checkbox" class="form-check-input" id="select-all-qr"></th>
                        <th style="width:50px;"></th>
                        <th>{{ __('Produit') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Categorie') }}</th>
                        <th class="text-end">{{ __('Prix') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><input type="checkbox" class="form-check-input qr-cb" value="{{ $product->id }}" data-name="{{ $product->name }}" data-sku="{{ $product->sku }}" data-code="{{ $product->qrcode ?? $product->barcode ?? $product->sku }}" data-price="{{ $product->price }}"></td>
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
                                @php $qrVal = $product->qrcode ?? $product->barcode; @endphp
                                @if($qrVal)
                                    <span class="badge bg-light text-dark font-monospace border">{{ $qrVal }}</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">{{ __('SKU utilise') }}</span>
                                @endif
                            </td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td class="text-end fw-bold">{{ number_format($product->price, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ti ti-qrcode-off fs-1 d-block mb-2"></i>{{ __('Aucun produit trouve.') }}
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

{{-- QR Print Modal --}}
<div class="modal fade" id="qr-print-modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="ti ti-qrcode me-2"></i>{{ __('Generer les QR codes') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <h6 class="fw-bold mb-3">{{ __('Configuration') }}</h6>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Produits') }} (<span id="qr-print-count">0</span>)</label>
                            <div id="qr-selected-list" class="border rounded p-2" style="max-height:120px;overflow-y:auto;">
                                <p class="text-muted small mb-0">{{ __('Cochez les produits.') }}</p>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">{{ __('Format papier') }}</label>
                                <select class="form-select form-select-sm qr-modal-select2" id="qr-opt-paper">
                                    <option value="a4" selected>A4</option>
                                    <option value="a5">A5</option>
                                    <option value="letter">Letter</option>
                                    <option value="label_65">65/page</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label">{{ __('Par ligne') }}</label>
                                <select class="form-select form-select-sm qr-modal-select2" id="qr-opt-per-row">
                                    <option value="3" selected>3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label">{{ __('Quantite') }}</label>
                                <input type="number" class="form-control form-control-sm" id="qr-opt-qty" value="1" min="1" max="100">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label">{{ __('Taille QR') }}</label>
                                <select class="form-select form-select-sm qr-modal-select2" id="qr-opt-size">
                                    <option value="80">{{ __('Petit') }} (80px)</option>
                                    <option value="120" selected>{{ __('Moyen') }} (120px)</option>
                                    <option value="160">{{ __('Grand') }} (160px)</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label">{{ __('Entreprise') }}</label>
                                <input type="text" class="form-control form-control-sm" id="qr-opt-company" value="{{ $instance->name ?? 'B360' }}">
                            </div>
                            <div class="col-4">
                                <label class="form-label">{{ __('Devise') }}</label>
                                <input type="text" class="form-control form-control-sm" id="qr-opt-currency" value="FCFA">
                            </div>
                        </div>
                        <h6 class="fw-bold mb-2">{{ __('Afficher') }}</h6>
                        <div class="row g-2">
                            <div class="col-6"><div class="form-check"><input class="form-check-input qr-lbl" type="checkbox" id="qr-lbl-name" checked><label class="form-check-label" for="qr-lbl-name">{{ __('Nom') }}</label></div></div>
                            <div class="col-6"><div class="form-check"><input class="form-check-input qr-lbl" type="checkbox" id="qr-lbl-price" checked><label class="form-check-label" for="qr-lbl-price">{{ __('Prix') }}</label></div></div>
                            <div class="col-6"><div class="form-check"><input class="form-check-input qr-lbl" type="checkbox" id="qr-lbl-sku"><label class="form-check-label" for="qr-lbl-sku">{{ __('SKU') }}</label></div></div>
                            <div class="col-6"><div class="form-check"><input class="form-check-input qr-lbl" type="checkbox" id="qr-lbl-company"><label class="form-check-label" for="qr-lbl-company">{{ __('Entreprise') }}</label></div></div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h6 class="fw-bold mb-3">{{ __('Apercu') }}</h6>
                        <div class="border rounded p-3 bg-light" style="min-height:300px;" id="qr-preview-zone">
                            <div class="text-center text-muted py-5">{{ __('Selectionnez des produits') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <button type="button" class="btn btn-outline-info" id="btn-qr-preview"><i class="ti ti-eye me-1"></i>{{ __('Apercu') }}</button>
                <button type="button" class="btn btn-primary" id="btn-qr-print" disabled><i class="ti ti-printer me-1"></i>{{ __('Imprimer') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
jQuery(function ($) {
    // ── Select2 ──
    $('.qr-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' })
            .on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
    });

    var searchTimer = null;
    $('#qr-search-input').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { $('#qr-filter-form')[0].submit(); }, 500);
    }).on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); $('#qr-filter-form')[0].submit(); } });

    // ── Select2 in modal ──
    $('.qr-modal-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', minimumResultsForSearch: Infinity, width: '100%', dropdownParent: $('#qr-print-modal') });
    });

    // ── Selection ──
    var $selectAll = $('#select-all-qr');
    function getSelected() {
        var items = [];
        $('.qr-cb:checked').each(function () {
            items.push({ id: $(this).val(), name: $(this).data('name'), sku: $(this).data('sku'), code: $(this).data('code'), price: $(this).data('price') });
        });
        return items;
    }

    function syncQrSelection() {
        var items = getSelected();
        var $list = $('#qr-selected-list').empty();
        $('#qr-print-count').text(items.length);
        if (items.length === 0) {
            $list.html('<p class="text-muted small mb-0">' + @json(__('Cochez les produits.')) + '</p>');
            $('#btn-qr-print').prop('disabled', true);
        } else {
            items.forEach(function (p) { $list.append('<div class="small mb-1"><i class="ti ti-check text-success me-1"></i>' + p.name + '</div>'); });
            $('#btn-qr-print').prop('disabled', false);
        }
        updateQrPreview();
    }

    $selectAll.on('change', function () { $('.qr-cb').prop('checked', this.checked); syncQrSelection(); });
    $(document).on('change', '.qr-cb', syncQrSelection);
    $('.qr-lbl, #qr-opt-per-row, #qr-opt-size, #qr-opt-company, #qr-opt-currency').on('change input', updateQrPreview);

    // QR cache to avoid re-rendering
    var qrCache = {};
    function makeQR(text, size, targetEl) {
        var key = text + '_' + size;
        if (targetEl) {
            targetEl.innerHTML = '';
            new QRCode(targetEl, { text: text || 'N/A', width: size, height: size, colorDark: '#000', colorLight: '#fff', correctLevel: QRCode.CorrectLevel.M });
        }
    }

    function updateQrPreview() {
        var items = getSelected();
        var $zone = $('#qr-preview-zone').empty();
        if (items.length === 0) { $zone.html('<div class="text-center text-muted py-5">' + @json(__('Selectionnez des produits')) + '</div>'); return; }

        var perRow = parseInt($('#qr-opt-per-row').val()) || 3;
        var size = parseInt($('#qr-opt-size').val()) || 120;
        var showName = $('#qr-lbl-name').is(':checked'), showPrice = $('#qr-lbl-price').is(':checked');
        var showSku = $('#qr-lbl-sku').is(':checked'), showCompany = $('#qr-lbl-company').is(':checked');
        var company = $('#qr-opt-company').val(), currency = $('#qr-opt-currency').val();
        var colClass = 'col-' + (12 / perRow);

        var $row = $('<div class="row g-2"></div>');
        items.slice(0, 6).forEach(function (p) {
            var $cell = $('<div class="' + colClass + '"><div class="qr-preview"></div></div>');
            var $inner = $cell.find('.qr-preview');
            if (showCompany) $inner.append('<div style="font-size:.65rem;font-weight:700;text-transform:uppercase;">' + company + '</div>');
            if (showName) $inner.append('<div style="font-size:.75rem;font-weight:600;">' + p.name + '</div>');
            var $qrDiv = $('<div style="display:inline-block;margin:.25rem 0;"></div>');
            $inner.append($qrDiv);
            if (showSku) $inner.append('<div style="font-size:.6rem;color:#888;">SKU: ' + p.sku + '</div>');
            if (showPrice) $inner.append('<div style="font-size:.85rem;font-weight:700;">' + Math.round(p.price).toLocaleString('fr-FR') + ' ' + currency + '</div>');
            $row.append($cell);
            // Render QR after appending to DOM
            setTimeout(function () { makeQR(p.code || p.sku, size, $qrDiv[0]); }, 10);
        });
        if (items.length > 6) $row.append('<div class="col-12 text-center text-muted small mt-2">+ ' + (items.length - 6) + ' ' + @json(__('autres')) + '</div>');
        $zone.append($row);
    }

    function openQrPrintWindow(autoPrint) {
        var items = getSelected();
        if (items.length === 0) { alert(@json(__('Selectionnez au moins un produit.'))); return; }

        var qty = parseInt($('#qr-opt-qty').val()) || 1;
        var perRow = parseInt($('#qr-opt-per-row').val()) || 3;
        var size = parseInt($('#qr-opt-size').val()) || 120;
        var paper = $('#qr-opt-paper').val();
        var showName = $('#qr-lbl-name').is(':checked'), showPrice = $('#qr-lbl-price').is(':checked');
        var showSku = $('#qr-lbl-sku').is(':checked'), showCompany = $('#qr-lbl-company').is(':checked');
        var company = $('#qr-opt-company').val(), currency = $('#qr-opt-currency').val();
        var colW = (100 / perRow) + '%';

        var w = window.open('', '_blank', 'width=800,height=600');
        var doc = w.document;
        doc.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>QR Codes</title>');
        doc.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>');
        doc.write('<style>@page{margin:5mm;size:' + paper + ';}*{margin:0;padding:0;box-sizing:border-box;}body{font-family:Arial,sans-serif;}');
        doc.write('.grid{display:flex;flex-wrap:wrap;}.cell{width:' + colW + ';padding:2mm;text-align:center;page-break-inside:avoid;}</style>');
        doc.write('</head><body><div class="grid">');

        var cellId = 0;
        items.forEach(function (p) {
            for (var i = 0; i < qty; i++) {
                var id = 'qr-' + (cellId++);
                doc.write('<div class="cell">');
                if (showCompany) doc.write('<div style="font-size:7pt;font-weight:700;text-transform:uppercase;">' + company + '</div>');
                if (showName) doc.write('<div style="font-size:8pt;font-weight:600;margin-bottom:1mm;">' + p.name + '</div>');
                doc.write('<div id="' + id + '" data-text="' + (p.code || p.sku) + '"></div>');
                if (showSku) doc.write('<div style="font-size:6pt;color:#888;">SKU: ' + p.sku + '</div>');
                if (showPrice) doc.write('<div style="font-size:9pt;font-weight:700;">' + Math.round(p.price).toLocaleString('fr-FR') + ' ' + currency + '</div>');
                doc.write('</div>');
            }
        });

        doc.write('</div><script>');
        doc.write('document.addEventListener("DOMContentLoaded",function(){');
        doc.write('var els=document.querySelectorAll("[data-text]");');
        doc.write('for(var i=0;i<els.length;i++){new QRCode(els[i],{text:els[i].dataset.text,width:' + size + ',height:' + size + ',colorDark:"#000",colorLight:"#fff",correctLevel:QRCode.CorrectLevel.M});}');
        if (autoPrint) doc.write('setTimeout(function(){window.print();},500);');
        doc.write('});<\/script></body></html>');
        doc.close();
    }

    $('#btn-qr-print').on('click', function () { openQrPrintWindow(true); });
    $('#btn-qr-preview').on('click', function () { openQrPrintWindow(false); });
});
</script>
@endpush

</x-dashboard::layouts.master>

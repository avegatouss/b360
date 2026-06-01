@php
    $slug = $instance->slug ?? '';
    $productTaxIds = $product->productTaxes?->pluck('tax_id')->toArray() ?? [];
@endphp

<x-dashboard::layouts.master
    :title="__('Modifier le produit') . ' — ' . $product->name"
    :instance="$instance"
    :pageTitle="__('Modifier le produit')">

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('build/plugins/summernote/summernote-lite.min.css') }}">
<style>
    .img-preview-zone { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .5rem; }
    .img-preview-zone img { width: 80px; height: 80px; object-fit: cover; border-radius: .375rem; border: 2px solid var(--bs-border-color); }
    .input-group .select2-container { flex: 1 1 auto; width: auto !important; min-width: 0; }
    .input-group .select2-container .select2-selection { border-top-right-radius: 0; border-bottom-right-radius: 0; }
</style>
@endpush

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Modifier le produit') }}</h4>
            <h6>{{ $product->name }} <span class="text-muted">({{ $product->sku }})</span></h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.products.show', [$slug, $product]) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-eye me-1"></i>{{ __('Voir') }}</a>
        <a href="{{ route('eshop360.products.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form action="{{ route('eshop360.products.update', [$slug, $product]) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="row g-3">
        {{-- ═══════ COLONNE GAUCHE ═══════ --}}
        <div class="col-xl-8">

            {{-- Informations generales --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-2"></i>{{ __('Informations generales') }}</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Nom du produit') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Type de vente') }}</label>
                            <select name="selling_type" class="form-select edit-select2">
                                <option value="both" {{ old('selling_type', $product->selling_type ?? 'both') == 'both' ? 'selected' : '' }}>{{ __('POS + En ligne') }}</option>
                                <option value="pos" {{ old('selling_type', $product->selling_type) == 'pos' ? 'selected' : '' }}>{{ __('POS uniquement') }}</option>
                                <option value="online" {{ old('selling_type', $product->selling_type) == 'online' ? 'selected' : '' }}>{{ __('En ligne uniquement') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Categorie') }}</label>
                            <select name="category_id" class="form-select edit-select2" data-placeholder="{{ __('Selectionner') }}">
                                <option value=""></option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Marque') }}</label>
                            <select name="brand_id" class="form-select edit-select2" data-placeholder="{{ __('Selectionner') }}">
                                <option value=""></option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Unite') }}</label>
                            <select name="unit" class="form-select edit-select2">
                                @foreach(['pcs' => 'Piece', 'bx' => 'Boite', 'kg' => 'Kilogramme', 'l' => 'Litre', 'fl' => 'Flacon', 'tb' => 'Tube', 'sac' => 'Sachet', 'cp' => 'Comprime', 'amp' => 'Ampoule', 'dz' => 'Douzaine', 'plq' => 'Plaquette'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('unit', $product->unit) == $val ? 'selected' : '' }}>{{ __($label) }} ({{ $val }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" id="edit-description" class="form-control">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Prix & Taxes --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-currency-dollar me-2"></i>{{ __('Prix et taxes') }}</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Prix de vente') }} <span class="text-danger">*</span></label>
                            <input type="number" name="price" step="0.01" class="form-control" value="{{ old('price', $product->price) }}" required>
                        </div>
                        @if($canSeePricing ?? false)
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Cout de revient') }}</label>
                            <input type="number" name="cost_price" step="0.01" class="form-control" value="{{ old('cost_price', $product->cost_price) }}">
                        </div>
                        @endif
                        <div class="col-md-4">
                            <label class="form-label d-block">{{ __('Taxe incluse') }}</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="tax_inclusive" value="0">
                                <input class="form-check-input" type="checkbox" name="tax_inclusive" value="1" {{ old('tax_inclusive', $product->tax_inclusive) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ __('TTC') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Taxes applicables') }}</label>
                            <select name="taxes[]" class="form-select edit-select2-multi" multiple data-placeholder="{{ __('Selectionner les taxes') }}">
                                @foreach($taxes ?? [] as $tax)
                                    <option value="{{ $tax->id }}" {{ in_array($tax->id, old('taxes', $productTaxIds)) ? 'selected' : '' }}>{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Taux manuel (%)') }}</label>
                            <input type="number" name="tax_rate" step="0.01" min="0" max="100" class="form-control" value="{{ old('tax_rate', $product->tax_rate) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Remise') }}</label>
                            <select name="discount_type" class="form-select edit-select2" id="edit-discount-type">
                                <option value="none" {{ old('discount_type', $product->discount_type) == 'none' ? 'selected' : '' }}>{{ __('Aucune') }}</option>
                                <option value="percentage" {{ old('discount_type', $product->discount_type) == 'percentage' ? 'selected' : '' }}>{{ __('Pourcentage (%)') }}</option>
                                <option value="fixed" {{ old('discount_type', $product->discount_type) == 'fixed' ? 'selected' : '' }}>{{ __('Montant fixe') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="edit-discount-wrap" style="{{ old('discount_type', $product->discount_type ?? 'none') === 'none' ? 'display:none' : '' }}">
                            <label class="form-label">{{ __('Valeur remise') }}</label>
                            <input type="number" name="discount_value" step="0.01" min="0" class="form-control" value="{{ old('discount_value', $product->discount_value) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Prix grossiste') }}</label>
                            <input type="number" name="wholesale_price" step="0.01" class="form-control" value="{{ old('wholesale_price', $product->wholesale_price) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tarification SAPHIR --}}
            @can('products.factory_price')
            <div class="card border-0 shadow-sm mb-3 border-warning">
                <div class="card-header bg-warning bg-opacity-10" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#saphirSection">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-building-factory me-2 text-warning"></i>{{ __('Tarification SAPHIR & Pharma') }} <span class="badge bg-warning text-dark ms-2">{{ __('Restreint') }}</span> <i class="ti ti-chevron-down ms-2 small"></i></h6>
                </div>
                <div class="collapse" id="saphirSection">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3"><label class="form-label">{{ __('PA Usine') }}</label><input type="number" step="0.0001" name="purchase_price_factory" class="form-control" value="{{ old('purchase_price_factory', $product->purchase_price_factory) }}"></div>
                            <div class="col-md-3"><label class="form-label">{{ __('PGHT') }}</label><input type="number" step="0.0001" name="pght" class="form-control" value="{{ old('pght', $product->pght) }}"></div>
                            <div class="col-md-3"><label class="form-label">{{ __('Prix pharmacie') }}</label><input type="number" step="0.01" name="pharmacy_price" class="form-control" value="{{ old('pharmacy_price', $product->pharmacy_price) }}"></div>
                            <div class="col-md-3"><label class="form-label">{{ __('Qte min. grossiste') }}</label><input type="number" min="1" name="min_qty_wholesale" class="form-control" value="{{ old('min_qty_wholesale', $product->min_qty_wholesale ?? 1) }}"></div>
                            <div class="col-md-3"><label class="form-label">{{ __('Prix revient reel') }}</label><input type="number" step="0.0001" name="cost_price_real" class="form-control" value="{{ old('cost_price_real', $product->cost_price_real) }}"></div>
                            <div class="col-md-3"><label class="form-label">{{ __('DCI') }}</label><input type="text" name="dci" class="form-control" value="{{ old('dci', $product->dci) }}"></div>
                            <div class="col-md-2"><label class="form-label">{{ __('Dosage') }}</label><input type="text" name="dosage" class="form-control" value="{{ old('dosage', $product->dosage) }}"></div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Forme') }}</label>
                                <select name="form" class="form-select edit-select2">
                                    <option value="">—</option>
                                    @foreach(['Comprime','Gelule','Sirop','Injectable','Suppositoire','Pommade','Creme','Gouttes','Spray','Patch','Sachet','Autre'] as $f)
                                        <option value="{{ $f }}" {{ old('form', $product->form) == $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2"><label class="form-label">{{ __('Conditionnement') }}</label><input type="text" name="packaging" class="form-control" value="{{ old('packaging', $product->packaging) }}"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

        </div>

        {{-- ═══════ COLONNE DROITE ═══════ --}}
        <div class="col-xl-4">

            {{-- Image --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-photo me-2"></i>{{ __('Images') }}</h6></div>
                <div class="card-body">
                    <div class="text-center mb-2">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded shadow-sm" style="max-height:160px;" id="edit-img-current">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:120px;" id="edit-img-current"><i class="ti ti-package fs-1 text-muted"></i></div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Image principale') }}</label>
                        <input type="file" name="image" class="form-control form-control-sm" accept="image/*" id="edit-image-input">
                        <div class="img-preview-zone" id="editMainPreview"></div>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Galerie') }}</label>
                        <input type="file" name="images[]" class="form-control form-control-sm" accept="image/*" multiple id="edit-gallery-input">
                        <div class="img-preview-zone" id="editGalleryPreview"></div>
                    </div>
                    @if($product->images && count($product->images) > 0)
                        <div class="mt-2">
                            <small class="text-muted">{{ __('Galerie actuelle') }}:</small>
                            <div class="d-flex gap-1 mt-1 flex-wrap">
                                @foreach($product->images as $img)
                                    <img src="{{ asset('storage/' . $img) }}" class="rounded" style="width:50px;height:50px;object-fit:cover;">
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Identification --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-barcode me-2"></i>{{ __('Identification') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('SKU') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="sku" id="sku-input" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" required>
                            <button type="button" class="btn btn-outline-secondary" id="generate-sku-btn" title="{{ __('Generer') }}"><i class="ti ti-refresh"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Type code-barres') }}</label>
                        <select name="barcode_type" class="form-select edit-select2">
                            <option value="ean13" {{ old('barcode_type', $product->barcode_type) == 'ean13' ? 'selected' : '' }}>EAN-13</option>
                            <option value="code128" {{ old('barcode_type', $product->barcode_type) == 'code128' ? 'selected' : '' }}>Code 128</option>
                            <option value="code39" {{ old('barcode_type', $product->barcode_type) == 'code39' ? 'selected' : '' }}>Code 39</option>
                            <option value="upc" {{ old('barcode_type', $product->barcode_type) == 'upc' ? 'selected' : '' }}>UPC</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Code-barres') }}</label>
                        <div class="input-group">
                            <input type="text" name="barcode" id="barcode-input" class="form-control" value="{{ old('barcode', $product->barcode) }}">
                            <button type="button" class="btn btn-outline-secondary" id="generate-barcode-btn" title="{{ __('Generer') }}"><i class="ti ti-refresh"></i></button>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">{{ __('Seuil alerte') }}</label>
                            <input type="number" name="alert_quantity" min="0" class="form-control" value="{{ old('alert_quantity', $product->alert_quantity) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Qte min.') }}</label>
                            <input type="number" name="min_quantity" min="0" class="form-control" value="{{ old('min_quantity', $product->min_quantity) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statut & Dates --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-settings me-2"></i>{{ __('Statut & Dates') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} id="is_active">
                            <label class="form-check-label" for="is_active">{{ __('Produit actif') }}</label>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">{{ __('Fabrication') }}</label>
                            <input type="date" name="manufactured_date" class="form-control form-control-sm" value="{{ old('manufactured_date', $product->manufactured_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Expiration') }}</label>
                            <input type="date" name="expiry_date" class="form-control form-control-sm" value="{{ old('expiry_date', $product->expiry_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('N. de lot') }}</label>
                            <input type="text" name="batch_number" class="form-control form-control-sm" value="{{ old('batch_number', $product->batch_number) }}">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="sticky-bottom bg-white border-top p-3 text-end" style="position: sticky; bottom: 0; z-index: 100;">
        <a href="{{ route('eshop360.products.index', $slug) }}" class="btn btn-secondary me-2">{{ __('Annuler') }}</a>
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('Enregistrer les modifications') }}</button>
    </div>
</form>

@push('scripts')
<script src="{{ asset('build/plugins/summernote/summernote-lite.min.js') }}"></script>
<script>
jQuery(function ($) {
    // ── Select2 ──
    $('.edit-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: 'resolve', placeholder: $(this).data('placeholder') || '' });
    });
    $('.edit-select2-multi').each(function () {
        $(this).select2({ theme: 'bootstrap-5', width: '100%', placeholder: $(this).data('placeholder') || '', closeOnSelect: false });
    });

    // ── Summernote ──
    $('#edit-description').summernote({
        height: 180,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });

    // ── Discount toggle ──
    $('#edit-discount-type').on('change', function () {
        $('#edit-discount-wrap').toggle($(this).val() !== 'none');
    });

    // ── Image preview ──
    function setupPreview(inputId, previewId) {
        $('#' + inputId).on('change', function () {
            var zone = $('#' + previewId).empty();
            Array.from(this.files).forEach(function (file) {
                if (!file.type.startsWith('image/')) return;
                var reader = new FileReader();
                reader.onload = function (e) { zone.append('<img src="' + e.target.result + '">'); };
                reader.readAsDataURL(file);
            });
        });
    }
    setupPreview('edit-image-input', 'editMainPreview');
    setupPreview('edit-gallery-input', 'editGalleryPreview');

    // ── Generate SKU ──
    $('#generate-sku-btn').on('click', function () {
        var name = $('[name="name"]').val() || 'PROD';
        var normalized = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase().replace(/[^A-Z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '').substring(0, 10);
        var digits = ''; for (var i = 0; i < 4; i++) digits += Math.floor(Math.random() * 10);
        $('#sku-input').val(normalized + '-' + digits);
    });

    // ── Generate Barcode ──
    $('#generate-barcode-btn').on('click', function () {
        var type = $('[name="barcode_type"]').val() || 'ean13';
        var code = '';
        if (type === 'ean13') {
            var d = ''; for (var i = 0; i < 12; i++) d += Math.floor(Math.random() * 10);
            var sum = 0; for (var i = 0; i < 12; i++) sum += parseInt(d[i]) * (i % 2 === 0 ? 1 : 3);
            code = d + ((10 - (sum % 10)) % 10);
        } else {
            var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            var len = type === 'code128' ? 10 : 8;
            for (var i = 0; i < len; i++) code += chars[Math.floor(Math.random() * chars.length)];
        }
        $('#barcode-input').val(code);
    });
});
</script>
@endpush

</x-dashboard::layouts.master>

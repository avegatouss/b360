@php
    $instanceSlug = $instance->slug ?? '';
    $categoriesJson = $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'parent_id' => $c->parent_id])->values()->toArray();
    $categoryUrl = route('eshop360.categories.store', $instanceSlug);
    $brandUrl = route('eshop360.brands.store', $instanceSlug);
    $warehouseUrl = route('eshop360.warehouses.store', $instanceSlug);
    $storeUrl = route('eshop360.stores.store', $instanceSlug);
@endphp

<x-dashboard::layouts.master
    :title="__('Ajouter un produit') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Ajouter un produit')">

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('build/plugins/summernote/summernote-lite.min.css') }}">
<style>
    .img-preview-zone { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .5rem; }
    .img-preview-zone img { width: 80px; height: 80px; object-fit: cover; border-radius: .375rem; border: 2px solid var(--bs-border-color); }
    .note-editor { border-radius: .375rem; }
    .note-editor .note-toolbar { background: var(--bs-body-bg); border-bottom: 1px solid var(--bs-border-color); }
    .input-group .select2-container { flex: 1 1 auto; width: auto !important; min-width: 0; }
    .input-group .select2-container .select2-selection { border-top-right-radius: 0; border-bottom-right-radius: 0; }
</style>
@endpush

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Nouveau produit') }}</h4>
            <h6>{{ __('Remplissez les informations du produit') }}</h6>
        </div>
    </div>
    <div class="page-btn mt-0">
        <a href="{{ route('eshop360.products.index', $instanceSlug) }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Retour aux produits') }}
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('eshop360.products.store', $instanceSlug) }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf

    <div class="row">
        {{-- ═══════ COLONNE GAUCHE ═══════ --}}
        <div class="col-xl-8">

            {{-- Section 1 : Informations de base --}}
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-info-circle me-2"></i>{{ __('Informations de base') }}</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label">{{ __('Nom du produit') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="selling_type" class="form-label">{{ __('Type de vente') }}</label>
                            <select class="form-select product-select2" id="selling_type" name="selling_type">
                                <option value="both" {{ old('selling_type', 'both') == 'both' ? 'selected' : '' }}>{{ __('POS + En ligne') }}</option>
                                <option value="pos" {{ old('selling_type') == 'pos' ? 'selected' : '' }}>{{ __('POS uniquement') }}</option>
                                <option value="online" {{ old('selling_type') == 'online' ? 'selected' : '' }}>{{ __('En ligne uniquement') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="category_id" class="form-label">{{ __('Categorie') }}</label>
                            <div class="input-group">
                                <select class="form-select product-select2 @error('category_id') is-invalid @enderror" id="category_id" name="category_id" data-placeholder="{{ __('Selectionner') }}">
                                    <option value=""></option>
                                    @foreach($categories->whereNull('parent_id') as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-quick-create" data-type="category" title="{{ __('Creer') }}"><i class="ti ti-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="sub_category_id" class="form-label">{{ __('Sous-categorie') }}</label>
                            <div class="input-group">
                                <select class="form-select product-select2" id="sub_category_id" name="sub_category_id" data-placeholder="{{ __('Selectionner') }}">
                                    <option value=""></option>
                                    @foreach($categories->whereNotNull('parent_id') as $sub)
                                        <option value="{{ $sub->id }}" data-parent="{{ $sub->parent_id }}" {{ old('sub_category_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-quick-create" data-type="subcategory" title="{{ __('Creer') }}"><i class="ti ti-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="brand_id" class="form-label">{{ __('Marque') }}</label>
                            <div class="input-group">
                                <select class="form-select product-select2 @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id" data-placeholder="{{ __('Selectionner') }}">
                                    <option value=""></option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-quick-create" data-type="brand" title="{{ __('Creer') }}"><i class="ti ti-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="unit" class="form-label">{{ __('Unite') }}</label>
                            <div class="input-group">
                                <select class="form-select product-select2 @error('unit') is-invalid @enderror" id="unit" name="unit" data-placeholder="{{ __('Selectionner') }}">
                                    <option value=""></option>
                                    @foreach(['bx' => 'Boite', 'pcs' => 'Piece', 'kg' => 'Kilogramme', 'l' => 'Litre', 'fl' => 'Flacon', 'tb' => 'Tube', 'sac' => 'Sachet', 'cp' => 'Comprime', 'amp' => 'Ampoule', 'dz' => 'Douzaine', 'plq' => 'Plaquette'] as $code => $label)
                                        <option value="{{ $code }}" {{ old('unit') == $code ? 'selected' : '' }}>{{ __($label) }} ({{ $code }})</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-quick-create" data-type="unit" title="{{ __('Creer') }}"><i class="ti ti-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="slug" class="form-label">{{ __('Slug') }}</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}" placeholder="{{ __('Auto-genere') }}">
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2 : Prix & Taxes --}}
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-currency-dollar me-2"></i>{{ __('Prix et taxes') }}</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="price" class="form-label">{{ __('Prix de vente') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="cost_price" class="form-label">{{ __('Cout de revient') }}</label>
                            <input type="number" step="0.01" min="0" class="form-control @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block">{{ __('Taxe incluse dans le prix') }}</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="tax_inclusive" value="0">
                                <input class="form-check-input" type="checkbox" id="tax_inclusive" name="tax_inclusive" value="1" {{ old('tax_inclusive') ? 'checked' : '' }}>
                                <label class="form-check-label" for="tax_inclusive">{{ __('TTC') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="taxes" class="form-label">{{ __('Taxes applicables') }}</label>
                            <select class="form-select product-select2-multi" id="taxes" name="taxes[]" multiple data-placeholder="{{ __('Selectionner les taxes') }}">
                                @foreach($taxes ?? [] as $tax)
                                    <option value="{{ $tax->id }}" {{ in_array($tax->id, old('taxes', [])) ? 'selected' : '' }}>{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('Si aucune taxe selectionnee, le taux global s\'applique.') }}</div>
                        </div>
                        <div class="col-md-3">
                            <label for="tax_rate" class="form-label">{{ __('Taux manuel (%)') }}</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="tax_rate" name="tax_rate" value="{{ old('tax_rate') }}" placeholder="{{ __('Si pas de taxe ci-dessus') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="discount_type" class="form-label">{{ __('Remise') }}</label>
                            <select class="form-select product-select2" id="discount_type" name="discount_type">
                                <option value="none" {{ old('discount_type', 'none') == 'none' ? 'selected' : '' }}>{{ __('Aucune') }}</option>
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>{{ __('Pourcentage (%)') }}</option>
                                <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>{{ __('Montant fixe') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="discount_value_wrap" style="{{ old('discount_type', 'none') === 'none' ? 'display:none' : '' }}">
                            <label for="discount_value" class="form-label">{{ __('Valeur remise') }}</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="discount_value" name="discount_value" value="{{ old('discount_value') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3 : Stock par magasin --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="ti ti-package me-2"></i>{{ __('Stock') }}</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddStockRow"><i class="ti ti-plus me-1"></i>{{ __('Ajouter un magasin') }}</button>
                </div>
                <div class="card-body">
                    <div id="stock-rows">
                        {{-- Ligne 1 (par defaut) --}}
                        <div class="stock-row row g-2 align-items-end mb-2">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Magasin') }}</label>
                                <select class="form-select form-select-sm stock-store-select" name="stocks[0][store_id]" data-placeholder="{{ __('Selectionner') }}">
                                    <option value=""></option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Entrepot') }}</label>
                                <select class="form-select form-select-sm stock-warehouse-select" name="stocks[0][warehouse_id]" data-placeholder="{{ __('Selectionner') }}">
                                    <option value=""></option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Quantite') }}</label>
                                <input type="number" min="0" class="form-control form-control-sm" name="stocks[0][quantity]" value="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Seuil alerte') }}</label>
                                <input type="number" min="0" class="form-control form-control-sm" name="stocks[0][alert_quantity]" value="">
                            </div>
                            <div class="col-md-2 d-flex gap-1">
                                <div class="flex-grow-1">
                                    <label class="form-label">{{ __('Qte min.') }}</label>
                                    <input type="number" min="0" class="form-control form-control-sm" name="stocks[0][min_quantity]" value="0">
                                </div>
                                <div class="d-flex align-items-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-stock-row d-none" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 4 : Options avancees --}}
            <div class="card mb-3">
                <div class="card-header" role="button" data-bs-toggle="collapse" data-bs-target="#advancedOptions" aria-expanded="false">
                    <h5 class="card-title mb-0"><i class="ti ti-settings me-2"></i>{{ __('Options avancees') }} <i class="ti ti-chevron-down float-end"></i></h5>
                </div>
                <div class="collapse" id="advancedOptions">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="warranty" class="form-label">{{ __('Garantie') }}</label>
                                <input type="text" class="form-control" id="warranty" name="warranty" value="{{ old('warranty') }}" placeholder="{{ __('Ex: 12 mois') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="manufacturer" class="form-label">{{ __('Fabricant') }}</label>
                                <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="{{ old('manufacturer') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="batch_number" class="form-label">{{ __('Numero de lot') }}</label>
                                <input type="text" class="form-control" id="batch_number" name="batch_number" value="{{ old('batch_number') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="manufactured_date" class="form-label">{{ __('Date de fabrication') }}</label>
                                <input type="date" class="form-control" id="manufactured_date" name="manufactured_date" value="{{ old('manufactured_date') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="expiry_date" class="form-label">{{ __('Date d\'expiration') }}</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">{{ __('Statut') }}</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ __('Produit actif') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 5 : Tarification SAPHIR --}}
            @can('products.factory_price')
            <div class="card mb-3 border-warning">
                <div class="card-header bg-warning bg-opacity-10" role="button" data-bs-toggle="collapse" data-bs-target="#saphirPricing" aria-expanded="false">
                    <h5 class="card-title mb-0"><i class="ti ti-building-factory me-2 text-warning"></i>{{ __('Tarification SAPHIR') }} <span class="badge bg-warning text-dark ms-2">{{ __('Restreint') }}</span> <i class="ti ti-chevron-down float-end"></i></h5>
                </div>
                <div class="collapse" id="saphirPricing">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="purchase_price_factory" class="form-label">{{ __('PA Usine') }}</label>
                                <input type="number" step="0.0001" min="0" class="form-control" id="purchase_price_factory" name="purchase_price_factory" value="{{ old('purchase_price_factory') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="purchase_price_provisional" class="form-label">{{ __('PA Provisionnel') }}</label>
                                <input type="number" step="0.0001" min="0" class="form-control" id="purchase_price_provisional" name="purchase_price_provisional" value="{{ old('purchase_price_provisional') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="pght" class="form-label">{{ __('PGHT') }}</label>
                                <input type="number" step="0.0001" min="0" class="form-control" id="pght" name="pght" value="{{ old('pght') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="cost_price_real" class="form-label">{{ __('Prix de revient reel') }}</label>
                                <input type="number" step="0.0001" min="0" class="form-control" id="cost_price_real" name="cost_price_real" value="{{ old('cost_price_real') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

        </div>

        {{-- ═══════ COLONNE DROITE ═══════ --}}
        <div class="col-xl-4">

            {{-- Identification --}}
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-barcode me-2"></i>{{ __('Identification') }}</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="sku" class="form-label">{{ __('SKU') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku') }}" required>
                            <button type="button" class="btn btn-outline-secondary" id="btnGenerateSku" title="{{ __('Generer') }}"><i class="ti ti-refresh"></i></button>
                        </div>
                        @error('sku') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="barcode_type" class="form-label">{{ __('Type code-barres') }}</label>
                        <select class="form-select product-select2" id="barcode_type" name="barcode_type" data-placeholder="{{ __('Selectionner') }}">
                            <option value=""></option>
                            <option value="ean13" {{ old('barcode_type', 'ean13') == 'ean13' ? 'selected' : '' }}>EAN-13</option>
                            <option value="code128" {{ old('barcode_type') == 'code128' ? 'selected' : '' }}>Code 128</option>
                            <option value="code39" {{ old('barcode_type') == 'code39' ? 'selected' : '' }}>Code 39</option>
                            <option value="upc" {{ old('barcode_type') == 'upc' ? 'selected' : '' }}>UPC</option>
                        </select>
                    </div>
                    <div>
                        <label for="barcode" class="form-label">{{ __('Code-barres') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode" name="barcode" value="{{ old('barcode') }}">
                            <button type="button" class="btn btn-outline-secondary" id="btnGenerateBarcode" title="{{ __('Generer') }}"><i class="ti ti-refresh"></i></button>
                        </div>
                        @error('barcode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Images --}}
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-photo me-2"></i>{{ __('Images') }}</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="image" class="form-label">{{ __('Image principale') }}</label>
                        <input type="file" class="form-control form-control-sm @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="img-preview-zone" id="mainImagePreview"></div>
                    </div>
                    <div>
                        <label for="images" class="form-label">{{ __('Galerie') }}</label>
                        <input type="file" class="form-control form-control-sm @error('images') is-invalid @enderror" id="images" name="images[]" accept="image/*" multiple>
                        <div class="form-text">{{ __('Max 10 images, 2 Mo chacune') }}</div>
                        @error('images') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="img-preview-zone" id="galleryPreview"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Bouton sticky --}}
    <div class="sticky-bottom bg-white border-top p-3 text-end" style="position: sticky; bottom: 0; z-index: 100;">
        <a href="{{ route('eshop360.products.index', $instanceSlug) }}" class="btn btn-secondary me-2">{{ __('Annuler') }}</a>
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('Enregistrer le produit') }}</button>
    </div>
</form>

{{-- Modal Quick Create --}}
<div class="modal fade" id="quickCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickCreateModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="quickCreateFields"></div>
                <div class="alert alert-danger d-none" id="quickCreateError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <button type="button" class="btn btn-primary" id="quickCreateSubmit">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="quickCreateSpinner"></span>{{ __('Creer') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('build/plugins/summernote/summernote-lite.min.js') }}"></script>
<script>
(function() {
    'use strict';
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var categoriesData = @json($categoriesJson);

    // ── Summernote rich text ──
    jQuery('#description').summernote({
        height: 180,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview']]
        ],
        callbacks: { onInit: function() { jQuery('.note-editable').css('min-height', '150px'); } }
    });

    // ── Image preview ──
    function setupImagePreview(inputId, previewId) {
        var input = document.getElementById(inputId);
        var preview = document.getElementById(previewId);
        if (!input || !preview) return;
        input.addEventListener('change', function() {
            preview.innerHTML = '';
            var files = this.files;
            for (var i = 0; i < files.length; i++) {
                if (!files[i].type.startsWith('image/')) continue;
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(files[i]);
            }
        });
    }
    setupImagePreview('image', 'mainImagePreview');
    setupImagePreview('images', 'galleryPreview');

    // ── Auto-generate barcode on page load ──
    if (!document.getElementById('barcode').value) {
        generateBarcode();
    }

    // ── Discount toggle ──
    var discountType = document.getElementById('discount_type');
    var discountWrap = document.getElementById('discount_value_wrap');
    function toggleDiscountValue() {
        discountWrap.style.display = discountType.value === 'none' ? 'none' : '';
    }
    discountType.addEventListener('change', toggleDiscountValue);

    // ── Subcategory filtering ──
    var categorySelect = document.getElementById('category_id');
    var subCategorySelect = document.getElementById('sub_category_id');
    function filterSubcategories() {
        var parentId = categorySelect.value;
        var $sub = jQuery('#sub_category_id');
        $sub.find('option[data-parent]').each(function() {
            var $opt = jQuery(this);
            if (parentId && $opt.data('parent') == parentId) {
                $opt.prop('disabled', false);
            } else {
                $opt.prop('disabled', true);
                if ($opt.is(':selected')) $sub.val('').trigger('change.select2');
            }
        });
    }
    jQuery('#category_id').on('change', filterSubcategories);
    filterSubcategories();

    // ── SKU auto-generation ──
    document.getElementById('btnGenerateSku').addEventListener('click', function() {
        var name = document.getElementById('name').value || 'PROD';
        var normalized = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase().replace(/[^A-Z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '').substring(0, 10);
        var digits = ''; for (var i = 0; i < 4; i++) digits += Math.floor(Math.random() * 10);
        document.getElementById('sku').value = normalized + '-' + digits;
    });

    // ── Barcode generation ──
    document.getElementById('btnGenerateBarcode').addEventListener('click', generateBarcode);
    function generateBarcode() {
        var type = document.getElementById('barcode_type').value || 'ean13';
        var code = '';
        if (type === 'ean13') {
            var digits = ''; for (var i = 0; i < 12; i++) digits += Math.floor(Math.random() * 10);
            var sum = 0; for (var i = 0; i < 12; i++) sum += parseInt(digits[i]) * (i % 2 === 0 ? 1 : 3);
            code = digits + ((10 - (sum % 10)) % 10);
        } else {
            var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            var len = type === 'code128' ? 10 : 8;
            for (var i = 0; i < len; i++) code += chars[Math.floor(Math.random() * chars.length)];
        }
        document.getElementById('barcode').value = code;
    }

    // ── Slug auto-generation ──
    var nameInput = document.getElementById('name');
    var slugInput = document.getElementById('slug');
    var slugManuallyEdited = false;
    slugInput.addEventListener('input', function() { slugManuallyEdited = true; });
    nameInput.addEventListener('input', function() {
        if (!slugManuallyEdited) {
            slugInput.value = nameInput.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
    });

    // ── Quick Create Modal System ──
    var quickCreateConfig = {
        category: { url: @json($categoryUrl), title: @json(__('Creer une categorie')), fields: [{ name: 'name', label: @json(__('Nom')), type: 'text', required: true }], selectId: 'category_id' },
        subcategory: { url: @json($categoryUrl), title: @json(__('Creer une sous-categorie')), fields: [{ name: 'name', label: @json(__('Nom')), type: 'text', required: true }, { name: 'parent_id', label: @json(__('Categorie parente')), type: 'select', options: 'categories', required: false }], selectId: 'sub_category_id' },
        brand: { url: @json($brandUrl), title: @json(__('Creer une marque')), fields: [{ name: 'name', label: @json(__('Nom')), type: 'text', required: true }], selectId: 'brand_id' },
        warehouse: { url: @json($warehouseUrl), title: @json(__('Creer un entrepot')), fields: [{ name: 'name', label: @json(__('Nom')), type: 'text', required: true }, { name: 'code', label: @json(__('Code')), type: 'text', required: false }], selectId: 'warehouse_id' },
        store: { url: @json($storeUrl), title: @json(__('Creer un magasin')), fields: [{ name: 'name', label: @json(__('Nom')), type: 'text', required: true }], selectId: 'store_id' },
        unit: { url: '', title: @json(__('Ajouter une unite')), fields: [{ name: 'custom_unit_label', label: @json(__('Libelle')), type: 'text', required: true }, { name: 'custom_unit_value', label: @json(__('Code')), type: 'text', required: true }], selectId: 'unit', local: true }
    };
    var currentQuickType = null;
    var quickModal = new bootstrap.Modal(document.getElementById('quickCreateModal'));
    var quickFieldsContainer = document.getElementById('quickCreateFields');
    var quickError = document.getElementById('quickCreateError');
    var quickSpinner = document.getElementById('quickCreateSpinner');
    var quickSubmitBtn = document.getElementById('quickCreateSubmit');

    document.querySelectorAll('.btn-quick-create').forEach(function(btn) {
        btn.addEventListener('click', function() { openQuickCreate(this.getAttribute('data-type')); });
    });

    function openQuickCreate(type) {
        var config = quickCreateConfig[type]; if (!config) return;
        currentQuickType = type;
        document.getElementById('quickCreateModalLabel').textContent = config.title;
        quickFieldsContainer.innerHTML = '';
        quickError.classList.add('d-none');

        config.fields.forEach(function(field) {
            var div = document.createElement('div'); div.className = 'mb-3';
            var label = document.createElement('label'); label.className = 'form-label'; label.textContent = field.label;
            if (field.required) { var span = document.createElement('span'); span.className = 'text-danger'; span.textContent = ' *'; label.appendChild(span); }
            label.setAttribute('for', 'qc_' + field.name); div.appendChild(label);

            if (field.type === 'select') {
                var select = document.createElement('select'); select.className = 'form-select'; select.id = 'qc_' + field.name; select.name = field.name;
                var emptyOpt = document.createElement('option'); emptyOpt.value = ''; emptyOpt.textContent = '-- ' + @json(__('Selectionner')) + ' --'; select.appendChild(emptyOpt);
                if (field.options === 'categories') {
                    categoriesData.filter(function(c) { return !c.parent_id; }).forEach(function(c) {
                        var opt = document.createElement('option'); opt.value = c.id; opt.textContent = c.name; select.appendChild(opt);
                    });
                }
                div.appendChild(select);
            } else {
                var input = document.createElement('input'); input.type = 'text'; input.className = 'form-control'; input.id = 'qc_' + field.name; input.name = field.name;
                if (field.required) input.required = true; div.appendChild(input);
            }
            quickFieldsContainer.appendChild(div);
        });
        quickModal.show();
        setTimeout(function() { var first = quickFieldsContainer.querySelector('input, select'); if (first) first.focus(); }, 300);
    }

    quickSubmitBtn.addEventListener('click', function() {
        if (!currentQuickType) return;
        var config = quickCreateConfig[currentQuickType];
        if (config.local) { handleLocalQuickCreate(config); return; }

        var formData = {}, valid = true;
        config.fields.forEach(function(field) {
            var el = document.getElementById('qc_' + field.name);
            if (el) { formData[field.name] = el.value; if (field.required && !el.value.trim()) valid = false; }
        });
        if (!valid) { quickError.textContent = @json(__('Veuillez remplir tous les champs obligatoires.')); quickError.classList.remove('d-none'); return; }

        quickSpinner.classList.remove('d-none'); quickSubmitBtn.disabled = true; quickError.classList.add('d-none');

        fetch(config.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify(formData) })
        .then(function(r) { if (!r.ok) return r.json().then(function(e) { throw e; }); return r.json(); })
        .then(function(data) {
            var $target = jQuery('#' + config.selectId);
            var newOpt = new Option(data.name, data.id, true, true);
            if (currentQuickType === 'subcategory') { newOpt.setAttribute('data-parent', formData.parent_id || ''); }
            $target.append(newOpt).trigger('change');
            if (currentQuickType === 'category') categoriesData.push({ id: data.id, name: data.name, parent_id: null });
            if (currentQuickType === 'subcategory') categoriesData.push({ id: data.id, name: data.name, parent_id: parseInt(formData.parent_id) });
            quickModal.hide();
        })
        .catch(function(err) {
            var msg = @json(__('Une erreur est survenue.'));
            if (err && err.errors) { var msgs = []; for (var k in err.errors) err.errors[k].forEach(function(m) { msgs.push(m); }); if (msgs.length) msg = msgs.join(' '); }
            else if (err && err.message) msg = err.message;
            quickError.textContent = msg; quickError.classList.remove('d-none');
        })
        .finally(function() { quickSpinner.classList.add('d-none'); quickSubmitBtn.disabled = false; });
    });

    function handleLocalQuickCreate(config) {
        var labelEl = document.getElementById('qc_custom_unit_label'), valueEl = document.getElementById('qc_custom_unit_value');
        if (!labelEl || !valueEl || !labelEl.value.trim() || !valueEl.value.trim()) { quickError.textContent = @json(__('Veuillez remplir tous les champs obligatoires.')); quickError.classList.remove('d-none'); return; }
        var $target = jQuery('#' + config.selectId);
        $target.append(new Option(labelEl.value.trim() + ' (' + valueEl.value.trim() + ')', valueEl.value.trim(), true, true)).trigger('change');
        quickModal.hide();
    }

    // ── Init Select2 ──
    jQuery(function($) {
        $('.product-select2').each(function() {
            var $el = $(this);
            $el.select2({ theme: 'bootstrap-5', allowClear: true, width: 'resolve', placeholder: $el.data('placeholder') || '', dropdownParent: $el.closest('.card-body') });
        });
        $('.product-select2-multi').each(function() {
            $(this).select2({ theme: 'bootstrap-5', width: '100%', placeholder: $(this).data('placeholder') || '', closeOnSelect: false });
        });

        // ── Stock rows: init Select2 on first row ──
        function initStockRowSelect2($row) {
            $row.find('.stock-store-select, .stock-warehouse-select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) return;
                $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' });
            });
        }
        initStockRowSelect2($('#stock-rows .stock-row').first());

        var stockIndex = 1;
        var storeOptionsHtml = @json($stores->map(fn($s) => '<option value="'.$s->id.'">'.$s->name.'</option>')->implode(''));
        var warehouseOptionsHtml = @json($warehouses->map(fn($w) => '<option value="'.$w->id.'">'.$w->name.'</option>')->implode(''));

        $('#btnAddStockRow').on('click', function() {
            var idx = stockIndex++;
            var html = '<div class="stock-row row g-2 align-items-end mb-2">'
                + '<div class="col-md-3"><label class="form-label">' + @json(__('Magasin')) + '</label>'
                + '<select class="form-select form-select-sm stock-store-select" name="stocks[' + idx + '][store_id]" data-placeholder="' + @json(__('Selectionner')) + '"><option value=""></option>' + storeOptionsHtml + '</select></div>'
                + '<div class="col-md-3"><label class="form-label">' + @json(__('Entrepot')) + '</label>'
                + '<select class="form-select form-select-sm stock-warehouse-select" name="stocks[' + idx + '][warehouse_id]" data-placeholder="' + @json(__('Selectionner')) + '"><option value=""></option>' + warehouseOptionsHtml + '</select></div>'
                + '<div class="col-md-2"><label class="form-label">' + @json(__('Quantite')) + '</label>'
                + '<input type="number" min="0" class="form-control form-control-sm" name="stocks[' + idx + '][quantity]" value="0"></div>'
                + '<div class="col-md-2"><label class="form-label">' + @json(__('Seuil alerte')) + '</label>'
                + '<input type="number" min="0" class="form-control form-control-sm" name="stocks[' + idx + '][alert_quantity]" value=""></div>'
                + '<div class="col-md-2 d-flex gap-1"><div class="flex-grow-1"><label class="form-label">' + @json(__('Qte min.')) + '</label>'
                + '<input type="number" min="0" class="form-control form-control-sm" name="stocks[' + idx + '][min_quantity]" value="0"></div>'
                + '<div class="d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-stock-row" title="' + @json(__('Supprimer')) + '"><i class="ti ti-trash"></i></button></div></div>'
                + '</div>';
            var $row = $(html);
            $('#stock-rows').append($row);
            initStockRowSelect2($row);
        });

        $('#stock-rows').on('click', '.btn-remove-stock-row', function() {
            var $row = $(this).closest('.stock-row');
            $row.find('select').select2('destroy');
            $row.remove();
        });
    });

})();
</script>
@endpush

</x-dashboard::layouts.master>

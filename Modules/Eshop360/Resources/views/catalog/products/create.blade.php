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
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('eshop360.products.store', $instanceSlug) }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf

    {{-- ═══════ Section 1 : Informations de base ═══════ --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-info-circle me-2"></i>{{ __('Informations de base') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                {{-- Nom --}}
                <div class="col-md-8">
                    <label for="name" class="form-label">{{ __('Nom du produit') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Categorie --}}
                <div class="col-md-4">
                    <label for="category_id" class="form-label">{{ __('Categorie') }}</label>
                    <div class="input-group">
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                            <option value="">{{ __('-- Selectionner --') }}</option>
                            @foreach($categories->whereNull('parent_id') as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-quick-create" data-type="category" title="{{ __('Creer une categorie') }}">
                            <i class="ti ti-plus"></i>
                        </button>
                    </div>
                    @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- Sous-categorie --}}
                <div class="col-md-4">
                    <label for="sub_category_id" class="form-label">{{ __('Sous-categorie') }}</label>
                    <select class="form-select" id="sub_category_id" name="sub_category_id">
                        <option value="">{{ __('-- Selectionner --') }}</option>
                        @foreach($categories->whereNotNull('parent_id') as $sub)
                            <option value="{{ $sub->id }}" data-parent="{{ $sub->parent_id }}" {{ old('sub_category_id') == $sub->id ? 'selected' : '' }} style="display:none;">{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Marque --}}
                <div class="col-md-4">
                    <label for="brand_id" class="form-label">{{ __('Marque') }}</label>
                    <div class="input-group">
                        <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                            <option value="">{{ __('-- Selectionner --') }}</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-quick-create" data-type="brand" title="{{ __('Creer une marque') }}">
                            <i class="ti ti-plus"></i>
                        </button>
                    </div>
                    @error('brand_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- Unite --}}
                <div class="col-md-4">
                    <label for="unit" class="form-label">{{ __('Unite') }}</label>
                    <div class="input-group">
                        <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit">
                            <option value="">{{ __('-- Selectionner --') }}</option>
                            <option value="bx" {{ old('unit') == 'bx' ? 'selected' : '' }}>{{ __('Boite (bx)') }}</option>
                            <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>{{ __('Piece (pcs)') }}</option>
                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>{{ __('Kilogramme (kg)') }}</option>
                            <option value="l" {{ old('unit') == 'l' ? 'selected' : '' }}>{{ __('Litre (l)') }}</option>
                            <option value="fl" {{ old('unit') == 'fl' ? 'selected' : '' }}>{{ __('Flacon (fl)') }}</option>
                            <option value="tb" {{ old('unit') == 'tb' ? 'selected' : '' }}>{{ __('Tube (tb)') }}</option>
                            <option value="sac" {{ old('unit') == 'sac' ? 'selected' : '' }}>{{ __('Sachet (sac)') }}</option>
                            <option value="cp" {{ old('unit') == 'cp' ? 'selected' : '' }}>{{ __('Comprime (cp)') }}</option>
                            <option value="amp" {{ old('unit') == 'amp' ? 'selected' : '' }}>{{ __('Ampoule (amp)') }}</option>
                            <option value="dz" {{ old('unit') == 'dz' ? 'selected' : '' }}>{{ __('Douzaine (dz)') }}</option>
                            <option value="plq" {{ old('unit') == 'plq' ? 'selected' : '' }}>{{ __('Plaquette (plq)') }}</option>
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-quick-create" data-type="unit" title="{{ __('Creer une unite') }}">
                            <i class="ti ti-plus"></i>
                        </button>
                    </div>
                    @error('unit') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- Type de vente --}}
                <div class="col-md-4">
                    <label for="selling_type" class="form-label">{{ __('Type de vente') }}</label>
                    <select class="form-select" id="selling_type" name="selling_type">
                        <option value="both" {{ old('selling_type', 'both') == 'both' ? 'selected' : '' }}>{{ __('En ligne et en magasin') }}</option>
                        <option value="pos" {{ old('selling_type') == 'pos' ? 'selected' : '' }}>{{ __('En magasin uniquement') }}</option>
                        <option value="online" {{ old('selling_type') == 'online' ? 'selected' : '' }}>{{ __('En ligne uniquement') }}</option>
                    </select>
                </div>

                {{-- Description --}}
                <div class="col-12">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════ Section 2 : Prix et stock ═══════ --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-currency-dollar me-2"></i>{{ __('Prix et stock') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                {{-- Prix --}}
                <div class="col-md-4">
                    <label for="price" class="form-label">{{ __('Prix de vente') }} <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Quantite --}}
                <div class="col-md-4">
                    <label for="min_quantity" class="form-label">{{ __('Quantite minimale') }}</label>
                    <input type="number" min="0" class="form-control @error('min_quantity') is-invalid @enderror" id="min_quantity" name="min_quantity" value="{{ old('min_quantity', 0) }}">
                    @error('min_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Magasin --}}
                <div class="col-md-4">
                    <label for="store_id" class="form-label">{{ __('Magasin') }}</label>
                    <div class="input-group">
                        <select class="form-select" id="store_id" name="store_id">
                            <option value="">{{ __('-- Selectionner --') }}</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-quick-create" data-type="store" title="{{ __('Creer un magasin') }}">
                            <i class="ti ti-plus"></i>
                        </button>
                    </div>
                </div>

                {{-- Entrepot --}}
                <div class="col-md-4">
                    <label for="warehouse_id" class="form-label">{{ __('Entrepot') }}</label>
                    <div class="input-group">
                        <select class="form-select" id="warehouse_id" name="warehouse_id">
                            <option value="">{{ __('-- Selectionner --') }}</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-quick-create" data-type="warehouse" title="{{ __('Creer un entrepot') }}">
                            <i class="ti ti-plus"></i>
                        </button>
                    </div>
                </div>

                {{-- Seuil alerte --}}
                <div class="col-md-4">
                    <label for="alert_quantity" class="form-label">{{ __('Seuil d\'alerte stock') }}</label>
                    <input type="number" min="0" class="form-control @error('alert_quantity') is-invalid @enderror" id="alert_quantity" name="alert_quantity" value="{{ old('alert_quantity') }}">
                    @error('alert_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Type taxe --}}
                <div class="col-md-4">
                    <label for="tax_type" class="form-label">{{ __('Type de taxe') }}</label>
                    <select class="form-select" id="tax_type" name="tax_type">
                        <option value="">{{ __('-- Aucune --') }}</option>
                        <option value="percentage" {{ old('tax_type') == 'percentage' ? 'selected' : '' }}>{{ __('Pourcentage (%)') }}</option>
                        <option value="fixed" {{ old('tax_type') == 'fixed' ? 'selected' : '' }}>{{ __('Montant fixe') }}</option>
                    </select>
                </div>

                {{-- Taux taxe --}}
                <div class="col-md-4">
                    <label for="tax_rate" class="form-label">{{ __('Taux de taxe') }}</label>
                    <input type="number" step="0.01" min="0" max="100" class="form-control @error('tax_rate') is-invalid @enderror" id="tax_rate" name="tax_rate" value="{{ old('tax_rate') }}">
                    @error('tax_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Type remise --}}
                <div class="col-md-4">
                    <label for="discount_type" class="form-label">{{ __('Type de remise') }}</label>
                    <select class="form-select @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type">
                        <option value="none" {{ old('discount_type', 'none') == 'none' ? 'selected' : '' }}>{{ __('Aucune') }}</option>
                        <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>{{ __('Pourcentage (%)') }}</option>
                        <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>{{ __('Montant fixe') }}</option>
                    </select>
                    @error('discount_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Valeur remise --}}
                <div class="col-md-4">
                    <label for="discount_value" class="form-label">{{ __('Valeur de la remise') }}</label>
                    <input type="number" step="0.01" min="0" class="form-control @error('discount_value') is-invalid @enderror" id="discount_value" name="discount_value" value="{{ old('discount_value') }}">
                    @error('discount_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════ Section 3 : Identification ═══════ --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-barcode me-2"></i>{{ __('Identification') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                {{-- SKU --}}
                <div class="col-md-4">
                    <label for="sku" class="form-label">{{ __('SKU') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku') }}" required>
                        <button type="button" class="btn btn-outline-secondary" id="btnGenerateSku" title="{{ __('Generer automatiquement') }}">
                            <i class="ti ti-refresh"></i>
                        </button>
                    </div>
                    @error('sku') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- Code-barres type --}}
                <div class="col-md-4">
                    <label for="barcode_type" class="form-label">{{ __('Type de code-barres') }}</label>
                    <select class="form-select @error('barcode_type') is-invalid @enderror" id="barcode_type" name="barcode_type">
                        <option value="">{{ __('-- Selectionner --') }}</option>
                        <option value="ean13" {{ old('barcode_type') == 'ean13' ? 'selected' : '' }}>EAN-13</option>
                        <option value="code128" {{ old('barcode_type') == 'code128' ? 'selected' : '' }}>Code 128</option>
                        <option value="code39" {{ old('barcode_type') == 'code39' ? 'selected' : '' }}>Code 39</option>
                        <option value="upc" {{ old('barcode_type') == 'upc' ? 'selected' : '' }}>UPC</option>
                    </select>
                    @error('barcode_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Code-barres --}}
                <div class="col-md-4">
                    <label for="barcode" class="form-label">{{ __('Code-barres') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode" name="barcode" value="{{ old('barcode') }}">
                        <button type="button" class="btn btn-outline-secondary" id="btnGenerateBarcode" title="{{ __('Generer automatiquement') }}">
                            <i class="ti ti-refresh"></i>
                        </button>
                    </div>
                    @error('barcode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- Slug --}}
                <div class="col-md-4">
                    <label for="slug" class="form-label">{{ __('Slug') }}</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}" placeholder="{{ __('Auto-genere a partir du nom') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════ Section 4 : Images ═══════ --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-photo me-2"></i>{{ __('Images') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="image" class="form-label">{{ __('Image principale') }}</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="images" class="form-label">{{ __('Galerie d\'images') }}</label>
                    <input type="file" class="form-control @error('images') is-invalid @enderror" id="images" name="images[]" accept="image/*" multiple>
                    <div class="form-text">{{ __('Maximum 10 images, 2 Mo chacune') }}</div>
                    @error('images') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════ Section 5 : Options avancees ═══════ --}}
    <div class="card mb-3">
        <div class="card-header" role="button" data-bs-toggle="collapse" data-bs-target="#advancedOptions" aria-expanded="false">
            <h5 class="card-title mb-0">
                <i class="ti ti-settings me-2"></i>{{ __('Options avancees') }}
                <i class="ti ti-chevron-down float-end"></i>
            </h5>
        </div>
        <div class="collapse" id="advancedOptions">
            <div class="card-body">
                <div class="row g-3">
                    {{-- Garantie --}}
                    <div class="col-md-4">
                        <label for="warranty" class="form-label">{{ __('Garantie') }}</label>
                        <input type="text" class="form-control" id="warranty" name="warranty" value="{{ old('warranty') }}" placeholder="{{ __('Ex: 12 mois') }}">
                    </div>

                    {{-- Fabricant --}}
                    <div class="col-md-4">
                        <label for="manufacturer" class="form-label">{{ __('Fabricant') }}</label>
                        <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="{{ old('manufacturer') }}">
                    </div>

                    {{-- Date de fabrication --}}
                    <div class="col-md-4">
                        <label for="manufactured_date" class="form-label">{{ __('Date de fabrication') }}</label>
                        <input type="date" class="form-control @error('manufactured_date') is-invalid @enderror" id="manufactured_date" name="manufactured_date" value="{{ old('manufactured_date') }}">
                        @error('manufactured_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Date d'expiration --}}
                    <div class="col-md-4">
                        <label for="expiry_date" class="form-label">{{ __('Date d\'expiration') }}</label>
                        <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                        @error('expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Numero de lot --}}
                    <div class="col-md-4">
                        <label for="batch_number" class="form-label">{{ __('Numero de lot') }}</label>
                        <input type="text" class="form-control" id="batch_number" name="batch_number" value="{{ old('batch_number') }}">
                    </div>

                    {{-- Actif --}}
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

    {{-- ═══════ Section 6 : Tarification SAPHIR ═══════ --}}
    @can('products.factory_price')
    <div class="card mb-3">
        <div class="card-header" role="button" data-bs-toggle="collapse" data-bs-target="#saphirPricing" aria-expanded="false">
            <h5 class="card-title mb-0">
                <i class="ti ti-building-factory me-2"></i>{{ __('Tarification SAPHIR') }}
                <i class="ti ti-chevron-down float-end"></i>
            </h5>
        </div>
        <div class="collapse" id="saphirPricing">
            <div class="card-body">
                <div class="row g-3">
                    {{-- Prix d'achat usine --}}
                    <div class="col-md-4">
                        <label for="purchase_price_factory" class="form-label">{{ __('Prix d\'achat usine') }}</label>
                        <input type="number" step="0.0001" min="0" class="form-control @error('purchase_price_factory') is-invalid @enderror" id="purchase_price_factory" name="purchase_price_factory" value="{{ old('purchase_price_factory') }}">
                        @error('purchase_price_factory') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Prix d'achat provisionnel --}}
                    <div class="col-md-4">
                        <label for="purchase_price_provisional" class="form-label">{{ __('Prix d\'achat provisionnel') }}</label>
                        <input type="number" step="0.0001" min="0" class="form-control @error('purchase_price_provisional') is-invalid @enderror" id="purchase_price_provisional" name="purchase_price_provisional" value="{{ old('purchase_price_provisional') }}">
                        @error('purchase_price_provisional') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- PGHT --}}
                    <div class="col-md-4">
                        <label for="pght" class="form-label">{{ __('PGHT') }}</label>
                        <input type="number" step="0.0001" min="0" class="form-control @error('pght') is-invalid @enderror" id="pght" name="pght" value="{{ old('pght') }}">
                        @error('pght') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Prix de revient reel --}}
                    <div class="col-md-4">
                        <label for="cost_price_real" class="form-label">{{ __('Prix de revient reel') }}</label>
                        <input type="number" step="0.0001" min="0" class="form-control @error('cost_price_real') is-invalid @enderror" id="cost_price_real" name="cost_price_real" value="{{ old('cost_price_real') }}">
                        @error('cost_price_real') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Cout de revient --}}
                    <div class="col-md-4">
                        <label for="cost_price" class="form-label">{{ __('Cout de revient') }}</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price') }}">
                        @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- ═══════ Bouton de soumission sticky ═══════ --}}
    <div class="sticky-bottom bg-white border-top p-3 text-end" style="position: sticky; bottom: 0; z-index: 100;">
        <a href="{{ route('eshop360.products.index', $instanceSlug) }}" class="btn btn-secondary me-2">{{ __('Annuler') }}</a>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy me-1"></i>{{ __('Enregistrer le produit') }}
        </button>
    </div>
</form>

{{-- ═══════ Modal Quick Create (generique) ═══════ --}}
<div class="modal fade" id="quickCreateModal" tabindex="-1" aria-labelledby="quickCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickCreateModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Fermer') }}"></button>
            </div>
            <div class="modal-body">
                <div id="quickCreateFields"></div>
                <div class="alert alert-danger d-none" id="quickCreateError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <button type="button" class="btn btn-primary" id="quickCreateSubmit">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="quickCreateSpinner"></span>
                    {{ __('Creer') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    'use strict';

    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ── Categories data for subcategory filtering and quick-create ──
    var categoriesData = @json($categoriesJson);

    // ── Quick Create Configuration ──
    var quickCreateConfig = {
        category: {
            url: '{{ $categoryUrl }}',
            title: '{{ __("Creer une categorie") }}',
            fields: [
                { name: 'name', label: '{{ __("Nom") }}', type: 'text', required: true }
            ],
            selectId: 'category_id'
        },
        subcategory: {
            url: '{{ $categoryUrl }}',
            title: '{{ __("Creer une sous-categorie") }}',
            fields: [
                { name: 'name', label: '{{ __("Nom") }}', type: 'text', required: true },
                { name: 'parent_id', label: '{{ __("Categorie parente") }}', type: 'select', options: 'categories', required: false }
            ],
            selectId: 'sub_category_id'
        },
        brand: {
            url: '{{ $brandUrl }}',
            title: '{{ __("Creer une marque") }}',
            fields: [
                { name: 'name', label: '{{ __("Nom") }}', type: 'text', required: true }
            ],
            selectId: 'brand_id'
        },
        warehouse: {
            url: '{{ $warehouseUrl }}',
            title: '{{ __("Creer un entrepot") }}',
            fields: [
                { name: 'name', label: '{{ __("Nom") }}', type: 'text', required: true },
                { name: 'code', label: '{{ __("Code") }}', type: 'text', required: false }
            ],
            selectId: 'warehouse_id'
        },
        store: {
            url: '{{ $storeUrl }}',
            title: '{{ __("Creer un magasin") }}',
            fields: [
                { name: 'name', label: '{{ __("Nom") }}', type: 'text', required: true }
            ],
            selectId: 'store_id'
        },
        unit: {
            url: '',
            title: '{{ __("Ajouter une unite") }}',
            fields: [
                { name: 'custom_unit_label', label: '{{ __("Libelle") }}', type: 'text', required: true },
                { name: 'custom_unit_value', label: '{{ __("Code") }}', type: 'text', required: true }
            ],
            selectId: 'unit',
            local: true
        }
    };

    var currentQuickType = null;

    // ── Subcategory filtering ──
    var categorySelect = document.getElementById('category_id');
    var subCategorySelect = document.getElementById('sub_category_id');

    function filterSubcategories() {
        var parentId = categorySelect.value;
        var options = subCategorySelect.querySelectorAll('option[data-parent]');
        subCategorySelect.value = '';
        for (var i = 0; i < options.length; i++) {
            if (parentId && options[i].getAttribute('data-parent') === parentId) {
                options[i].style.display = '';
            } else {
                options[i].style.display = 'none';
            }
        }
    }

    categorySelect.addEventListener('change', filterSubcategories);
    // Initial filter
    filterSubcategories();

    // ── SKU auto-generation ──
    document.getElementById('btnGenerateSku').addEventListener('click', function() {
        var name = document.getElementById('name').value;
        if (!name) {
            name = 'PROD';
        }
        // Normalize: remove accents, uppercase, replace spaces with hyphens
        var normalized = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase().replace(/[^A-Z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
        // Truncate to 10 chars and append 4 random digits
        normalized = normalized.substring(0, 10);
        var digits = '';
        for (var i = 0; i < 4; i++) {
            digits += Math.floor(Math.random() * 10);
        }
        document.getElementById('sku').value = normalized + '-' + digits;
    });

    // ── Barcode generation ──
    document.getElementById('btnGenerateBarcode').addEventListener('click', function() {
        generateBarcode();
    });

    function generateBarcode() {
        var type = document.getElementById('barcode_type').value;
        var code = '';
        if (type === 'ean13') {
            var digits = '';
            for (var i = 0; i < 12; i++) digits += Math.floor(Math.random() * 10);
            var sum = 0;
            for (var i = 0; i < 12; i++) sum += parseInt(digits[i]) * (i % 2 === 0 ? 1 : 3);
            var check = (10 - (sum % 10)) % 10;
            code = digits + check;
        } else if (type === 'code128') {
            var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            for (var i = 0; i < 10; i++) code += chars[Math.floor(Math.random() * chars.length)];
        } else {
            var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            for (var i = 0; i < 8; i++) code += chars[Math.floor(Math.random() * chars.length)];
        }
        document.getElementById('barcode').value = code;
    }

    // ── Quick Create Modal System ──
    var quickModal = new bootstrap.Modal(document.getElementById('quickCreateModal'));
    var quickFieldsContainer = document.getElementById('quickCreateFields');
    var quickError = document.getElementById('quickCreateError');
    var quickSpinner = document.getElementById('quickCreateSpinner');
    var quickSubmitBtn = document.getElementById('quickCreateSubmit');

    // Attach click handlers to all quick-create buttons
    var quickBtns = document.querySelectorAll('.btn-quick-create');
    for (var b = 0; b < quickBtns.length; b++) {
        quickBtns[b].addEventListener('click', function() {
            openQuickCreate(this.getAttribute('data-type'));
        });
    }

    function openQuickCreate(type) {
        var config = quickCreateConfig[type];
        if (!config) return;

        currentQuickType = type;

        // Set title
        document.getElementById('quickCreateModalLabel').textContent = config.title;

        // Clear fields container
        while (quickFieldsContainer.firstChild) {
            quickFieldsContainer.removeChild(quickFieldsContainer.firstChild);
        }

        // Hide error
        quickError.classList.add('d-none');
        quickError.textContent = '';

        // Build fields
        for (var f = 0; f < config.fields.length; f++) {
            var field = config.fields[f];
            var div = document.createElement('div');
            div.className = 'mb-3';

            var label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = field.label;
            if (field.required) {
                var span = document.createElement('span');
                span.className = 'text-danger';
                span.textContent = ' *';
                label.appendChild(span);
            }
            label.setAttribute('for', 'qc_' + field.name);
            div.appendChild(label);

            if (field.type === 'select') {
                var select = document.createElement('select');
                select.className = 'form-select';
                select.id = 'qc_' + field.name;
                select.name = field.name;
                if (field.required) select.required = true;

                var emptyOpt = document.createElement('option');
                emptyOpt.value = '';
                emptyOpt.textContent = '-- {{ __("Selectionner") }} --';
                select.appendChild(emptyOpt);

                // Populate options from categories data
                if (field.options === 'categories') {
                    for (var c = 0; c < categoriesData.length; c++) {
                        if (!categoriesData[c].parent_id) {
                            var opt = document.createElement('option');
                            opt.value = categoriesData[c].id;
                            opt.textContent = categoriesData[c].name;
                            select.appendChild(opt);
                        }
                    }
                }

                div.appendChild(select);
            } else {
                var input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control';
                input.id = 'qc_' + field.name;
                input.name = field.name;
                if (field.required) input.required = true;
                div.appendChild(input);
            }

            quickFieldsContainer.appendChild(div);
        }

        quickModal.show();
    }

    quickSubmitBtn.addEventListener('click', function() {
        if (!currentQuickType) return;
        var config = quickCreateConfig[currentQuickType];
        if (!config) return;

        // Handle local-only types (unit)
        if (config.local) {
            handleLocalQuickCreate(config);
            return;
        }

        // Collect form data
        var formData = {};
        var valid = true;
        for (var f = 0; f < config.fields.length; f++) {
            var field = config.fields[f];
            var el = document.getElementById('qc_' + field.name);
            if (el) {
                formData[field.name] = el.value;
                if (field.required && !el.value.trim()) {
                    valid = false;
                }
            }
        }

        if (!valid) {
            quickError.textContent = '{{ __("Veuillez remplir tous les champs obligatoires.") }}';
            quickError.classList.remove('d-none');
            return;
        }

        // Show spinner
        quickSpinner.classList.remove('d-none');
        quickSubmitBtn.disabled = true;
        quickError.classList.add('d-none');

        // AJAX request
        fetch(config.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(formData)
        })
        .then(function(response) {
            if (!response.ok) {
                return response.json().then(function(err) { throw err; });
            }
            return response.json();
        })
        .then(function(data) {
            // Add new option to target select
            var targetSelect = document.getElementById(config.selectId);
            if (targetSelect) {
                var newOption = document.createElement('option');
                newOption.value = data.id;
                newOption.textContent = data.name;
                newOption.selected = true;
                targetSelect.appendChild(newOption);

                // If we added a category, update categoriesData and trigger subcategory filter
                if (currentQuickType === 'category') {
                    categoriesData.push({ id: data.id, name: data.name, parent_id: null });
                }
                if (currentQuickType === 'subcategory') {
                    // Add data-parent attribute to the new option
                    var parentVal = document.getElementById('qc_parent_id');
                    if (parentVal) {
                        newOption.setAttribute('data-parent', parentVal.value);
                        newOption.style.display = '';
                        categoriesData.push({ id: data.id, name: data.name, parent_id: parseInt(parentVal.value) });
                    }
                }

                // Trigger change event
                targetSelect.dispatchEvent(new Event('change'));
            }

            quickModal.hide();
        })
        .catch(function(err) {
            var msg = '{{ __("Une erreur est survenue.") }}';
            if (err && err.message) {
                msg = err.message;
            }
            if (err && err.errors) {
                var messages = [];
                for (var key in err.errors) {
                    if (err.errors.hasOwnProperty(key)) {
                        for (var e = 0; e < err.errors[key].length; e++) {
                            messages.push(err.errors[key][e]);
                        }
                    }
                }
                if (messages.length) msg = messages.join(' ');
            }
            quickError.textContent = msg;
            quickError.classList.remove('d-none');
        })
        .finally(function() {
            quickSpinner.classList.add('d-none');
            quickSubmitBtn.disabled = false;
        });
    });

    function handleLocalQuickCreate(config) {
        var labelEl = document.getElementById('qc_custom_unit_label');
        var valueEl = document.getElementById('qc_custom_unit_value');
        if (!labelEl || !valueEl || !labelEl.value.trim() || !valueEl.value.trim()) {
            quickError.textContent = '{{ __("Veuillez remplir tous les champs obligatoires.") }}';
            quickError.classList.remove('d-none');
            return;
        }

        var targetSelect = document.getElementById(config.selectId);
        if (targetSelect) {
            var newOption = document.createElement('option');
            newOption.value = valueEl.value.trim();
            newOption.textContent = labelEl.value.trim() + ' (' + valueEl.value.trim() + ')';
            newOption.selected = true;
            targetSelect.appendChild(newOption);
        }

        quickModal.hide();
    }

    // ── Slug auto-generation from name ──
    var nameInput = document.getElementById('name');
    var slugInput = document.getElementById('slug');
    var slugManuallyEdited = false;

    slugInput.addEventListener('input', function() {
        slugManuallyEdited = true;
    });

    nameInput.addEventListener('input', function() {
        if (!slugManuallyEdited) {
            var val = nameInput.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            slugInput.value = val;
        }
    });

})();
</script>
@endpush

</x-dashboard::layouts.master>

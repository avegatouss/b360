<x-dashboard::layouts.master
    :title="__('Modifier le produit') . ' — ' . $product->name"
    :instance="$instance"
    :pageTitle="__('Modifier le produit')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Modifier le produit') }}</h4>
            <h6>{{ $product->name }} <span class="text-muted">({{ $product->sku }})</span></h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.products.index', $slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Retour aux produits') }}
        </a>
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
        {{-- LEFT: Main info --}}
        <div class="col-xl-8">
            {{-- Information produit --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-2"></i>{{ __('Informations generales') }}</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Nom du produit') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('SKU') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="sku" id="sku-input" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" required>
                                <button type="button" class="btn btn-outline-primary" id="generate-sku-btn" title="{{ __('Generer le SKU') }}"><i class="ti ti-refresh"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Categorie') }}</label>
                            <select name="category_id" class="form-select select2-edit">
                                <option value="">{{ __('-- Aucune --') }}</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Marque') }}</label>
                            <select name="brand_id" class="form-select select2-edit">
                                <option value="">{{ __('-- Aucune --') }}</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="5" style="width:100%;">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tarification --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-currency-dollar me-2"></i>{{ __('Tarification') }}</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Prix de vente') }} <span class="text-danger">*</span></label>
                            <input type="number" name="price" step="0.01" class="form-control" value="{{ old('price', $product->price) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Prix de revient') }}</label>
                            <input type="number" name="cost_price" step="0.01" class="form-control" value="{{ old('cost_price', $product->cost_price) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Taux de taxe (%)') }}</label>
                            <input type="number" name="tax_rate" step="0.01" min="0" max="100" class="form-control" value="{{ old('tax_rate', $product->tax_rate ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Type de remise') }}</label>
                            <select name="discount_type" class="form-select">
                                <option value="none" {{ old('discount_type', $product->discount_type) == 'none' ? 'selected' : '' }}>{{ __('Aucune') }}</option>
                                <option value="percentage" {{ old('discount_type', $product->discount_type) == 'percentage' ? 'selected' : '' }}>{{ __('Pourcentage') }}</option>
                                <option value="fixed" {{ old('discount_type', $product->discount_type) == 'fixed' ? 'selected' : '' }}>{{ __('Montant fixe') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Valeur de la remise') }}</label>
                            <input type="number" name="discount_value" step="0.01" min="0" class="form-control" value="{{ old('discount_value', $product->discount_value) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Prix grossiste') }}</label>
                            <input type="number" name="wholesale_price" step="0.01" class="form-control" value="{{ old('wholesale_price', $product->wholesale_price) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tarification avancee --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent cursor-pointer" data-bs-toggle="collapse" data-bs-target="#saphirSection" style="cursor:pointer;">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-pill me-2"></i>{{ __('Tarification SAPHIR & Pharma') }} <i class="ti ti-chevron-down ms-2 small"></i></h6>
                </div>
                <div class="collapse" id="saphirSection">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3"><label class="form-label">{{ __('PA Usine') }}</label><input type="number" step="0.0001" name="purchase_price_factory" class="form-control" value="{{ old('purchase_price_factory', $product->purchase_price_factory) }}"></div>
                            <div class="col-md-3"><label class="form-label">{{ __('PGHT') }}</label><input type="number" step="0.0001" name="pght" class="form-control" value="{{ old('pght', $product->pght) }}"></div>
                            <div class="col-md-3"><label class="form-label">{{ __('Prix pharmacie') }}</label><input type="number" step="0.01" name="pharmacy_price" class="form-control" value="{{ old('pharmacy_price', $product->pharmacy_price) }}"></div>
                            <div class="col-md-3"><label class="form-label">{{ __('Qte min. grossiste') }}</label><input type="number" min="1" name="min_qty_wholesale" class="form-control" value="{{ old('min_qty_wholesale', $product->min_qty_wholesale ?? 1) }}"></div>
                            <div class="col-md-4"><label class="form-label">{{ __('DCI') }}</label><input type="text" name="dci" class="form-control" value="{{ old('dci', $product->dci) }}" placeholder="Ex: Amoxicilline"></div>
                            <div class="col-md-2"><label class="form-label">{{ __('Dosage') }}</label><input type="text" name="dosage" class="form-control" value="{{ old('dosage', $product->dosage) }}" placeholder="500mg"></div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Forme') }}</label>
                                <select name="form" class="form-select">
                                    <option value="">—</option>
                                    @foreach(['Comprime','Gelule','Sirop','Injectable','Suppositoire','Pommade','Creme','Gouttes','Spray','Patch','Sachet','Autre'] as $f)
                                        <option value="{{ $f }}" {{ old('form', $product->form) == $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3"><label class="form-label">{{ __('Conditionnement') }}</label><input type="text" name="packaging" class="form-control" value="{{ old('packaging', $product->packaging) }}" placeholder="Boite de 20"></div>
                            <div class="col-md-3"><label class="form-label">{{ __('N de lot') }}</label><input type="text" name="batch_number" class="form-control" value="{{ old('batch_number', $product->batch_number) }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Sidebar --}}
        <div class="col-xl-4">
            {{-- Image --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-photo me-2"></i>{{ __('Image') }}</h6></div>
                <div class="card-body text-center">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded mb-2" style="max-height:160px;">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-2" style="height:120px;"><i class="ti ti-package fs-1 text-muted"></i></div>
                    @endif
                    <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                    <small class="text-muted">{{ __('Max 2 Mo. JPG, PNG, WebP') }}</small>
                </div>
            </div>

            {{-- Statut & Type de vente --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-settings me-2"></i>{{ __('Statut & Vente') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} id="is_active">
                            <label class="form-check-label" for="is_active">{{ __('Produit actif') }}</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Type de vente') }} <span class="text-danger">*</span></label>
                        <select name="selling_type" class="form-select">
                            <option value="both" {{ old('selling_type', $product->selling_type ?? 'both') == 'both' ? 'selected' : '' }}>{{ __('POS + En ligne') }}</option>
                            <option value="pos" {{ old('selling_type', $product->selling_type) == 'pos' ? 'selected' : '' }}>{{ __('POS uniquement') }}</option>
                            <option value="online" {{ old('selling_type', $product->selling_type) == 'online' ? 'selected' : '' }}>{{ __('En ligne uniquement') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Unite') }}</label>
                        <select name="unit" class="form-select">
                            @foreach(['pcs' => 'Piece', 'kg' => 'Kg', 'l' => 'Litre', 'bx' => 'Boite', 'dz' => 'Douzaine', 'bt' => 'Bouteille', 'fl' => 'Flacon'] as $val => $label)
                                <option value="{{ $val }}" {{ old('unit', $product->unit) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Identification --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-barcode me-2"></i>{{ __('Identification') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Code-barres') }}</label>
                        <div class="input-group">
                            <input type="text" name="barcode" id="barcode-input" class="form-control" value="{{ old('barcode', $product->barcode) }}">
                            <button type="button" class="btn btn-outline-primary" id="generate-barcode-btn" title="{{ __('Generer') }}"><i class="ti ti-refresh"></i></button>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">{{ __('Seuil alerte stock') }}</label>
                            <input type="number" name="alert_quantity" min="0" class="form-control" value="{{ old('alert_quantity', $product->alert_quantity) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Qte minimum') }}</label>
                            <input type="number" name="min_quantity" min="0" class="form-control" value="{{ old('min_quantity', $product->min_quantity) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dates --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-calendar me-2"></i>{{ __('Dates') }}</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">{{ __('Fabrication') }}</label>
                            <input type="date" name="manufactured_date" class="form-control" value="{{ old('manufactured_date', $product->manufactured_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Expiration') }}</label>
                            <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', $product->expiry_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Enregistrer les modifications') }}</button>
        <a href="{{ route('eshop360.products.index', $slug) }}" class="btn btn-outline-secondary">{{ __('Annuler') }}</a>
    </div>
</form>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select2
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('.select2-edit').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%' });
    }

    // Generate SKU
    document.getElementById('generate-sku-btn')?.addEventListener('click', function () {
        var name = document.querySelector('[name="name"]').value || 'PROD';
        var prefix = name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, 'X');
        var random = Math.random().toString(36).substring(2, 8).toUpperCase();
        document.getElementById('sku-input').value = prefix + '-' + random;
    });

    // Generate Barcode (EAN-13)
    document.getElementById('generate-barcode-btn')?.addEventListener('click', function () {
        var code = '690';
        for (var i = 0; i < 9; i++) code += Math.floor(Math.random() * 10);
        var sum = 0;
        for (var j = 0; j < 12; j++) sum += parseInt(code[j]) * (j % 2 === 0 ? 1 : 3);
        code += String((10 - (sum % 10)) % 10);
        document.getElementById('barcode-input').value = code;
    });
});
</script>
@endpush

</x-dashboard::layouts.master>

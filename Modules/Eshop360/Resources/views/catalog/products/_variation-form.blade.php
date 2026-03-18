{{-- Shared form partial for product variation create/edit --}}
@php $v = $variation ?? null; @endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label">{{ __('Nom de la variante') }} <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $v?->name) }}" required placeholder="Ex: Rouge - Taille L">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('SKU') }}</label>
        <input type="text" name="sku" class="form-control" value="{{ old('sku', $v?->sku) }}" placeholder="{{ __('Optionnel') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('Code-barres') }}</label>
        <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $v?->barcode) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('Prix de vente') }}</label>
        <input type="number" name="price" class="form-control" value="{{ old('price', $v?->price) }}" min="0" step="0.01" placeholder="{{ __('Herite du produit') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('Prix de revient') }}</label>
        <input type="number" name="cost_price" class="form-control" value="{{ old('cost_price', $v?->cost_price) }}" min="0" step="0.01">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('Quantite initiale') }}</label>
        <input type="number" name="quantity" class="form-control" value="{{ old('quantity', $v?->quantity ?? 0) }}" min="0">
    </div>

    {{-- Dynamic attribute key-value pairs --}}
    <div class="col-12">
        <label class="form-label">{{ __('Attributs') }}</label>
        <div id="variation-attrs">
            @if($v && $v->values)
                @foreach($v->values as $attrKey => $attrVal)
                    <div class="row g-2 mb-2 attr-row">
                        <div class="col-5">
                            <input type="text" name="values[{{ $attrKey }}]" class="form-control form-control-sm" value="{{ $attrVal }}" placeholder="{{ $attrKey }}">
                        </div>
                        <div class="col-5">
                            <input type="text" class="form-control form-control-sm text-muted" value="{{ $attrKey }}" disabled>
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-attr"><i class="ti ti-x"></i></button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="add-attr-btn">
            <i class="ti ti-plus me-1"></i>{{ __('Ajouter attribut') }}
        </button>
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ __('Image') }}</label>
        <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
        @if($v?->image)
            <img src="{{ asset('storage/' . $v->image) }}" alt="" class="mt-1 rounded" style="height:40px;">
        @endif
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $v?->is_active ?? true) ? 'checked' : '' }} id="var_active_{{ $v?->id ?? 'new' }}">
            <label class="form-check-label" for="var_active_{{ $v?->id ?? 'new' }}">{{ __('Active') }}</label>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var counter = {{ $v && $v->values ? count($v->values) : 0 }};

    document.querySelectorAll('#add-attr-btn, [id^="add-attr-btn"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var container = this.previousElementSibling;
            var row = document.createElement('div');
            row.className = 'row g-2 mb-2 attr-row';

            var keyCol = document.createElement('div'); keyCol.className = 'col-5';
            var keyInput = document.createElement('input'); keyInput.type = 'text'; keyInput.className = 'form-control form-control-sm';
            keyInput.placeholder = 'Cle (ex: Couleur)'; keyInput.id = 'attr_key_' + counter;
            keyCol.appendChild(keyInput);

            var valCol = document.createElement('div'); valCol.className = 'col-5';
            var valInput = document.createElement('input'); valInput.type = 'text'; valInput.className = 'form-control form-control-sm';
            valInput.placeholder = 'Valeur (ex: Rouge)'; valInput.id = 'attr_val_' + counter;
            valCol.appendChild(valInput);

            var actCol = document.createElement('div'); actCol.className = 'col-2';
            var rmBtn = document.createElement('button'); rmBtn.type = 'button'; rmBtn.className = 'btn btn-sm btn-outline-danger remove-attr';
            var rmIcon = document.createElement('i'); rmIcon.className = 'ti ti-x'; rmBtn.appendChild(rmIcon);
            actCol.appendChild(rmBtn);

            row.appendChild(keyCol); row.appendChild(valCol); row.appendChild(actCol);
            container.appendChild(row);

            // Update name attribute when key is filled
            keyInput.addEventListener('change', function () {
                if (this.value.trim()) valInput.name = 'values[' + this.value.trim() + ']';
            });

            counter++;
        });
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-attr')) {
            e.target.closest('.attr-row').remove();
        }
    });
});
</script>
@endpush
@endonce

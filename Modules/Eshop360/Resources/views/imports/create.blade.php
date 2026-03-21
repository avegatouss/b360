<x-dashboard::layouts.master
    :title="__('Nouvel import') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Nouvel import')">

@php $slug = $instance->slug ?? ''; @endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-ship me-2"></i>{{ __('Nouvelle commande d\'import') }}</h4>
        <p class="text-muted mb-0">{{ __('Créer un nouvel approvisionnement international') }}</p>
    </div>
    <a href="{{ route('eshop360.imports.index', $slug) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="ti ti-alert-circle me-1"></i>{{ __('Veuillez corriger les erreurs ci-dessous.') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('eshop360.imports.store', $slug) }}" method="POST" id="importForm">
    @csrf

    <div class="row g-3">
        {{-- Left: Order info --}}
        <div class="col-xl-8">
            {{-- General Info --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-2"></i>{{ __('Informations générales') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Fournisseur') }} <span class="text-danger">*</span></label>
                            <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror select2-create" data-placeholder="{{ __('Sélectionner un fournisseur') }}" required>
                                <option value="">{{ __('Sélectionner un fournisseur') }}</option>
                                @foreach($suppliers ?? [] as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                        {{ $supplier->name }}
                                        @if($supplier->country) ({{ $supplier->country }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Entrepôt de destination') }} <span class="text-danger">*</span></label>
                            <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror select2-create" data-placeholder="{{ __('Sélectionner un entrepôt') }}" required>
                                <option value="">{{ __('Sélectionner un entrepôt') }}</option>
                                @foreach($warehouses ?? [] as $wh)
                                    <option value="{{ $wh->id }}" @selected(old('warehouse_id') == $wh->id)>{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                            @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Type de transport') }} <span class="text-danger">*</span></label>
                            <select name="shipping_type" class="form-select select2-create @error('shipping_type') is-invalid @enderror" required>
                                <option value="">{{ __('Sélectionner') }}</option>
                                <option value="sea" @selected(old('shipping_type') === 'sea')><i class="ti ti-ship"></i> {{ __('Maritime') }}</option>
                                <option value="air" @selected(old('shipping_type') === 'air')>{{ __('Aérien') }}</option>
                                <option value="land" @selected(old('shipping_type') === 'land')>{{ __('Terrestre') }}</option>
                            </select>
                            @error('shipping_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('N° conteneur') }}</label>
                            <input type="text" name="container_no" class="form-control @error('container_no') is-invalid @enderror"
                                   value="{{ old('container_no') }}" placeholder="{{ __('Ex: MSKU1234567') }}">
                            @error('container_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Méthode de répartition') }} <span class="text-danger">*</span></label>
                            <select name="cost_allocation_method" class="form-select select2-create @error('cost_allocation_method') is-invalid @enderror" required>
                                <option value="value" @selected(old('cost_allocation_method', 'value') === 'value')>{{ __('Par valeur') }}</option>
                                <option value="hybrid" @selected(old('cost_allocation_method') === 'hybrid')>{{ __('Hybride (valeur + quantite)') }}</option>
                                <option value="quantity" @selected(old('cost_allocation_method') === 'quantity')>{{ __('Par quantite') }}</option>
                            </select>
                            @error('cost_allocation_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">{{ __('Comment répartir les frais sur les produits') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Products Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-package me-2"></i>{{ __('Produits') }}</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="addProductRow">
                        <i class="ti ti-plus me-1"></i>{{ __('Ajouter un produit') }}
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="productsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:35%;">{{ __('Produit') }}</th>
                                    <th style="width:13%;">{{ __('Quantité') }}</th>
                                    <th style="width:17%;">{{ __('Prix usine (unit.)') }}</th>
                                    <th style="width:13%;">{{ __('Poids (kg)') }}</th>
                                    <th style="width:17%;" class="text-end">{{ __('Sous-total') }}</th>
                                    <th style="width:5%;"></th>
                                </tr>
                            </thead>
                            <tbody id="productRows">
                                <tr class="product-row" data-index="0">
                                    <td>
                                        <select name="items[0][product_id]" class="form-select form-select-sm product-select select2-product" data-placeholder="{{ __('Rechercher un produit...') }}" required>
                                            <option value="">{{ __('Rechercher un produit...') }}</option>
                                            @foreach($products ?? [] as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->purchase_price_provisional ?? $product->cost_price ?? 0 }}" data-sku="{{ $product->sku }}">
                                                    {{ $product->name }} {{ $product->sku ? '['.$product->sku.']' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="items[0][unit_price_factory]" class="form-control form-control-sm item-price" step="0.01" min="0" value="0" required>
                                            <span class="input-group-text">{{ $eshopCurrency ?? 'FCFA' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][weight]" class="form-control form-control-sm" step="0.01" min="0" value="0">
                                    </td>
                                    <td class="text-end align-middle">
                                        <span class="item-subtotal fw-bold">0</span> <small class="text-muted">{{ $eshopCurrency ?? 'FCFA' }}</small>
                                    </td>
                                    <td class="align-middle">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="{{ __('Supprimer') }}">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td class="fw-bold text-end" colspan="2">
                                        <span id="totalItems">1</span> {{ __('produit(s)') }}
                                    </td>
                                    <td class="fw-bold text-end" colspan="2">{{ __('Total valeur usine') }} :</td>
                                    <td class="text-end">
                                        <span id="grandTotal" class="fw-bold text-primary fs-6">0</span> <small class="text-muted">{{ $eshopCurrency ?? 'FCFA' }}</small>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Dates, notes, submit --}}
        <div class="col-xl-4">
            {{-- Dates --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-calendar me-2"></i>{{ __('Planning') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Date d\'expédition') }}</label>
                        <input type="date" name="ship_date" class="form-control @error('ship_date') is-invalid @enderror"
                               value="{{ old('ship_date') }}">
                        @error('ship_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('Date d\'arrivée estimée (ETA)') }}</label>
                        <input type="date" name="eta" class="form-control @error('eta') is-invalid @enderror"
                               value="{{ old('eta') }}">
                        @error('eta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-notes me-2"></i>{{ __('Notes') }}</h6>
                </div>
                <div class="card-body">
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="4"
                              placeholder="{{ __('Notes internes, instructions, remarques...') }}">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Summary --}}
            <div class="card border-0 shadow-sm bg-primary-subtle mb-3">
                <div class="card-body text-center py-3">
                    <small class="text-muted">{{ __('Valeur totale usine') }}</small>
                    <h3 class="fw-bold text-primary mb-0" id="summaryTotal">0 {{ $eshopCurrency ?? 'FCFA' }}</h3>
                    <small class="text-muted"><span id="summaryItems">0</span> {{ __('produit(s)') }}</small>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-check me-1"></i>{{ __('Créer la commande d\'import') }}
                </button>
                <a href="{{ route('eshop360.imports.index', $slug) }}" class="btn btn-outline-secondary">
                    {{ __('Annuler') }}
                </a>
            </div>

            {{-- Help --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body small text-muted">
                    <h6 class="fw-bold mb-2"><i class="ti ti-help me-1"></i>{{ __('Guide') }}</h6>
                    <ol class="ps-3 mb-0">
                        <li class="mb-1">{{ __('Sélectionnez le fournisseur et l\'entrepôt de destination') }}</li>
                        <li class="mb-1">{{ __('Ajoutez les produits avec les quantités et prix usine') }}</li>
                        <li class="mb-1">{{ __('La commande sera créée en brouillon') }}</li>
                        <li class="mb-1">{{ __('Vous pourrez ensuite ajouter les frais (fret, douane, etc.)') }}</li>
                        <li>{{ __('La répartition des coûts calculera le prix de revient réel') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</form>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var rowIndex = 1;
    @php
        $productsJsonData = ($products ?? collect())->map(function($p) {
            return ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'price' => $p->purchase_price_provisional ?? $p->cost_price ?? 0];
        });
    @endphp
    var productsData = @json($productsJsonData);

    // Init Select2
    function initSelect2() {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.select2-create').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%' });
            $('.select2-product').each(function () {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        allowClear: true,
                        width: '100%',
                        language: { noResults: function () { return '{{ __("Aucun résultat") }}'; } }
                    }).on('change', function () {
                        var row = this.closest('.product-row');
                        var opt = this.options[this.selectedIndex];
                        var price = opt ? (opt.dataset.price || 0) : 0;
                        row.querySelector('.item-price').value = price;
                        calcRowSubtotal(row);
                        calcTotal();
                    });
                }
            });
        }
    }
    initSelect2();

    // Add row
    document.getElementById('addProductRow').addEventListener('click', function () {
        var tbody = document.getElementById('productRows');
        var firstRow = tbody.querySelector('.product-row');
        // Destroy select2 before cloning
        var sel = firstRow.querySelector('.select2-product');
        if (typeof $ !== 'undefined' && $(sel).hasClass('select2-hidden-accessible')) {
            $(sel).select2('destroy');
        }
        var newRow = firstRow.cloneNode(true);
        newRow.dataset.index = rowIndex;
        newRow.querySelectorAll('[name]').forEach(function (el) {
            el.name = el.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else if (el.type === 'number') el.value = el.classList.contains('item-qty') ? 1 : 0;
        });
        newRow.querySelector('.item-subtotal').textContent = '0';
        // Remove any select2 containers in clone
        var s2Container = newRow.querySelector('.select2-container');
        if (s2Container) s2Container.remove();
        var clonedSelect = newRow.querySelector('.select2-product');
        if (clonedSelect) {
            clonedSelect.classList.remove('select2-hidden-accessible');
            clonedSelect.removeAttribute('data-select2-id');
            clonedSelect.removeAttribute('aria-hidden');
            clonedSelect.style.display = '';
        }
        tbody.appendChild(newRow);
        bindRowEvents(newRow);
        // Re-init select2 on all
        initSelect2();
        rowIndex++;
        calcTotal();
    });

    function bindRowEvents(row) {
        row.querySelector('.remove-row').addEventListener('click', function () {
            if (document.querySelectorAll('.product-row').length > 1) {
                var sel = row.querySelector('.select2-product');
                if (typeof $ !== 'undefined' && $(sel).hasClass('select2-hidden-accessible')) {
                    $(sel).select2('destroy');
                }
                row.remove();
                calcTotal();
            }
        });
        row.querySelectorAll('.item-qty, .item-price').forEach(function (el) {
            el.addEventListener('input', function () { calcRowSubtotal(row); calcTotal(); });
        });
    }

    function calcRowSubtotal(row) {
        var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        var price = parseFloat(row.querySelector('.item-price').value) || 0;
        var sub = Math.round(qty * price);
        row.querySelector('.item-subtotal').textContent = sub.toLocaleString('fr-FR');
    }

    function calcTotal() {
        var total = 0;
        var count = 0;
        document.querySelectorAll('.product-row').forEach(function (row) {
            var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            var price = parseFloat(row.querySelector('.item-price').value) || 0;
            total += qty * price;
            count++;
        });
        var formatted = Math.round(total).toLocaleString('fr-FR');
        document.getElementById('grandTotal').textContent = formatted;
        document.getElementById('totalItems').textContent = count;
        document.getElementById('summaryTotal').textContent = formatted + ' {{ $eshopCurrency ?? "FCFA" }}';
        document.getElementById('summaryItems').textContent = count;
    }

    document.querySelectorAll('.product-row').forEach(function (row) { bindRowEvents(row); });
    calcTotal();
});
</script>
@endpush

</x-dashboard::layouts.master>

<x-dashboard::layouts.master
    :title="__('Nouvel achat') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Nouvel achat fournisseur')">

@php $slug = $instance->slug ?? ''; @endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-truck me-2"></i>{{ __('Nouvel achat fournisseur') }}</h4>
        <p class="text-muted mb-0">{{ __('Créer un bon de commande fournisseur') }}</p>
    </div>
    <a href="{{ route('eshop360.purchases.index', $slug) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="ti ti-alert-circle me-1"></i>{{ __('Veuillez corriger les erreurs ci-dessous.') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('eshop360.purchases.store', $slug) }}" method="POST" id="purchaseForm">
    @csrf

    <div class="row g-3">
        {{-- Left: Main form --}}
        <div class="col-xl-8">
            {{-- Supplier & info --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-building me-2"></i>{{ __('Informations') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Fournisseur') }} <span class="text-danger">*</span></label>
                            <select id="supplier-select" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror select2-form" data-placeholder="{{ __('Rechercher un fournisseur...') }}">
                                <option value="">{{ __('Rechercher un fournisseur...') }}</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" data-name="{{ $sup->name }}" data-email="{{ $sup->email ?? '' }}" @selected(old('supplier_id') == $sup->id)>
                                        {{ $sup->name }} @if($sup->code) ({{ $sup->code }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="supplier_name" id="supplier-name" value="{{ old('supplier_name') }}">
                            <input type="hidden" name="supplier_email" id="supplier-email" value="{{ old('supplier_email') }}">
                            @error('supplier_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Entrepôt de destination') }}</label>
                            <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror select2-form" data-placeholder="{{ __('Sélectionner') }}">
                                <option value="">{{ __('Sélectionner un entrepôt') }}</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" @selected(old('warehouse_id') == $wh->id)>{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                            @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Instructions, commentaires...') }}">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Products --}}
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
                                    <th style="width:40%;">{{ __('Produit') }}</th>
                                    <th style="width:15%;">{{ __('Quantité') }}</th>
                                    <th style="width:20%;">{{ __('Coût unitaire') }}</th>
                                    <th style="width:20%;" class="text-end">{{ __('Sous-total') }}</th>
                                    <th style="width:5%;"></th>
                                </tr>
                            </thead>
                            <tbody id="productRows">
                                <tr class="product-row" data-index="0">
                                    <td>
                                        <select name="items[0][product_id]" class="form-select form-select-sm product-select select2-product" data-placeholder="{{ __('Rechercher...') }}" required>
                                            <option value="">{{ __('Rechercher un produit...') }}</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->cost_price ?? 0 }}">
                                                    {{ $product->name }} @if($product->sku) [{{ $product->sku }}] @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="items[0][unit_cost]" class="form-control form-control-sm item-price" step="0.01" min="0" value="0" required>
                                            <span class="input-group-text">{{ $eshopCurrency ?? 'FCFA' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end align-middle"><span class="item-subtotal fw-bold">0</span> <span class="text-muted">{{ $eshopCurrency ?? 'FCFA' }}</span></td>
                                    <td class="align-middle"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ti ti-x"></i></button></td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td class="fw-bold text-end" colspan="1"><span id="totalItems">1</span> {{ __('produit(s)') }}</td>
                                    <td class="fw-bold text-end" colspan="2">{{ __('Total') }} :</td>
                                    <td class="text-end"><span id="grandTotal" class="fw-bold text-primary fs-6">0</span> <span class="text-muted">{{ $eshopCurrency ?? 'FCFA' }}</span></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Payment + Submit --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-cash me-2"></i>{{ __('Paiement') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Montant payé') }}</label>
                        <div class="input-group">
                            <input type="number" name="paid_amount" class="form-control" step="0.01" min="0" value="{{ old('paid_amount', 0) }}">
                            <span class="input-group-text">{{ $eshopCurrency ?? 'FCFA' }}</span>
                        </div>
                        <span class="text-muted">{{ __('Laisser à 0 si non payé') }}</span>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Statut initial') }}</label>
                        <select name="status" class="form-select">
                            <option value="pending" @selected(old('status', 'pending') === 'pending')>{{ __('En attente') }}</option>
                            <option value="ordered" @selected(old('status') === 'ordered')>{{ __('Commandée') }}</option>
                            <option value="received" @selected(old('status') === 'received')>{{ __('Reçue (stock MAJ auto)') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Summary --}}
            <div class="card border-0 shadow-sm bg-primary-subtle mb-3">
                <div class="card-body text-center py-3">
                    <span class="text-muted">{{ __('Total commande') }}</span>
                    <h3 class="fw-bold text-primary mb-0" id="summaryTotal">0 {{ $eshopCurrency ?? 'FCFA' }}</h3>
                    <span class="text-muted"><span id="summaryItems">0</span> {{ __('produit(s)') }}</span>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-check me-1"></i>{{ __('Créer le bon de commande') }}
                </button>
                <a href="{{ route('eshop360.purchases.index', $slug) }}" class="btn btn-outline-secondary">{{ __('Annuler') }}</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var rowIndex = 1;

    // Select2 init
    function initSelect2() {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.select2-form').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%' });
            $('.select2-product').each(function () {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap-5', allowClear: true, width: '100%',
                        language: { noResults: function () { return '{{ __("Aucun résultat") }}'; } }
                    }).on('change', function () {
                        var row = this.closest('.product-row');
                        var opt = this.options[this.selectedIndex];
                        if (opt && opt.dataset.price) {
                            row.querySelector('.item-price').value = opt.dataset.price;
                        }
                        calcRowSubtotal(row); calcTotal();
                    });
                }
            });
        }
    }
    initSelect2();

    // Supplier select → hidden fields (use jQuery for Select2 compat)
    if (typeof $ !== 'undefined') {
        $('#supplier-select').on('change select2:select select2:clear', function () {
            var opt = this.options[this.selectedIndex];
            document.getElementById('supplier-name').value = opt && opt.value ? (opt.dataset.name || '') : '';
            document.getElementById('supplier-email').value = opt && opt.value ? (opt.dataset.email || '') : '';
        });
        if ($('#supplier-select').val()) $('#supplier-select').trigger('change');
    }

    // Add row
    document.getElementById('addProductRow').addEventListener('click', function () {
        var tbody = document.getElementById('productRows');
        var firstRow = tbody.querySelector('.product-row');
        var sel = firstRow.querySelector('.select2-product');
        if (typeof $ !== 'undefined' && $(sel).hasClass('select2-hidden-accessible')) $(sel).select2('destroy');
        var newRow = firstRow.cloneNode(true);
        newRow.dataset.index = rowIndex;
        newRow.querySelectorAll('[name]').forEach(function (el) {
            el.name = el.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else if (el.type === 'number') el.value = el.classList.contains('item-qty') ? 1 : 0;
        });
        newRow.querySelector('.item-subtotal').textContent = '0';
        var s2 = newRow.querySelector('.select2-container');
        if (s2) s2.remove();
        var clonedSel = newRow.querySelector('.select2-product');
        if (clonedSel) { clonedSel.classList.remove('select2-hidden-accessible'); clonedSel.removeAttribute('data-select2-id'); clonedSel.style.display = ''; }
        tbody.appendChild(newRow);
        bindRowEvents(newRow);
        initSelect2();
        rowIndex++;
        calcTotal();
    });

    function bindRowEvents(row) {
        row.querySelector('.remove-row').addEventListener('click', function () {
            if (document.querySelectorAll('.product-row').length > 1) {
                var sel = row.querySelector('.select2-product');
                if (typeof $ !== 'undefined' && $(sel).hasClass('select2-hidden-accessible')) $(sel).select2('destroy');
                row.remove(); calcTotal();
            }
        });
        row.querySelectorAll('.item-qty, .item-price').forEach(function (el) {
            el.addEventListener('input', function () { calcRowSubtotal(row); calcTotal(); });
        });
    }

    function calcRowSubtotal(row) {
        var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        var price = parseFloat(row.querySelector('.item-price').value) || 0;
        row.querySelector('.item-subtotal').textContent = Math.round(qty * price).toLocaleString('fr-FR');
    }

    function calcTotal() {
        var total = 0, count = 0;
        document.querySelectorAll('.product-row').forEach(function (row) {
            total += (parseFloat(row.querySelector('.item-qty').value) || 0) * (parseFloat(row.querySelector('.item-price').value) || 0);
            count++;
        });
        var f = Math.round(total).toLocaleString('fr-FR');
        document.getElementById('grandTotal').textContent = f;
        document.getElementById('totalItems').textContent = count;
        document.getElementById('summaryTotal').textContent = f + ' {{ $eshopCurrency ?? "FCFA" }}';
        document.getElementById('summaryItems').textContent = count;
    }

    document.querySelectorAll('.product-row').forEach(function (row) { bindRowEvents(row); });
    calcTotal();
});
</script>
@endpush

</x-dashboard::layouts.master>

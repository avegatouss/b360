<x-dashboard::layouts.master
    :title="__('Nouvelle vente') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Nouvelle vente')">

@php $slug = $instance->slug ?? ''; @endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-receipt me-2"></i>{{ __('Nouvelle vente') }}</h4>
        <p class="text-muted mb-0">{{ __('Enregistrer une vente manuelle') }}</p>
    </div>
    <a href="{{ route('eshop360.sales.index', $slug) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ti ti-arrow-left me-1"></i>{{ __('Retour aux ventes') }}
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="ti ti-alert-circle me-1"></i>{{ __('Veuillez corriger les erreurs ci-dessous.') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('eshop360.sales.store', $slug) }}" method="POST" id="saleForm">
    @csrf
    <input type="hidden" name="source" value="manual">

    <div class="row g-3">
        {{-- Left: Products --}}
        <div class="col-xl-8">
            {{-- Customer & Channel --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-user me-2"></i>{{ __('Client & Canal') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Client') }}</label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror select2-form" data-placeholder="{{ __('Client anonyme (optionnel)') }}">
                                <option value="">{{ __('Client anonyme') }}</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                        {{ $customer->name }} @if($customer->code)({{ $customer->code }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @if($channels->isNotEmpty())
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Canal de distribution') }}</label>
                            <select name="channel_id" class="form-select @error('channel_id') is-invalid @enderror select2-form" data-placeholder="{{ __('Aucun (vente directe)') }}">
                                <option value="">{{ __('Aucun (vente directe)') }}</option>
                                @foreach($channels as $ch)
                                    <option value="{{ $ch->id }}" @selected(old('channel_id') == $ch->id)>{{ $ch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Products --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-package me-2"></i>{{ __('Articles') }}</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="addItemRow">
                        <i class="ti ti-plus me-1"></i>{{ __('Ajouter un article') }}
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:35%;">{{ __('Produit') }}</th>
                                    <th style="width:12%;">{{ __('Quantité') }}</th>
                                    <th style="width:18%;">{{ __('Prix unitaire') }}</th>
                                    <th style="width:12%;">{{ __('Remise') }}</th>
                                    <th style="width:18%;" class="text-end">{{ __('Sous-total') }}</th>
                                    <th style="width:5%;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemRows">
                                <tr class="item-row" data-index="0">
                                    <td>
                                        <select name="items[0][product_id]" class="form-select form-select-sm product-select select2-product" data-placeholder="{{ __('Rechercher...') }}" required>
                                            <option value="">{{ __('Rechercher un produit...') }}</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        data-price="{{ $product->selling_price ?? $product->sale_price ?? 0 }}"
                                                        data-original="{{ $product->selling_price ?? $product->sale_price ?? 0 }}"
                                                        data-sku="{{ $product->sku }}">
                                                    {{ $product->name }} @if($product->sku)[{{ $product->sku }}]@endif — {{ number_format($product->selling_price ?? $product->sale_price ?? 0, 0, ',', ' ') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="items[0][unit_price]" class="form-control form-control-sm item-price" step="1" min="0" value="0" required>
                                            <span class="input-group-text">XAF</span>
                                        </div>
                                        <input type="hidden" name="items[0][original_price]" class="item-original-price" value="0">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="items[0][discount]" class="form-control form-control-sm item-discount" step="1" min="0" value="0">
                                            <span class="input-group-text">XAF</span>
                                        </div>
                                    </td>
                                    <td class="text-end align-middle">
                                        <span class="item-subtotal fw-bold">0</span> <small class="text-muted">XAF</small>
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
                                    <td colspan="2" class="fw-bold"><span id="totalItems">1</span> {{ __('article(s)') }}</td>
                                    <td colspan="2" class="text-end fw-bold">{{ __('Sous-total') }} :</td>
                                    <td class="text-end"><span id="subtotal" class="fw-bold">0</span> <small class="text-muted">XAF</small></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <label class="form-label"><i class="ti ti-notes me-1"></i>{{ __('Notes') }}</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Notes internes sur cette vente...') }}">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Right: Payment & Summary --}}
        <div class="col-xl-4">
            {{-- Adjustments --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-adjustments me-2"></i>{{ __('Ajustements') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Remise globale') }}</label>
                        <div class="input-group">
                            <input type="number" name="discount_amount" id="globalDiscount" class="form-control" step="1" min="0" value="{{ old('discount_amount', 0) }}">
                            <span class="input-group-text">XAF</span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Frais de livraison') }}</label>
                        <div class="input-group">
                            <input type="number" name="shipping_amount" id="shippingAmount" class="form-control" step="1" min="0" value="{{ old('shipping_amount', 0) }}">
                            <span class="input-group-text">XAF</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-cash me-2"></i>{{ __('Paiement') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Méthode de paiement') }} <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>{{ __('Espèces') }}</option>
                            <option value="card" @selected(old('payment_method') === 'card')>{{ __('Carte bancaire') }}</option>
                            <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>{{ __('Virement') }}</option>
                            <option value="cheque" @selected(old('payment_method') === 'cheque')>{{ __('Chèque') }}</option>
                            <option value="paypal" @selected(old('payment_method') === 'paypal')>PayPal</option>
                            <option value="deposit" @selected(old('payment_method') === 'deposit')>{{ __('Acompte') }}</option>
                            <option value="gift_card" @selected(old('payment_method') === 'gift_card')>{{ __('Carte cadeau') }}</option>
                            <option value="external" @selected(old('payment_method') === 'external')>{{ __('Externe') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Montant payé') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="paid_amount" id="paidAmount" class="form-control" step="1" min="0" value="{{ old('paid_amount', 0) }}" required>
                            <span class="input-group-text">XAF</span>
                        </div>
                        <div class="d-flex gap-1 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-success pay-full-btn">{{ __('Payer total') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary pay-half-btn">{{ __('50%') }}</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary --}}
            <div class="card border-0 shadow-sm bg-primary-subtle mb-3">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('Sous-total') }}</span>
                        <span id="summarySubtotal" class="fw-medium">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('Remise') }}</span>
                        <span id="summaryDiscount" class="text-danger">-0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('Livraison') }}</span>
                        <span id="summaryShipping">+0</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold fs-6">{{ __('TOTAL') }}</span>
                        <span id="summaryTotal" class="fw-bold fs-5 text-primary">0 XAF</span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted small">{{ __('Reste à payer') }}</span>
                        <span id="summaryDue" class="fw-medium text-danger">0 XAF</span>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="ti ti-check me-1"></i>{{ __('Enregistrer la vente') }}
                </button>
                <a href="{{ route('eshop360.sales.index', $slug) }}" class="btn btn-outline-secondary">{{ __('Annuler') }}</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var rowIndex = 1;

    function initSelect2() {
        if (typeof $ === 'undefined' || !$.fn.select2) return;
        $('.select2-form').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%' });
            }
        });
        $('.select2-product').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    theme: 'bootstrap-5', allowClear: true, width: '100%',
                    language: { noResults: function () { return '{{ __("Aucun résultat") }}'; } }
                }).on('change', function () {
                    var row = this.closest('.item-row');
                    var opt = this.options[this.selectedIndex];
                    if (opt && opt.dataset.price) {
                        row.querySelector('.item-price').value = Math.round(parseFloat(opt.dataset.price));
                        row.querySelector('.item-original-price').value = opt.dataset.original || opt.dataset.price;
                    }
                    calcRow(row); calcTotals();
                });
            }
        });
    }
    initSelect2();

    // Add row
    document.getElementById('addItemRow').addEventListener('click', function () {
        var tbody = document.getElementById('itemRows');
        var first = tbody.querySelector('.item-row');
        var sel = first.querySelector('.select2-product');
        if (typeof $ !== 'undefined' && $(sel).hasClass('select2-hidden-accessible')) $(sel).select2('destroy');

        var nr = first.cloneNode(true);
        nr.dataset.index = rowIndex;
        nr.querySelectorAll('[name]').forEach(function (el) {
            el.name = el.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else if (el.type === 'number') el.value = el.classList.contains('item-qty') ? 1 : 0;
        });
        nr.querySelector('.item-subtotal').textContent = '0';
        var s2c = nr.querySelector('.select2-container');
        if (s2c) s2c.remove();
        var cs = nr.querySelector('.select2-product');
        if (cs) { cs.classList.remove('select2-hidden-accessible'); cs.removeAttribute('data-select2-id'); cs.style.display = ''; }

        tbody.appendChild(nr);
        bindRow(nr);
        initSelect2();
        rowIndex++;
        calcTotals();
    });

    function bindRow(row) {
        row.querySelector('.remove-row').addEventListener('click', function () {
            if (document.querySelectorAll('.item-row').length > 1) {
                var sel = row.querySelector('.select2-product');
                if (typeof $ !== 'undefined' && $(sel).hasClass('select2-hidden-accessible')) $(sel).select2('destroy');
                row.remove(); calcTotals();
            }
        });
        row.querySelectorAll('.item-qty, .item-price, .item-discount').forEach(function (el) {
            el.addEventListener('input', function () { calcRow(row); calcTotals(); });
        });
    }

    function calcRow(row) {
        var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        var price = parseFloat(row.querySelector('.item-price').value) || 0;
        var disc = parseFloat(row.querySelector('.item-discount').value) || 0;
        var sub = Math.round((qty * price) - disc);
        row.querySelector('.item-subtotal').textContent = Math.max(0, sub).toLocaleString('fr-FR');
    }

    function calcTotals() {
        var subtotal = 0, count = 0;
        document.querySelectorAll('.item-row').forEach(function (row) {
            var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            var price = parseFloat(row.querySelector('.item-price').value) || 0;
            var disc = parseFloat(row.querySelector('.item-discount').value) || 0;
            subtotal += Math.max(0, (qty * price) - disc);
            count++;
        });
        var globalDisc = parseFloat(document.getElementById('globalDiscount').value) || 0;
        var shipping = parseFloat(document.getElementById('shippingAmount').value) || 0;
        var total = Math.max(0, Math.round(subtotal - globalDisc + shipping));
        var paid = parseFloat(document.getElementById('paidAmount').value) || 0;
        var due = Math.max(0, total - paid);

        document.getElementById('subtotal').textContent = Math.round(subtotal).toLocaleString('fr-FR');
        document.getElementById('totalItems').textContent = count;
        document.getElementById('summarySubtotal').textContent = Math.round(subtotal).toLocaleString('fr-FR') + ' XAF';
        document.getElementById('summaryDiscount').textContent = '-' + Math.round(globalDisc).toLocaleString('fr-FR') + ' XAF';
        document.getElementById('summaryShipping').textContent = '+' + Math.round(shipping).toLocaleString('fr-FR') + ' XAF';
        document.getElementById('summaryTotal').textContent = total.toLocaleString('fr-FR') + ' XAF';
        document.getElementById('summaryDue').textContent = due.toLocaleString('fr-FR') + ' XAF';
        document.getElementById('summaryDue').classList.toggle('text-danger', due > 0);
        document.getElementById('summaryDue').classList.toggle('text-success', due === 0);

        // Store total for quick-pay buttons
        window._saleTotal = total;
    }

    // Quick pay buttons
    document.querySelector('.pay-full-btn').addEventListener('click', function () {
        document.getElementById('paidAmount').value = window._saleTotal || 0;
        calcTotals();
    });
    document.querySelector('.pay-half-btn').addEventListener('click', function () {
        document.getElementById('paidAmount').value = Math.round((window._saleTotal || 0) / 2);
        calcTotals();
    });

    // Recalc on adjustment changes
    ['globalDiscount', 'shippingAmount', 'paidAmount'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', calcTotals);
    });

    document.querySelectorAll('.item-row').forEach(function (r) { bindRow(r); });
    calcTotals();
});
</script>
@endpush

</x-dashboard::layouts.master>

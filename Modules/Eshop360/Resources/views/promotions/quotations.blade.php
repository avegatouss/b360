<x-dashboard::layouts.master
    :title="__('Devis') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Devis')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Devis') }}</h4>
            <h6>{{ __('Gerez vos devis et propositions commerciales') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.invoices.index', $instance->slug ?? '') }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-file-invoice me-1"></i>{{ __('Factures') }}</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-quotation-modal"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouveau devis') }}</button>
    </div>
</div>

{{-- Filtres --}}
@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ __('Reference, client...') }}" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('Tous les statuts') }}</option>
                    @foreach(['draft' => 'Brouillon', 'sent' => 'Envoye', 'pending' => 'En attente', 'ordered' => 'Commande', 'cancelled' => 'Annule'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
            <div class="col-auto">
                <a href="{{ route('eshop360.quotations.index', $instance->slug ?? '') }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-clipboard-list me-2"></i>{{ __('Devis') }} <span class="badge bg-primary ms-1">{{ $quotations->total() }}</span></h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Validite') }}</th>
                        <th>{{ __('Articles') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quotation)
                        @php
                            $statusColors = [
                                'draft' => 'bg-secondary', 'sent' => 'bg-info',
                                'pending' => 'bg-warning text-dark', 'ordered' => 'bg-success',
                                'cancelled' => 'bg-danger',
                            ];
                            $statusLabels = [
                                'draft' => 'Brouillon', 'sent' => 'Envoye',
                                'pending' => 'En attente', 'ordered' => 'Commande',
                                'cancelled' => 'Annule',
                            ];
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $quotation->reference }}</td>
                            <td>{{ $quotation->customer->name ?? __('Sans client') }}</td>
                            <td>{{ $quotation->created_at?->format('d/m/Y') }}</td>
                            <td>
                                @if($quotation->valid_until)
                                    <span class="{{ $quotation->valid_until->isPast() ? 'text-danger' : '' }}">
                                        {{ $quotation->valid_until->format('d/m/Y') }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $quotation->items->count() }} {{ __('ligne(s)') }}</td>
                            <td class="text-end fw-bold">{{ number_format($quotation->total, 0, ',', ' ') }}</td>
                            <td>
                                <span class="badge {{ $statusColors[$quotation->status] ?? 'bg-secondary' }}">
                                    {{ __($statusLabels[$quotation->status] ?? $quotation->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('eshop360.quotations.show', [$instance->slug ?? '', $quotation]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Voir') }}">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @if($quotation->status !== 'ordered')
                                    <a href="{{ route('eshop360.quotations.pdf', [$instance->slug ?? '', $quotation]) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('PDF') }}" target="_blank">
                                        <i class="ti ti-file-type-pdf"></i>
                                    </a>
                                    @if($quotation->customer?->email)
                                    <form method="POST" action="{{ route('eshop360.quotations.send-email', [$instance->slug ?? '', $quotation]) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-info" title="{{ __('Email') }}"><i class="ti ti-mail-forward"></i></button>
                                    </form>
                                    @endif
                                    @if(in_array($quotation->status, ['draft', 'sent', 'pending']))
                                    <form method="POST" action="{{ route('eshop360.quotations.convert', [$instance->slug ?? '', $quotation]) }}" class="d-inline" onsubmit="return confirm('{{ __('Convertir ce devis en facture ?') }}')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="{{ __('Convertir en facture') }}"><i class="ti ti-file-invoice"></i></button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('eshop360.quotations.destroy', [$instance->slug ?? '', $quotation]) }}" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce devis ?') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                                    </form>
                                    @else
                                        <span class="badge bg-success-subtle text-success">{{ __('Commande creee') }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="ti ti-file-off fs-1 d-block mb-2"></i>
                                {{ __('Aucun devis. Creez votre premier devis !') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
            <div class="p-3">{{ $quotations->links() }}</div>
        @endif
    </div>
</div>

{{-- Modal Creation Devis --}}
<div class="modal fade" id="create-quotation-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-file-text me-2"></i>{{ __('Nouveau devis') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('eshop360.quotations.store', $instance->slug ?? '') }}" id="quotation-form">
                @csrf
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Client') }}</label>
                            <select name="customer_id" class="form-select select2-quotation-modal" data-placeholder="{{ __('Sans client') }}">
                                <option value="">{{ __('Sans client') }}</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}{{ $customer->code ? ' (' . $customer->code . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Valide jusqu\'au') }}</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ now()->addDays(30)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Remise globale') }}</label>
                            <input type="number" name="discount_amount" class="form-control" min="0" step="1" value="0" id="q-global-discount">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-2">{{ __('Articles') }}</h6>
                    <table class="table table-bordered" id="q-items-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%;">{{ __('Produit') }}</th>
                                <th style="width: 15%;">{{ __('Quantite') }}</th>
                                <th style="width: 15%;">{{ __('Prix unitaire') }}</th>
                                <th style="width: 10%;">{{ __('Remise') }}</th>
                                <th style="width: 15%;" class="text-end">{{ __('Total') }}</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="q-items-body"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="q-add-item">
                                        <i class="ti ti-plus me-1"></i>{{ __('Ajouter un article') }}
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <table class="table table-sm mb-0">
                                <tr><td class="text-muted">{{ __('Sous-total') }}</td><td class="text-end" id="q-subtotal">0</td></tr>
                                <tr><td class="text-muted">{{ __('Remise') }}</td><td class="text-end text-danger" id="q-discount">0</td></tr>
                                <tr class="fw-bold border-top"><td>{{ __('Total') }}</td><td class="text-end" id="q-total">0</td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Notes internes...') }}"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Conditions') }}</label>
                            <textarea name="terms" class="form-control" rows="2" placeholder="{{ __('Conditions de paiement, livraison...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary" id="q-submit-btn" disabled>
                        <i class="ti ti-check me-1"></i>{{ __('Creer le devis') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Product data for JS (hidden template) --}}
<template id="product-options-tpl">
    <option value="">-- {{ __('Choisir') }} --</option>
    @foreach($products as $p)
        <option value="{{ $p->id }}" data-price="{{ $p->price ?? 0 }}">{{ $p->name }}{{ $p->sku ? ' (' . $p->sku . ')' : '' }}</option>
    @endforeach
</template>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    var $modal = $('#create-quotation-modal');
    $('.select2-quotation-modal').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '', dropdownParent: $modal });
    });

    // Init Select2 on dynamically added product selects
    $(document).on('quotation:row-added', function (e, $sel) {
        $sel.select2({ theme: 'bootstrap-5', width: '100%', placeholder: '-- Choisir --', dropdownParent: $modal });
    });
});
</script>
@endpush

<script>
(function() {
    'use strict';

    var rowIndex = 0;
    var tbody = document.getElementById('q-items-body');
    var addBtn = document.getElementById('q-add-item');
    var submitBtn = document.getElementById('q-submit-btn');
    var optionsTpl = document.getElementById('product-options-tpl');

    function addRow() {
        var i = rowIndex++;
        var tr = document.createElement('tr');
        tr.setAttribute('data-row', String(i));

        // Product select
        var td1 = document.createElement('td');
        var sel = document.createElement('select');
        sel.name = 'items[' + i + '][product_id]';
        sel.className = 'form-select form-select-sm q-product-select';
        sel.required = true;
        sel.appendChild(optionsTpl.content.cloneNode(true));
        td1.appendChild(sel);

        // Quantity
        var td2 = document.createElement('td');
        var qtyInput = document.createElement('input');
        qtyInput.type = 'number'; qtyInput.name = 'items[' + i + '][quantity]';
        qtyInput.className = 'form-control form-control-sm q-qty';
        qtyInput.min = '1'; qtyInput.value = '1'; qtyInput.required = true;
        td2.appendChild(qtyInput);

        // Price
        var td3 = document.createElement('td');
        var priceInput = document.createElement('input');
        priceInput.type = 'number'; priceInput.name = 'items[' + i + '][unit_price]';
        priceInput.className = 'form-control form-control-sm q-price';
        priceInput.min = '0'; priceInput.step = '1'; priceInput.value = '0'; priceInput.required = true;
        td3.appendChild(priceInput);

        // Line discount
        var td4 = document.createElement('td');
        var discInput = document.createElement('input');
        discInput.type = 'number'; discInput.name = 'items[' + i + '][discount]';
        discInput.className = 'form-control form-control-sm q-item-discount';
        discInput.min = '0'; discInput.step = '1'; discInput.value = '0';
        td4.appendChild(discInput);

        // Line total
        var td5 = document.createElement('td');
        td5.className = 'text-end fw-semibold q-line-total';
        td5.textContent = '0';

        // Remove btn
        var td6 = document.createElement('td');
        var rmBtn = document.createElement('button');
        rmBtn.type = 'button'; rmBtn.className = 'btn btn-sm btn-outline-danger';
        var rmIcon = document.createElement('i');
        rmIcon.className = 'ti ti-x';
        rmBtn.appendChild(rmIcon);
        td6.appendChild(rmBtn);

        tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3);
        tr.appendChild(td4); tr.appendChild(td5); tr.appendChild(td6);
        tbody.appendChild(tr);

        // Use jQuery change for Select2 compatibility
        if (typeof jQuery !== 'undefined') {
            jQuery(sel).on('change', function() {
                var opt = sel.options[sel.selectedIndex];
                priceInput.value = Math.round(parseFloat(opt ? opt.getAttribute('data-price') : 0) || 0);
                recalc();
            });
            jQuery(document).trigger('quotation:row-added', [jQuery(sel)]);
        } else {
            sel.addEventListener('change', function() {
                var opt = sel.options[sel.selectedIndex];
                priceInput.value = Math.round(parseFloat(opt.getAttribute('data-price')) || 0);
                recalc();
            });
        }
        qtyInput.addEventListener('input', recalc);
        priceInput.addEventListener('input', recalc);
        discInput.addEventListener('input', recalc);
        rmBtn.addEventListener('click', function() {
            if (typeof jQuery !== 'undefined') jQuery(sel).select2('destroy');
            tr.remove();
            recalc();
        });

        recalc();
    }

    function recalc() {
        var rows = tbody.querySelectorAll('tr');
        var subtotal = 0;
        rows.forEach(function(row) {
            var qty = parseInt(row.querySelector('.q-qty').value) || 0;
            var price = parseFloat(row.querySelector('.q-price').value) || 0;
            var disc = parseFloat(row.querySelector('.q-item-discount').value) || 0;
            var lt = Math.max(0, (qty * price) - disc);
            row.querySelector('.q-line-total').textContent = Math.round(lt).toLocaleString('fr-FR');
            subtotal += lt;
        });
        var gd = parseFloat(document.getElementById('q-global-discount').value) || 0;
        var total = Math.max(0, subtotal - gd);
        document.getElementById('q-subtotal').textContent = Math.round(subtotal).toLocaleString('fr-FR');
        document.getElementById('q-discount').textContent = '-' + Math.round(gd).toLocaleString('fr-FR');
        document.getElementById('q-total').textContent = Math.round(total).toLocaleString('fr-FR');
        submitBtn.disabled = rows.length === 0;
    }

    addBtn.addEventListener('click', addRow);
    document.getElementById('q-global-discount').addEventListener('input', recalc);
    addRow();
})();
</script>

</x-dashboard::layouts.master>

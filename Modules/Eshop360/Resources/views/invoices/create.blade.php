<x-dashboard::layouts.master
    :title="__('Nouvelle Facture') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Nouvelle Facture')">

@php
    $slug = $instance->slug ?? '';
    $currency = $eshopCurrency ?? 'FCFA';
    $fneSettings = app(\Modules\Eshop360\Services\FneService::class)->getSettings();
    $templates = [
        'default' => 'Standard',
        'modern' => 'Moderne',
        'classic' => 'Classique',
        'minimal' => 'Minimal',
    ];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-file-invoice me-2"></i>{{ __('Nouvelle Facture') }}</h4>
        <p class="text-muted mb-0">{{ __('Creer une nouvelle facture') }}</p>
    </div>
    <a href="{{ route('eshop360.invoices.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="ti ti-alert-circle me-1"></i>{{ __('Veuillez corriger les erreurs ci-dessous.') }}
        <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('eshop360.invoices.store', $slug) }}" method="POST" id="invoice-form">
    @csrf

    <div class="row g-3">
        {{-- Left panel --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-info-circle me-2"></i>{{ __('Informations') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Client') }}</label>
                        <select name="customer_id" class="form-select inv-create-select2" data-placeholder="{{ __('Selectionner un client') }}">
                            <option value=""></option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(($order->customer_id ?? old('customer_id')) == $customer->id)>
                                {{ $customer->name }}{{ $customer->code ? ' (' . $customer->code . ')' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    @if($order)
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Commande liee') }}</label>
                        <input type="text" class="form-control" value="{{ $order->order_number }}" disabled>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">{{ __('Date d\'echeance') }}</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->addDays($settings['default_due_days'] ?? 30)->toDateString()) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Template') }}</label>
                        <div class="d-flex gap-2">
                            <select name="template" class="form-select inv-create-select2" id="template-select">
                                @foreach($templates as $val => $label)
                                    <option value="{{ $val }}" @selected(old('template', $settings['default_template'] ?? 'default') === $val)>{{ __($label) }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-info flex-shrink-0" id="preview-template-btn" title="{{ __('Apercu du template') }}">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Remise globale') }} ({{ $currency }})</label>
                        <input type="number" name="discount_amount" class="form-control" value="{{ old('discount_amount', 0) }}" min="0" step="1" id="inv-global-discount">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Notes internes...') }}">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Conditions') }}</label>
                        <textarea name="terms" class="form-control" rows="2">{{ old('terms', $settings['default_terms'] ?? '') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Pied de page') }}</label>
                        <textarea name="footer_text" class="form-control" rows="2">{{ old('footer_text', $settings['default_footer'] ?? '') }}</textarea>
                    </div>

                    {{-- FNE Option --}}
                    @if($fneSettings['enabled'])
                    <div class="border rounded p-3 bg-light">
                        <div class="form-check form-switch">
                            <input type="hidden" name="generate_fne" value="0">
                            <input class="form-check-input" type="checkbox" name="generate_fne" value="1" id="fne-toggle" @checked(old('generate_fne'))>
                            <label class="form-check-label fw-bold" for="fne-toggle">
                                <i class="ti ti-certificate me-1"></i>{{ __('Generer la facture FNE') }}
                            </label>
                        </div>
                        <span class="text-muted">{{ __('Certifier automatiquement aupres de la DGI a la creation.') }}</span>
                        @if($fneSettings['sandbox'])
                            <div class="mt-1"><span class="badge bg-warning text-dark">{{ __('Mode test') }}</span></div>
                        @endif
                        <div class="mt-2" id="fne-template-row" style="display:none;">
                            <label class="form-label">{{ __('Type FNE') }}</label>
                            <select name="fne_template" class="form-select form-select-sm">
                                <option value="B2C">B2C — {{ __('Particulier') }}</option>
                                <option value="B2B">B2B — {{ __('Entreprise') }}</option>
                                <option value="B2G">B2G — {{ __('Etat') }}</option>
                                <option value="B2F">B2F — {{ __('International') }}</option>
                            </select>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right panel: items --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="ti ti-list me-2"></i>{{ __('Articles') }}</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="add-item-btn"><i class="ti ti-plus me-1"></i>{{ __('Ajouter') }}</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="items-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:25%;">{{ __('Description') }}</th>
                                    <th style="width:20%;">{{ __('Produit') }}</th>
                                    <th style="width:10%;">{{ __('Qte') }}</th>
                                    <th style="width:15%;">{{ __('Prix unit.') }}</th>
                                    <th style="width:10%;">{{ __('Remise') }}</th>
                                    <th style="width:10%;">{{ __('Taxe') }}</th>
                                    <th style="width:5%;" class="text-end">{{ __('Total') }}</th>
                                    <th style="width:5%;"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body">
                                @if($order)
                                    @foreach($order->items as $idx => $item)
                                    <tr>
                                        <td><input type="text" name="items[{{ $idx }}][description]" class="form-control form-control-sm" value="{{ $item->product_name ?? $item->product->name ?? '' }}" required></td>
                                        <td>
                                            <select name="items[{{ $idx }}][product_id]" class="form-select form-select-sm inv-product-select">
                                                <option value="">--</option>
                                                @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price ?? 0 }}" @selected(($item->product_id ?? '') == $product->id)>{{ $product->name }}{{ $product->sku ? ' (' . $product->sku . ')' : '' }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="items[{{ $idx }}][quantity]" class="form-control form-control-sm inv-qty" value="{{ $item->quantity }}" min="1" required></td>
                                        <td><input type="number" name="items[{{ $idx }}][unit_price]" class="form-control form-control-sm inv-price" value="{{ $item->unit_price }}" min="0" step="1" required></td>
                                        <td><input type="number" name="items[{{ $idx }}][discount]" class="form-control form-control-sm inv-disc" value="{{ $item->discount ?? 0 }}" min="0" step="1"></td>
                                        <td><input type="number" name="items[{{ $idx }}][tax]" class="form-control form-control-sm inv-tax" value="{{ $item->tax ?? 0 }}" min="0" step="1"></td>
                                        <td class="text-end fw-bold inv-line-total align-middle">0</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger inv-remove-row"><i class="ti ti-trash"></i></button></td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td><input type="text" name="items[0][description]" class="form-control form-control-sm" required></td>
                                        <td>
                                            <select name="items[0][product_id]" class="form-select form-select-sm inv-product-select">
                                                <option value="">--</option>
                                                @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price ?? 0 }}">{{ $product->name }}{{ $product->sku ? ' (' . $product->sku . ')' : '' }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm inv-qty" value="1" min="1" required></td>
                                        <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm inv-price" value="0" min="0" step="1" required></td>
                                        <td><input type="number" name="items[0][discount]" class="form-control form-control-sm inv-disc" value="0" min="0" step="1"></td>
                                        <td><input type="number" name="items[0][tax]" class="form-control form-control-sm inv-tax" value="0" min="0" step="1"></td>
                                        <td class="text-end fw-bold inv-line-total align-middle">0</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger inv-remove-row"><i class="ti ti-trash"></i></button></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Totals --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <table class="table table-borderless mb-0">
                                <tr><td class="text-muted">{{ __('Sous-total') }}</td><td class="text-end fw-medium" id="inv-subtotal">0</td></tr>
                                <tr><td class="text-muted">{{ __('Taxes') }}</td><td class="text-end" id="inv-taxes">0</td></tr>
                                <tr><td class="text-muted">{{ __('Remise globale') }}</td><td class="text-end text-danger" id="inv-discount">0</td></tr>
                                <tr class="fw-bold border-top"><td>{{ __('Total') }}</td><td class="text-end fs-5 text-primary" id="inv-total">0 {{ $currency }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('eshop360.invoices.index', $slug) }}" class="btn btn-outline-secondary">{{ __('Annuler') }}</a>
                <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Creer la facture') }}</button>
            </div>
        </div>
    </div>
</form>

{{-- Template Preview Modal --}}
<div class="modal fade" id="template-preview-modal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="ti ti-eye me-2"></i>{{ __('Apercu du template') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="height:70vh;">
                <iframe id="template-preview-iframe" style="width:100%;height:100%;border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<template id="product-options-tpl">
    <option value="">--</option>
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
    var currency = @json($currency);

    // Select2
    $('.inv-create-select2').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: function () { return $(this).data('placeholder') || ''; } });

    // FNE toggle
    $('#fne-toggle').on('change', function () {
        $('#fne-template-row').toggle(this.checked);
    }).trigger('change');

    // Template preview
    $('#preview-template-btn').on('click', function () {
        var tpl = $('#template-select').val() || 'default';
        var previewUrl = @json(route('eshop360.invoices.index', $slug)) + '?preview_template=' + tpl;
        // For now show a placeholder — real preview needs a sample invoice
        var iframe = document.getElementById('template-preview-iframe');
        iframe.srcdoc = '<div style="display:flex;align-items:center;justify-content:center;height:100%;font-family:sans-serif;color:#666;"><div style="text-align:center;"><h2>' + tpl.charAt(0).toUpperCase() + tpl.slice(1) + '</h2><p>Apercu du template de facturation</p><p style="font-size:80px;margin:0;">&#128196;</p></div></div>';
        new bootstrap.Modal('#template-preview-modal').show();
    });

    // Items logic
    var itemIndex = {{ $order ? $order->items->count() : 1 }};
    var optionsTpl = document.getElementById('product-options-tpl');

    function recalc() {
        var subtotal = 0, taxes = 0;
        $('#items-body tr').each(function () {
            var qty = parseInt($(this).find('.inv-qty').val()) || 0;
            var price = parseFloat($(this).find('.inv-price').val()) || 0;
            var disc = parseFloat($(this).find('.inv-disc').val()) || 0;
            var tax = parseFloat($(this).find('.inv-tax').val()) || 0;
            var line = Math.max(0, (qty * price) - disc);
            $(this).find('.inv-line-total').text(Math.round(line + tax).toLocaleString('fr-FR'));
            subtotal += line;
            taxes += tax;
        });
        var gd = parseFloat($('#inv-global-discount').val()) || 0;
        var total = Math.max(0, subtotal + taxes - gd);
        $('#inv-subtotal').text(Math.round(subtotal).toLocaleString('fr-FR'));
        $('#inv-taxes').text(Math.round(taxes).toLocaleString('fr-FR'));
        $('#inv-discount').text('-' + Math.round(gd).toLocaleString('fr-FR'));
        $('#inv-total').text(Math.round(total).toLocaleString('fr-FR') + ' ' + currency);
    }

    $('#add-item-btn').on('click', function () {
        var i = itemIndex++;
        var row = $('<tr>' +
            '<td><input type="text" name="items[' + i + '][description]" class="form-control form-control-sm" required></td>' +
            '<td><select name="items[' + i + '][product_id]" class="form-select form-select-sm inv-product-select"></select></td>' +
            '<td><input type="number" name="items[' + i + '][quantity]" class="form-control form-control-sm inv-qty" value="1" min="1" required></td>' +
            '<td><input type="number" name="items[' + i + '][unit_price]" class="form-control form-control-sm inv-price" value="0" min="0" step="1" required></td>' +
            '<td><input type="number" name="items[' + i + '][discount]" class="form-control form-control-sm inv-disc" value="0" min="0" step="1"></td>' +
            '<td><input type="number" name="items[' + i + '][tax]" class="form-control form-control-sm inv-tax" value="0" min="0" step="1"></td>' +
            '<td class="text-end fw-bold inv-line-total align-middle">0</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger inv-remove-row"><i class="ti ti-trash"></i></button></td>' +
            '</tr>');
        row.find('.inv-product-select').append($(optionsTpl.content).clone());
        $('#items-body').append(row);
        row.find('.inv-product-select').select2({ theme: 'bootstrap-5', width: '100%' });
        recalc();
    });

    $(document).on('input change', '.inv-qty, .inv-price, .inv-disc, .inv-tax, #inv-global-discount', recalc);
    $(document).on('click', '.inv-remove-row', function () { $(this).closest('tr').remove(); recalc(); });
    $(document).on('change', '.inv-product-select', function () {
        var row = $(this).closest('tr');
        var price = $(this).find(':selected').data('price') || 0;
        row.find('.inv-price').val(Math.round(price));
        var name = $(this).find(':selected').text().split('(')[0].trim();
        if (!row.find('[name*="description"]').val()) row.find('[name*="description"]').val(name);
        recalc();
    });

    // Init Select2 on existing product selects
    $('.inv-product-select').select2({ theme: 'bootstrap-5', width: '100%' });

    recalc();
});
</script>
@endpush

</x-dashboard::layouts.master>

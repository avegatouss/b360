<x-dashboard::layouts.master
    :title="__('Vente') . ($sale->order_number ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail Vente')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $sale->order_number }}</h4>
            <h6>{{ $sale->created_at->format('d/m/Y H:i') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        @include('eshop360::fne._sign-button', ['order' => $sale])
        <button class="btn btn-white border" data-bs-toggle="modal" data-bs-target="#receipt-modal">
            <i class="ti ti-printer me-1"></i>{{ __('Recu') }}
        </button>
        <a href="{{ route('eshop360.sales.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Informations') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Numero</th><td>{{ $sale->order_number }}</td></tr>
                    <tr><th>Client</th><td>{{ $sale->customer->name ?? 'Client anonyme' }}</td></tr>
                    <tr><th>{{ __('Source') }}</th><td><span class="badge bg-info">{{ \Modules\Eshop360\Support\UiLabel::enum($sale->source) }}</span></td></tr>
                    <tr>
                        <th>{{ __('Statut') }}</th>
                        <td><span class="badge bg-{{ $sale->status === 'completed' ? 'success' : ($sale->status === 'cancelled' ? 'danger' : 'warning') }}">{{ \Modules\Eshop360\Support\UiLabel::enum($sale->status) }}</span></td>
                    </tr>
                    <tr>
                        <th>{{ __('Paiement') }}</th>
                        <td><span class="badge bg-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'overdue' ? 'danger' : 'warning') }}">{{ \Modules\Eshop360\Support\UiLabel::enum($sale->payment_status) }}</span></td>
                    </tr>
                    <tr><th>{{ __('Methode') }}</th><td>{{ \Modules\Eshop360\Support\UiLabel::enum($sale->payment_method) }}</td></tr>
                    @if($sale->cashRegister)
                    <tr><th>Caisse</th><td>#{{ $sale->cashRegister->id }}{{ $sale->store ? ' · ' . $sale->store->name : '' }}</td></tr>
                    @endif
                    @if($sale->coupon_code)
                    <tr><th>Coupon</th><td><code>{{ $sale->coupon_code }}</code></td></tr>
                    @endif
                    @if($sale->notes)
                    <tr><th>Notes</th><td>{{ $sale->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Totaux') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Sous-total</th><td class="text-end">{{ number_format($sale->subtotal, 2) }}</td></tr>
                    <tr><th>Taxes</th><td class="text-end">{{ number_format($sale->tax_amount, 2) }}</td></tr>
                    @if($sale->discount_amount > 0)
                    <tr><th>Remise</th><td class="text-end text-danger">-{{ number_format($sale->discount_amount, 2) }}</td></tr>
                    @endif
                    @if($sale->shipping_amount > 0)
                    <tr><th>Livraison</th><td class="text-end">{{ number_format($sale->shipping_amount, 2) }}</td></tr>
                    @endif
                    <tr class="fw-bold"><th>Total</th><td class="text-end">{{ number_format($sale->total, 2) }}</td></tr>
                    <tr><th>Paye</th><td class="text-end text-success">{{ number_format($sale->paid_amount, 2) }}</td></tr>
                    @if($sale->due_amount > 0)
                    <tr><th>Reste du</th><td class="text-end text-danger">{{ number_format($sale->due_amount, 2) }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Paiements') }}</h5></div>
            <div class="card-body">
                @forelse($sale->payments as $payment)
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ \Modules\Eshop360\Support\UiLabel::enum($payment->method) }}</strong>
                            <span class="{{ $payment->amount < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($payment->amount, 2) }}
                            </span>
                        </div>
                        <div class="small text-muted">
                            {{ $payment->reference ?? __('Sans reference') }} · {{ \Modules\Eshop360\Support\UiLabel::enum($payment->status ?? 'completed') }}
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('Aucun paiement enregistre.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5>{{ __('Articles') }}</h5></div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>{{ __('Produit') }}</th><th>{{ __('SKU') }}</th><th>{{ __('Prix unit.') }}</th><th>{{ __('Qte') }}</th><th>{{ __('Remise') }}</th><th>{{ __('Taxe') }}</th><th>{{ __('Total') }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse($sale->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td><code>{{ $item->sku }}</code></td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->discount ?? 0, 2) }}</td>
                            <td>{{ number_format($item->tax ?? 0, 2) }}</td>
                            <td class="fw-bold">{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted">{{ __('Aucun article') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($sale->status !== 'refunded' && $sale->items->where('quantity', '>', 0)->isNotEmpty())
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="ti ti-arrow-back-up me-2"></i>{{ __('Traiter un retour') }}</h5>
                    <span class="badge bg-warning-subtle text-warning">{{ __('Selectionnez les articles a retourner') }}</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('eshop360.sales.returns.store', $instance->slug ?? '') }}" id="return-form">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $sale->id }}">

                        <div class="table-responsive mb-3">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="select-all-items">
                                            </div>
                                        </th>
                                        <th>{{ __('Produit') }}</th>
                                        <th class="text-center" style="width:80px;">{{ __('Vendu') }}</th>
                                        <th class="text-center" style="width:100px;">{{ __('Qte retour') }}</th>
                                        <th style="width:180px;">{{ __('Action stock') }}</th>
                                        <th style="width:180px;">{{ __('Raison') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items->where('quantity', '>', 0)->values() as $index => $item)
                                        <tr class="return-item-row {{ !$loop->first ? 'opacity-50' : '' }}" data-index="{{ $index }}">
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input item-checkbox" type="checkbox" name="items[{{ $index }}][selected]" value="1" data-index="{{ $index }}">
                                                </div>
                                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                            </td>
                                            <td>
                                                <div class="fw-medium">{{ $item->product_name }}</div>
                                                <small class="text-muted"><code>{{ $item->sku }}</code> — {{ number_format($item->unit_price, 0, ',', ' ') }} / u</small>
                                            </td>
                                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                            <td>
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm return-qty" min="1" max="{{ $item->quantity }}" value="1" disabled data-unit-total="{{ $item->total / $item->quantity }}">
                                            </td>
                                            <td>
                                                <select name="items[{{ $index }}][stock_action]" class="form-select form-select-sm stock-action-select" disabled>
                                                    <option value="return_stock">{{ __('Retour en stock') }}</option>
                                                    <option value="adjustment">{{ __('Ajustement stock') }}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="items[{{ $index }}][reason]" class="form-select form-select-sm reason-select" disabled>
                                                    <option value="return">{{ __('Retour client') }}</option>
                                                    <option value="damaged">{{ __('Produit endommage') }}</option>
                                                    <option value="defective">{{ __('Produit defectueux') }}</option>
                                                    <option value="wrong_item">{{ __('Mauvais article') }}</option>
                                                    <option value="lost">{{ __('Perte / Vol') }}</option>
                                                    <option value="correction">{{ __('Correction d\'inventaire') }}</option>
                                                    <option value="other">{{ __('Autre') }}</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Montant a rembourser') }}</label>
                                <div class="input-group">
                                    <input type="number" name="refund_amount" id="refund-amount" class="form-control" min="0" step="1" value="0" required>
                                    <span class="input-group-text">{{ $instance->settings['currency'] ?? 'FCFA' }}</span>
                                </div>
                                <small class="text-muted">{{ __('Total vente : :total', ['total' => number_format($sale->total, 0, ',', ' ')]) }}</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Mode de remboursement') }}</label>
                                <select name="refund_method" class="form-select">
                                    <option value="cash">{{ __('Especes') }}</option>
                                    <option value="original">{{ __('Meme methode') }}</option>
                                    <option value="wallet">{{ __('Portefeuille client') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Notes') }}</label>
                                <input type="text" name="notes" class="form-control" placeholder="{{ __('Notes retour...') }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <div>
                                <span class="text-muted" id="return-summary">{{ __('Aucun article selectionne') }}</span>
                            </div>
                            <button type="submit" class="btn btn-danger" id="btn-submit-return" disabled>
                                <i class="ti ti-arrow-back-up me-1"></i>{{ __('Valider le retour') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Receipt Modal --}}
<div class="modal fade" id="receipt-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="ti ti-receipt me-1"></i>{{ __('Recu de vente') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="receipt-content" style="max-height:70vh;overflow-y:auto;">
                @include('eshop360::pdf.order-receipt-inline', ['order' => $sale])
            </div>
            <div class="modal-footer py-2 justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __('Fermer') }}</button>
                <div class="d-flex gap-2">
                    <a href="{{ route('eshop360.orders.receipt', [$instance->slug ?? '', $sale]) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="ti ti-printer me-1"></i>{{ __('Imprimer PDF') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var checkboxes = document.querySelectorAll('.item-checkbox');
    var selectAll = document.getElementById('select-all-items');
    var submitBtn = document.getElementById('btn-submit-return');
    var refundInput = document.getElementById('refund-amount');
    var summary = document.getElementById('return-summary');

    function updateRow(cb) {
        var row = cb.closest('.return-item-row');
        var checked = cb.checked;
        var inputs = row.querySelectorAll('input:not([type=hidden]):not([type=checkbox]), select');
        inputs.forEach(function (el) { el.disabled = !checked; });
        row.classList.toggle('opacity-50', !checked);
    }

    function recalculate() {
        var total = 0, count = 0;
        checkboxes.forEach(function (cb) {
            if (cb.checked) {
                var row = cb.closest('.return-item-row');
                var qty = parseInt(row.querySelector('.return-qty').value) || 0;
                var unitTotal = parseFloat(row.querySelector('.return-qty').dataset.unitTotal) || 0;
                total += qty * unitTotal;
                count += qty;
            }
        });
        if (refundInput) refundInput.value = Math.round(total);
        if (submitBtn) submitBtn.disabled = count === 0;
        if (summary) summary.textContent = count > 0
            ? count + ' article(s) — ' + Math.round(total).toLocaleString('fr') + ' {{ $instance->settings["currency"] ?? "FCFA" }}'
            : '{{ __("Aucun article selectionne") }}';
    }

    checkboxes.forEach(function (cb) {
        cb.addEventListener('change', function () {
            updateRow(this);
            recalculate();
        });
    });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (cb) {
                cb.checked = selectAll.checked;
                updateRow(cb);
            });
            recalculate();
        });
    }

    document.querySelectorAll('.return-qty').forEach(function (input) {
        input.addEventListener('input', recalculate);
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
